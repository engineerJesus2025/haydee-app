# generar_dataset_entrenamiento.py
"""
Genera dataset_entrenamiento.json a partir de:
- las imágenes del dataset
- ground_truth.json
- la salida de EasyOCR en los 3 intentos (raw, otsu, adaptativo)

Requiere: easyocr, opencv-python-headless (ya los usas).

Uso:
    python generar_dataset_entrenamiento.py \
        --dataset ./dataset \
        --gt ground_truth.json \
        --out dataset_entrenamiento.json
"""
import argparse
import json
import os
import tempfile
from datetime import datetime
from pathlib import Path

from motor_ocr import extraer_texto_de_imagen
from preprocesador import aplicar_filtro_otsu, aplicar_filtro_adaptativo

EXTENSIONES = {".jpg", ".jpeg", ".png", ".bmp", ".tif", ".tiff", ".webp"}


def _ocr_3_intentos(ruta_img: Path) -> dict:
    out = {}

    # Intento 1: raw
    txt, conf = extraer_texto_de_imagen(str(ruta_img))
    out["raw"] = {"texto": txt, "confianza": conf}

    # Intento 2: Otsu
    with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as f:
        tmp = f.name
    try:
        aplicar_filtro_otsu(str(ruta_img), tmp)
        txt, conf = extraer_texto_de_imagen(tmp)
        out["otsu"] = {"texto": txt, "confianza": conf}
    finally:
        os.remove(tmp)

    # Intento 3: Adaptativo
    with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as f:
        tmp = f.name
    try:
        aplicar_filtro_adaptativo(str(ruta_img), tmp)
        txt, conf = extraer_texto_de_imagen(tmp)
        out["adaptativo"] = {"texto": txt, "confianza": conf}
    finally:
        os.remove(tmp)

    return out


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dataset", type=Path, required=True)
    ap.add_argument("--gt", type=Path, required=True)
    ap.add_argument("--out", type=Path, default=Path("dataset_entrenamiento.json"))
    args = ap.parse_args()

    gt = json.loads(args.gt.read_text(encoding="utf-8"))
    imagenes = sorted(p for p in args.dataset.rglob("*") if p.suffix.lower() in EXTENSIONES)

    salida = {
        "metadata": {
            "fecha_generacion": datetime.now().isoformat(timespec="seconds"),
            "n_imagenes": len(imagenes),
            "version": "1.0",
        },
        "imagenes": {},
    }

    omitidas = 0
    for i, img in enumerate(imagenes, 1):
        if img.name not in gt:
            print(f"[SKIP] {img.name}: sin GT")
            omitidas += 1
            continue

        print(f"[{i}/{len(imagenes)}] {img.name} ...", end=" ", flush=True)
        intentos = _ocr_3_intentos(img)
        gt_img = gt[img.name]

        salida["imagenes"][img.name] = {
            "ruta_original": str(img),
            "tipo": gt_img.get("tipo", "desconocido"),
            "textos_ocr": intentos,
            "campos_correctos": {
                "numero_referencia": gt_img.get("numero_referencia", ""),
                "monto_detectado": gt_img.get("monto_detectado", 0.0),
                "banco_origen": gt_img.get("banco_origen", ""),
                "fecha_operacion": gt_img.get("fecha_operacion", ""),
            },
            "texto_completo_gt": gt_img.get("texto_completo", ""),
        }
        print("ok")

    args.out.write_text(json.dumps(salida, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"\n[OK] Guardado: {args.out}")
    print(f"     Imágenes: {len(salida['imagenes'])}  (omitidas: {omitidas})")


if __name__ == "__main__":
    main()