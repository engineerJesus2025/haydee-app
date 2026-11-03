# generar_candidatos.py
"""
Lee dataset_entrenamiento.json y produce dataset_candidatos.jsonl.

Cada línea: un candidato con features y label (1 = coincide con GT, 0 = no).

Uso:
    python generar_candidatos.py --entrada dataset_entrenamiento.json
"""
import argparse
import json
import re
from collections import Counter
from pathlib import Path

UMBRAL_MONTO = 0.01  # tolerancia para considerar "monto correcto"

MAPA_BANCOS = {
    "0102", "0105", "0108", "0115", "0134", "0163", "0172", "0175", "0191",
}


# ---------- Normalización ----------
def normalizar_monto(valor) -> float | None:
    if valor is None:
        return None
    s = re.sub(r"[^\d\.\,]", "", str(valor).strip())
    if not s:
        return None
    if "." in s and "," in s:
        s = s.replace(".", "").replace(",", ".")
    elif "," in s:
        s = s.replace(",", ".")
    try:
        return float(s)
    except ValueError:
        return None


def _dedup(seq):
    visto, out = set(), []
    for x in seq:
        if x not in visto:
            visto.add(x)
            out.append(x)
    return out


# ---------- Generadores de candidatos ----------
def candidatos_monto(texto: str) -> list[str]:
    t = texto.lower()
    cands = []
    # Buscar números con formatos de dinero (permitiendo letras O y l dentro)
    patron = r"\b[\dOoIl][\dOoIl\.\,\- ]{1,15}[\dOoIl]\b"
    for m in re.finditer(patron, t):
        # Normalizar
        val = m.group(0).upper().replace('O', '0').replace('I', '1').replace('L', '1')
        # Quedarse solo con los que tengan sentido numérico
        if any(c.isdigit() for c in val):
            cands.append(val)
    return _dedup(cands)


def candidatos_referencia(texto: str) -> list[str]:
    cands = []
    # Busca secuencias de 6 a 25 caracteres que sean números o letras similares
    for m in re.finditer(r"\b[\dOoIlzSZ]{6,25}\b", texto):
        val = m.group(0)
        # Normalizamos antes de guardar
        val = val.upper().replace('O', '0').replace('I', '1').replace('L', '1').replace('Z', '2').replace('S', '5')
        cands.append(val)
    return _dedup(cands)


def candidatos_banco(texto: str) -> list[str]:
    cands = []
    # Ahora busca 01XX, O1XX (letra O), y permite guiones o puntos después
    for m in re.finditer(r"\b[0Oo1lI][0Oo1lI]\d{2}\b", texto):
        # Normalizamos: si leyó O, lo pasamos a 0. Si leyó I o l, a 1.
        val = m.group(0).upper().replace('O', '0').replace('I', '1').replace('L', '1')
        cands.append(val)
    
    # Si sigue sin haber bancos, inyectamos uno falso
    if not cands:
        cands.append("0199")
        
    return _dedup(cands)

def candidatos_fecha(texto: str) -> list[str]:
    cands = []
    # Buscar fechas válidas
    for m in re.finditer(r"\b(\d{2})[\/\-\s]+(\d{2})[\/\-\s]+(\d{4})\b", texto):
        d, mo, y = m.groups()
        cands.append(f"{y}-{mo}-{d}")
    for m in re.finditer(r"\b(\d{4})[\/\-](\d{2})[\/\-](\d{2})\b", texto):
        y, mo, d = m.groups()
        cands.append(f"{y}-{mo}-{d}")
    
    # INYECCIÓN DE NEGATIVOS: Si no hay fechas, inventamos un par de falsas
    # para que el modelo aprenda a descartarlas
    if not cands:
        cands.append("1900-01-01") # Fecha basura 1
        cands.append("2099-12-31") # Fecha basura 2
        
    return _dedup(cands)


# ---------- Features ----------
def _contexto(texto, valor, ventana=40):
    pos = texto.find(str(valor))
    if pos < 0:
        return "", "", -1
    return (
        texto[max(0, pos - ventana):pos].lower(),
        texto[pos + len(str(valor)):pos + len(str(valor)) + ventana].lower(),
        pos / max(len(texto), 1),
    )


def features_monto(valor, texto):
    v = str(valor)
    prev, nxt, pos_rel = _contexto(texto, v)
    norm = normalizar_monto(v)
    return {
        "longitud": len(v),
        "tiene_coma": "," in v,
        "tiene_punto": "." in v,
        "es_entero": (norm is not None and norm == int(norm)) if norm else False,
        "empieza_con_cero": v.startswith("0"),
        "prev_monto": "monto" in prev,
        "prev_bs": "bs" in prev,
        "next_bs": "bs" in nxt,
        "pos_rel": pos_rel,
        "plausible": (norm is not None and 0 < norm < 1e8),
    }


