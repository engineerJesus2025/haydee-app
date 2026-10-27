#!/usr/bin/env python3
"""
evaluar.py — Evaluación automática del pipeline OCR Haydee.

Genera TODAS las métricas del "Reporte técnico de métricas de entrenamiento
y validación del componente inteligente OCR" y las deja en tablas Markdown
listas para pegar.

Flujo de uso:
    1) python evaluar.py --plantilla --dataset ./dataset --out ground_truth.json
    2) Editar ground_truth.json a mano (llenar los 4 campos por imagen)
    3) python evaluar.py --dataset ./dataset --gt ground_truth.json --out reporte_generado.md

Requisitos:
    pip install jiwer easyocr opencv-python-headless
"""

import argparse
import json
import os
import re
import sys
import tempfile
from collections import Counter, defaultdict
from pathlib import Path

try:
    import jiwer
    _JIWER = True
except ImportError:
    _JIWER = False

try:
    from motor_ocr import (
        extraer_texto_de_imagen,
        buscar_patrones_bancarios_universal,
    )
    from preprocesador import aplicar_filtro_otsu, aplicar_filtro_adaptativo
except ImportError as e:
    print(f"[ERROR] No se pudo importar el sistema OCR: {e}")
    print("Ejecuta este script desde la carpeta raíz del proyecto.")
    sys.exit(1)


EXTENSIONES_IMG = {".jpg", ".jpeg", ".png", ".bmp", ".tif", ".tiff", ".webp"}
UMBRAL_CONFIANZA = 60.0  # para la sección 7.2 del reporte


# ---------------------------------------------------------------------------
# Utilidades
# ---------------------------------------------------------------------------
def listar_imagenes(carpeta: Path):
    return sorted(p for p in carpeta.rglob("*") if p.suffix.lower() in EXTENSIONES_IMG)


def _merge_step(r, campos):
    """Aplica una etapa de fusión como lo hace ejecutar_pipeline_ocr."""
    r = dict(r)
    for k in ("numero_referencia", "monto_detectado"):
        if not r.get(k):
            r[k] = campos.get(k)
    if r.get("banco_origen") in (None, "Desconocido", ""):
        if campos.get("banco_origen") not in (None, "Desconocido", ""):
            r["banco_origen"] = campos["banco_origen"]
    if not r.get("fecha_operacion"):
        r["fecha_operacion"] = campos.get("fecha_operacion")
    return r


def _combinar(intentos):
    r = dict(intentos[1]["campos"])
    r = _merge_step(r, intentos[2]["campos"])
    r = _merge_step(r, intentos[3]["campos"])
    return r


def _intento_exito(intentos):
    r = intentos[1]["campos"]
    if r.get("numero_referencia") and r.get("monto_detectado"):
        return 1
    r = _merge_step(r, intentos[2]["campos"])
    if r.get("numero_referencia") and r.get("monto_detectado"):
        return 2
    r = _merge_step(r, intentos[3]["campos"])
    if r.get("numero_referencia") and r.get("monto_detectado"):
        return 3
    return 0  # fallo total


def _normalizar_texto(t):
    if not t:
        return ""
    return re.sub(r"\s+", " ", t).strip()


def _cer_wer(pred, gt):
    if not _JIWER or not gt:
        return None, None
    try:
        p = _normalizar_texto(pred)
        g = _normalizar_texto(gt)
        return jiwer.cer(g, p), jiwer.wer(g, p)
    except Exception:
        return None, None


def _match_ref(p, g):
    return p is not None and g is not None and str(p).strip().upper() == str(g).strip().upper()

def _match_monto(p, g):
    try:
        return p is not None and g is not None and abs(float(p) - float(g)) <= 0.01
    except (TypeError, ValueError):
        return False

def _match_banco(p, g):
    return p is not None and g is not None and str(p).strip() == str(g).strip()

def _match_fecha(p, g):
    return p is not None and g is not None and str(p).strip() == str(g).strip()


# ---------------------------------------------------------------------------
# Pipeline por imagen
# ---------------------------------------------------------------------------
def procesar_imagen(ruta: Path):
    res = {"ruta": str(ruta), "intentos": {}}

    txt1, c1 = extraer_texto_de_imagen(str(ruta))
    res["intentos"][1] = {"texto": txt1, "confianza": c1,
                          "campos": buscar_patrones_bancarios_universal(txt1)}

    for etapa, filtro in ((2, aplicar_filtro_otsu), (3, aplicar_filtro_adaptativo)):
        tmp = None
        try:
            with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as f:
                tmp = f.name
            filtro(str(ruta), tmp)
            txt, conf = extraer_texto_de_imagen(tmp)
            res["intentos"][etapa] = {"texto": txt, "confianza": conf,
                                       "campos": buscar_patrones_bancarios_universal(txt)}
        finally:
            if tmp and os.path.exists(tmp):
                os.remove(tmp)

    res["combinado"] = _combinar(res["intentos"])
    res["intento_exito"] = _intento_exito(res["intentos"])
    return res


