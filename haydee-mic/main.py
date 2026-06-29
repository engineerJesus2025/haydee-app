from fastapi import FastAPI, File, UploadFile, Header, HTTPException
from motor_ocr import ejecutar_pipeline_ocr
import shutil
import os

app = FastAPI()

# API Key de seguridad
API_KEY_SECRETA = "HaydeeSegura2026*"

@app.post("/v1/ocr")
async def procesar_comprobante(
    file: UploadFile = File(...), 
    x_api_key: str = Header(None, alias="X-API-Key")
):
    if x_api_key != API_KEY_SECRETA:
        raise HTTPException(status_code=401, detail="No autorizado. API Key inválida.")
        
    ruta_temporal = f"temp_{file.filename}"
    with open(ruta_temporal, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)
        
    resultado = ejecutar_pipeline_ocr(ruta_temporal)
    
    # Limpiamos el archivo temporal
    if os.path.exists(ruta_temporal):
        os.remove(ruta_temporal)
        
    return resultado