def features_referencia(valor, texto):
    v = str(valor)
    prev, _, pos_rel = _contexto(texto, v)
    return {
        "longitud": len(v),
        "solo_digitos": v.isdigit(),
        "long_numerica": len(re.sub(r"[^\d]", "", v)),
        "empieza_con_cero": v.startswith("0"),
        "prev_ref": ("referencia" in prev) or ("ref" in prev),
        "prev_operacion": "operaci" in prev,
        "es_codigo_banco": v in MAPA_BANCOS,
        "pos_rel": pos_rel,
    }


def features_banco(valor, texto):
    v = str(valor)
    prev, _, pos_rel = _contexto(texto, v)
    return {
        "es_conocido": v in MAPA_BANCOS,
        "longitud": len(v),
        "empieza_01": v.startswith("01"),
        "prev_banco": "banco" in prev,
        "prev_origen": "origen" in prev,
        "prev_destino": "destino" in prev,
        "pos_rel": pos_rel,
    }


def features_fecha(valor, texto):
    v = str(valor)
    prev, _, pos_rel = _contexto(texto, v)
    anio_ok = False
    if len(v) >= 4 and v[:4].isdigit():
        anio_ok = 2020 <= int(v[:4]) <= 2030
    return {
        "longitud": len(v),
        "prev_fecha": "fecha" in prev,
        "anio_plausible": anio_ok,
        "pos_rel": pos_rel,
    }


# ---------- Etiquetado ----------
def _etq_monto(valor, gt):
    if not gt:
        return 0
    n = normalizar_monto(valor)
    return int(n is not None and abs(n - float(gt)) <= UMBRAL_MONTO)


def _etq_ref(valor, gt):
    if not gt:
        return 0
    return int(str(valor).strip().upper() == str(gt).strip().upper())


def _etq_banco(valor, gt):
    if not gt:
        return 0
    return int(str(valor).strip() == str(gt).strip())


def _etq_fecha(valor, gt):
    if not gt:
        return 0
    return int(str(valor).strip() == str(gt).strip())


# ---------- Main ----------
def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--entrada", type=Path, default=Path("dataset_entrenamiento.json"))
    ap.add_argument("--out", type=Path, default=Path("dataset_candidatos.jsonl"))
    args = ap.parse_args()

    data = json.loads(args.entrada.read_text(encoding="utf-8"))

    stats = Counter()
    sin_positivo = Counter()  # campos sin ningún candidato positivo en la imagen
    lineas = []

    for img, info in data["imagenes"].items():
        gt = info["campos_correctos"]
        positivos_img = {c: False for c in ("monto_detectado", "numero_referencia",
                                            "banco_origen", "fecha_operacion")}

        for intento, payload in info["textos_ocr"].items():
            texto = payload["texto"]
            if not texto:
                continue

            especificaciones = [
                ("monto_detectado",   candidatos_monto(texto),     features_monto,      _etq_monto,      gt.get("monto_detectado")),
                ("numero_referencia", candidatos_referencia(texto), features_referencia, _etq_ref,        gt.get("numero_referencia")),
                ("banco_origen",      candidatos_banco(texto),      features_banco,      _etq_banco,      gt.get("banco_origen")),
                ("fecha_operacion",   candidatos_fecha(texto),      features_fecha,      _etq_fecha,      gt.get("fecha_operacion")),
            ]

            for campo, cands, feat_fn, etq_fn, gt_val in especificaciones:
                for valor in cands:
                    label = etq_fn(valor, gt_val)
                    if label == 1:
                        positivos_img[campo] = True
                    lineas.append({
                        "imagen": img,
                        "intento": intento,
                        "campo": campo,
                        "valor": valor,
                        "features": feat_fn(valor, texto),
                        "label": label,
                    })
                    stats[f"{campo}_{label}"] += 1

        for campo, hay in positivos_img.items():
            if not hay:
                sin_positivo[campo] += 1

    with args.out.open("w", encoding="utf-8") as f:
        for linea in lineas:
            f.write(json.dumps(linea, ensure_ascii=False) + "\n")

    print(f"[OK] Guardado: {args.out}")
    print(f"     Total candidatos: {len(lineas)}\n")
    print("     Distribución por campo (positivos / negativos / ratio):")
    for campo in ("monto_detectado", "numero_referencia", "banco_origen", "fecha_operacion"):
        p = stats.get(f"{campo}_1", 0)
        n = stats.get(f"{campo}_0", 0)
        total = p + n
        ratio = p / total if total else 0
        print(f"       {campo:20s}: {p:4d} / {n:4d}  ({ratio:.1%} positivos)")

    if sin_positivo:
        print("\n     [ALERTA] Imágenes sin ningún candidato positivo para algún campo:")
        for campo, k in sin_positivo.items():
            print(f"       {campo}: {k}/{len(data['imagenes'])} imágenes")
        print("       (Significa que el OCR no produjo un texto que, tras normalizar,")
        print("        coincida con el GT. Esas imágenes no aportan positivos a ese campo.)")


if __name__ == "__main__":
    main()