# ---------------------------------------------------------------------------
# Métricas
# ---------------------------------------------------------------------------
def _metricas_campo(pares):
    tp = fp = fn = 0
    for pred, gt, fn_match in pares:
        gt_existe = gt is not None and str(gt).strip() != ""
        pred_existe = pred is not None and str(pred).strip() != ""
        if pred_existe and gt_existe and fn_match(pred, gt):
            tp += 1
        elif pred_existe:
            fp += 1
        elif gt_existe and not pred_existe:
            fn += 1
    precision = tp / (tp + fp) if (tp + fp) else 0.0
    recall = tp / (tp + fn) if (tp + fn) else 0.0
    f1 = 2 * precision * recall / (precision + recall) if (precision + recall) else 0.0
    exactitud = tp / len(pares) if pares else 0.0
    return dict(precision=precision, recall=recall, f1=f1,
                exactitud=exactitud, tp=tp, fp=fp, fn=fn)


def calcular_metricas(resultados, gt):
    # 6.1 — métricas por campo (sobre el resultado COMBINADO)
    pares_ref, pares_monto, pares_banco, pares_fecha = [], [], [], []
    for r in resultados:
        nombre = Path(r["ruta"]).name
        g = gt[nombre]
        c = r["combinado"]
        pares_ref.append((c.get("numero_referencia"), g.get("numero_referencia"), _match_ref))
        pares_monto.append((c.get("monto_detectado"), g.get("monto_detectado"), _match_monto))
        pares_banco.append((c.get("banco_origen"), g.get("banco_origen"), _match_banco))
        pares_fecha.append((c.get("fecha_operacion"), g.get("fecha_operacion"), _match_fecha))

    metricas_por_campo = {
        "numero_referencia": _metricas_campo(pares_ref),
        "monto_detectado":  _metricas_campo(pares_monto),
        "banco_origen":     _metricas_campo(pares_banco),
        "fecha_operacion":  _metricas_campo(pares_fecha),
    }

    # 6.2 — CER/WER (sobre el texto del intento 1, el "OCR crudo")
    cers, wers = [], []
    for r in resultados:
        nombre = Path(r["ruta"]).name
        g_texto = gt[nombre].get("texto_completo", "")
        if not g_texto:
            continue
        cer, wer = _cer_wer(r["intentos"][1]["texto"], g_texto)
        if cer is not None:
            cers.append(cer); wers.append(wer)
    cer_medio = sum(cers) / len(cers) if cers else None
    wer_medio = sum(wers) / len(wers) if wers else None

    # 6.3 — ablación por intento
    resueltos = Counter(r["intento_exito"] for r in resultados)
    n = len(resultados)
    confianza_por_intento = {}
    cer_por_intento = {}
    for etapa in (1, 2, 3):
        confs = [r["intentos"][etapa]["confianza"] for r in resultados]
        confianza_por_intento[etapa] = sum(confs) / len(confs) if confs else 0.0
        vals = []
        for r in resultados:
            g_texto = gt[Path(r["ruta"]).name].get("texto_completo", "")
            if not g_texto:
                continue
            cer, _ = _cer_wer(r["intentos"][etapa]["texto"], g_texto)
            if cer is not None:
                vals.append(cer)
        cer_por_intento[etapa] = sum(vals) / len(vals) if vals else None

    # 6.4 — matriz de confusión de banco
    matriz = defaultdict(Counter)
    clases = set()
    for r in resultados:
        g = gt[Path(r["ruta"]).name].get("banco_origen", "")
        if not g:
            continue
        p = r["combinado"].get("banco_origen") or "No detectado"
        matriz[g][p] += 1
        clases.add(g); clases.add(p)

    # 6.5 — distribución por tipo de imagen
    por_tipo = defaultdict(lambda: Counter())
    for r in resultados:
        tipo = gt[Path(r["ruta"]).name].get("tipo", "desconocido")
        por_tipo[tipo]["total"] += 1
        if r["intento_exito"] == 0:
            por_tipo[tipo]["fallo"] += 1
        else:
            por_tipo[tipo][f"intento{r['intento_exito']}"] += 1

    # 7.x — casos para análisis de errores
    casos_conf_baja = sum(
        1 for r in resultados
        if r["intento_exito"] != 0
        and r["intentos"][r["intento_exito"]]["confianza"] < UMBRAL_CONFIANZA
    )
    casos_exito_campo_malo = 0
    for r in resultados:
        if r["intento_exito"] == 0:
            continue
        g = gt[Path(r["ruta"]).name]
        c = r["combinado"]
        if not _match_banco(c.get("banco_origen"), g.get("banco_origen")) or \
           not _match_fecha(c.get("fecha_operacion"), g.get("fecha_operacion")):
            casos_exito_campo_malo += 1

    casos_adaptativo_mejor = 0
    if _JIWER:
        for r in resultados:
            if r["intento_exito"] != 2:
                continue
            g_texto = gt[Path(r["ruta"]).name].get("texto_completo", "")
            if not g_texto:
                continue
            _, _ = None, None
            cer_otsu, _ = _cer_wer(r["intentos"][2]["texto"], g_texto)
            cer_adap, _ = _cer_wer(r["intentos"][3]["texto"], g_texto)
            if cer_otsu is not None and cer_adap is not None and cer_adap < cer_otsu:
                casos_adaptativo_mejor += 1

    return {
        "n": n,
        "metricas_por_campo": metricas_por_campo,
        "cer_medio": cer_medio,
        "wer_medio": wer_medio,
        "resueltos_por_intento": dict(resueltos),
        "confianza_por_intento": confianza_por_intento,
        "cer_por_intento": cer_por_intento,
        "matriz_confusion_banco": {k: dict(v) for k, v in matriz.items()},
        "clases_banco": sorted(clases),
        "por_tipo": {k: dict(v) for k, v in por_tipo.items()},
        "casos_conf_baja": casos_conf_baja,
        "casos_exito_campo_malo": casos_exito_campo_malo,
        "casos_adaptativo_mejor_que_otsu": casos_adaptativo_mejor,
        "umbral_confianza": UMBRAL_CONFIANZA,
    }


# ---------------------------------------------------------------------------
# Renderizado Markdown
# ---------------------------------------------------------------------------
def _fmt(v, dec=3):
    if v is None:
        return "—"
    return f"{v:.{dec}f}"


def render_markdown(m):
    n = m["n"]
    r = m["resueltos_por_intento"]
    lineas = []
    A = lineas.append

    A("# Métricas generadas automáticamente\n")
    A(f"**Total de imágenes evaluadas:** {n}\n")

    A("## 6.1 Métricas por campo (resultado combinado)\n")
    A("| Campo | Precisión | Recall | F1-score | Exactitud |")
    A("|---|---|---|---|---|")
    for campo, v in m["metricas_por_campo"].items():
        A(f"| `{campo}` | {_fmt(v['precision'])} | {_fmt(v['recall'])} | "
          f"{_fmt(v['f1'])} | {_fmt(v['exactitud'])} |")
    A("")

    A("## 6.2 Métricas del OCR crudo (texto)\n")
    A("| Métrica | Valor |")
    A("|---|---|")
    A(f"| CER medio | {_fmt(m['cer_medio'])} |")
    A(f"| WER medio | {_fmt(m['wer_medio'])} |")
    A("")

    A("## 6.3 Análisis de ablación por filtro\n")
    A("| Intento | Filtro | Imágenes resueltas | Tasa de éxito acumulada (%) | Confianza promedio (%) | CER medio |")
    A("|---|---|---|---|---|---|")
    acum = 0
    for etapa, filtro in ((1, "Ninguno (cruda)"), (2, "Otsu"), (3, "Adaptativo")):
        k = r.get(etapa, 0)
        acum += k
        tasa = acum / n * 100 if n else 0
        A(f"| {etapa} | {filtro} | {k} | {tasa:.1f} | "
          f"{m['confianza_por_intento'][etapa]:.1f} | {_fmt(m['cer_por_intento'][etapa])} |")
    A(f"| — | **Fallo total** | {r.get(0, 0)} | {r.get(0, 0)/n*100:.1f} | — | — |")
    A("")

    A("## 6.4 Matriz de confusión del clasificador de banco\n")
    clases = m["clases_banco"]
    A("| Real \\ Predicho | " + " | ".join(clases) + " |")
    A("|---" + "|---" * len(clases) + "|")
    for real in clases:
        fila = [str(m["matriz_confusion_banco"].get(real, {}).get(p, 0)) for p in clases]
        A(f"| **{real}** | " + " | ".join(fila) + " |")
    A("")

    A("## 6.5 Distribución por tipo de imagen\n")
    A("| Tipo | Total | Intento 1 | Intento 2 | Intento 3 | Fallo |")
    A("|---|---|---|---|---|---|")
    for tipo, v in m["por_tipo"].items():
        A(f"| {tipo} | {v.get('total', 0)} | {v.get('intento1', 0)} | "
          f"{v.get('intento2', 0)} | {v.get('intento3', 0)} | {v.get('fallo', 0)} |")
    A("")

    A("## 7.x Datos para análisis de errores\n")
    A(f"- Casos con `confianza < {m['umbral_confianza']:.0f}%` pero marcados como éxito: "
      f"**{m['casos_conf_baja']}**")
    A(f"- Casos marcados como éxito con `banco_origen` o `fecha_operacion` incorrectos: "
      f"**{m['casos_exito_campo_malo']}**")
    A(f"- Casos resueltos en intento 2 donde el adaptativo habría dado mejor CER: "
      f"**{m['casos_adaptativo_mejor_que_otsu']}**")
    A("")

    A("## 8. Resumen para conclusiones\n")
    exitos = sum(r.get(k, 0) for k in (1, 2, 3))
    A(f"- Tasa de resolución automática (sin revisión humana): "
      f"**{exitos/n*100:.1f}%** ({exitos}/{n})")
    A(f"- Tasa de fallo total (requiere revisión manual): "
      f"**{r.get(0,0)/n*100:.1f}%** ({r.get(0,0)}/{n})")
    if m['cer_medio'] is not None:
        A(f"- CER medio del pipeline completo: **{m['cer_medio']:.3f}**")
        A(f"- WER medio del pipeline completo: **{m['wer_medio']:.3f}**")
    A("")

    return "\n".join(lineas)


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------
def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dataset", type=Path, required=True, help="Carpeta con las imágenes")
    ap.add_argument("--gt", type=Path, help="JSON de ground truth")
    ap.add_argument("--out", type=Path, default=Path("reporte_generado.md"))
    ap.add_argument("--plantilla", action="store_true", help="Genera plantilla de ground truth")
    ap.add_argument("--json-out", type=Path, default=Path("metricas.json"))
    ap.add_argument("--limit", type=int, help="Procesar solo las primeras N imágenes")
    args = ap.parse_args()

    imagenes = listar_imagenes(args.dataset)
    if args.limit:
        imagenes = imagenes[:args.limit]
    if not imagenes:
        print(f"[ERROR] No hay imágenes en {args.dataset}")
        sys.exit(1)

    if args.plantilla:
        plantilla = {img.name: {
            "tipo": "digital",
            "numero_referencia": "",
            "monto_detectado": 0.0,
            "banco_origen": "",
            "fecha_operacion": "YYYY-MM-DD",
            "texto_completo": "",
        } for img in imagenes}
        args.gt.write_text(json.dumps(plantilla, indent=2, ensure_ascii=False), encoding="utf-8")
        print(f"[OK] Plantilla generada: {args.gt}  ({len(imagenes)} imágenes)")
        print("Edita ese archivo y vuelve a correr sin --plantilla.")
        return

    if not args.gt or not args.gt.exists():
        print("[ERROR] Falta --gt o el archivo no existe. Usa --plantilla primero.")
        sys.exit(1)

    gt = json.loads(args.gt.read_text(encoding="utf-8"))

    faltantes = [img.name for img in imagenes if img.name not in gt]
    if faltantes:
        print(f"[ALERTA] {len(faltantes)} imágenes sin entrada en GT. Se omiten.")
        imagenes = [img for img in imagenes if img.name in gt]

    if not _JIWER:
        print("[ALERTA] jiwer no está instalado. Se omite CER/WER (secciones 6.2 y 6.3).")
    sin_texto = sum(1 for img in imagenes if not gt[img.name].get("texto_completo"))
    if _JIWER and sin_texto:
        print(f"[ALERTA] {sin_texto}/{len(imagenes)} imágenes sin 'texto_completo' en GT. "
              f"Esas no cuentan para CER/WER.")

    resultados = []
    for i, img in enumerate(imagenes, 1):
        print(f"[{i}/{len(imagenes)}] {img.name} ...", end=" ", flush=True)
        r = procesar_imagen(img)
        print(f"intento={r['intento_exito']}")
        resultados.append(r)

    metricas = calcular_metricas(resultados, gt)

    args.json_out.write_text(json.dumps(metricas, indent=2, ensure_ascii=False), encoding="utf-8")
    md = render_markdown(metricas)
    args.out.write_text(md, encoding="utf-8")

    print("\n" + "=" * 60)
    print(f"[OK] Reporte Markdown: {args.out}")
    print(f"[OK] Métricas JSON:    {args.json_out}")
    print("=" * 60)
    print(md)


if __name__ == "__main__":
    main()