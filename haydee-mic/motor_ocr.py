import easyocr
import re
import os
from preprocesador import aplicar_filtro_otsu, aplicar_filtro_adaptativo

def corregir_errores_ocr_numericos(texto_sucio: str) -> str:
    reemplazos = {'U': '0', 'O': '0', 'o': '0', 'I': '1', 'l': '1', 't': '1', 'S': '5', 's': '5', 'B': '8'}
    texto_limpio = texto_sucio
    for letra, numero in reemplazos.items():
        texto_limpio = texto_limpio.replace(letra, numero)
    return texto_limpio

def buscar_patrones_bancarios_universal(texto: str) -> dict:
    datos = {
        "numero_referencia": None,
        "monto_detectado": None,
        "banco_origen": "Desconocido",
        "fecha_operacion": None  # <-- NUEVO CAMPO
    }
    texto_min = texto.lower()

    texto_sin_receptor = re.sub(r"(?:\ba\s*:|destino)[\s\S]*?(monto|referencia|concepto)", r"\1", texto_min)

    mapa_bancos = {
        "0108": ["provincial", "bbva", "0108", "banco provincial"],
        "0102": ["venezuela", "bdv", "0102", "pagomóvilbdv", "banco de venezuela"],
        "0105": ["mercantil", "tpago", "0105", "banco mercantil"],
        "0134": ["banesco", "0134"],
        "0172": ["bancamiga", "0172"],
        "0191": ["bnc", "nacional de crédito", "nacional de credito", "0191"],
        "0114": ["bancaribe", "0114"],
        "0175": ["bicentenario", "0175", "banco bicentenario"],
        "0163": ["tesoro", "0163", "banco del tesoro"]
    }

    for nombre_banco, palabras_clave in mapa_bancos.items():
        if any(clave in texto_sin_receptor for clave in palabras_clave):
            datos["banco_origen"] = nombre_banco
            break

    # --- REFERENCIA ---
    patron_ref = re.search(
        r"(?:(?:nro\.?|número|numero)\s*(?:de\s*)?)?"
        r"(?:ref(?:erenc(?:ia|la))?|operaci[óo]n(?:es)?|doc(?:umento)?|recibo)"
        r"[\s\.\:\-]*(?:es\b)?[\s\.\:\-]*(?:nro\.?|no\.?)?[\s\.\:\-]*"
        r"([0-9oilsz]{6,25})\b", 
        texto_min, re.IGNORECASE
    )
    
    if patron_ref:
        ref_sucia = patron_ref.group(1).strip()
        datos["numero_referencia"] = corregir_errores_ocr_numericos(ref_sucia).upper()
    else:
        numeros_largos = re.findall(r"\b\d{6,20}\b", texto)
        for num in numeros_largos:
            prefijos_ignorados = tuple([claves[-1] for claves in mapa_bancos.values()])
            if not num.startswith(prefijos_ignorados):
                datos["numero_referencia"] = corregir_errores_ocr_numericos(num)
                break

    # --- MONTO ---
    patron_monto_explicito = re.search(r"monto.*?([\d\.\,\-]+(?:[ \t]+[\d\.\,\-]+)*)", texto_min)
    monto_str = None
    
    if patron_monto_explicito:
        monto_str = patron_monto_explicito.group(1)
    else:
        patron_bs = re.search(r"(?:bs\.?[ \t]*([\d\.\,\-]+(?:[ \t]+[\d\.\,\-]+)*)|([\d\.\,\-]+(?:[ \t]+[\d\.\,\-]+)*)[ \t]*bs\.?)", texto_min)
        if patron_bs:
            monto_str = patron_bs.group(1) or patron_bs.group(2)

    if monto_str:
        monto_str = re.sub(r"[^\d\.\,]", "", monto_str) 
        if "." in monto_str and "," in monto_str:
            monto_str = monto_str.replace(".", "").replace(",", ".")
        elif "," in monto_str:
            monto_str = monto_str.replace(",", ".")
            
        try:
            datos["monto_detectado"] = float(monto_str)
        except ValueError:
            pass

    patron_fecha = re.search(r"\b(\d{2})[\/\-\s]+(\d{2})[\/\-\s]+(\d{4})\b", texto_min)
    
    if patron_fecha:
        dia = patron_fecha.group(1)
        mes = patron_fecha.group(2)
        anio = patron_fecha.group(3)
        datos["fecha_operacion"] = f"{anio}-{mes}-{dia}"

    return datos

def extraer_texto_de_imagen(ruta_imagen: str) -> tuple[str, float]:
    try:
        lector = easyocr.Reader(['es'], gpu=False, verbose=False) 
        
        bloques = lector.readtext(ruta_imagen) 
        
        if not bloques:
            return "", 0.0
            
        textos = []
        confianzas = []
        
        for b in bloques:
            # b[1] es el texto, b[2] es la confianza (un float entre 0.0 y 1.0)
            textos.append(b[1])
            confianzas.append(b[2])
            
        texto_completo = " ".join(textos)
        # Calculamos el promedio de confianza de toda la imagen y lo llevamos a escala 0-100%
        confianza_promedio = round((sum(confianzas) / len(confianzas)) * 100, 2)
        
        return texto_completo, confianza_promedio
        
    except Exception as error:
        print(f"[ERROR OCR] {str(error)}")
        return "", 0.0

def ejecutar_pipeline_ocr(ruta_imagen_original: str) -> dict:
    ruta_temporal = "temp_procesada.jpg"
    
    # IMAGEN CRUDA
    print("\n[PIPELINE] -> INTENTO 1: Lectura Cruda (Sin filtros)...")
    texto_raw, confianza = extraer_texto_de_imagen(ruta_imagen_original)
    print(f"[DIAGNÓSTICO RAW] -> {texto_raw} (Confianza: {confianza}%)")
    resultado = buscar_patrones_bancarios_universal(texto_raw)
    
    if resultado["numero_referencia"] and resultado["monto_detectado"]:
        print("[PIPELINE] ¡Éxito en Intento 1 (Imagen Cruda)!")
        return {
            "metadatos": {
                "estado": "exito", 
                "filtro": "NINGUNO",
                "confianza": confianza
            }, 
            "datos": resultado
        }

    # INTENTO 2 con OTSU
    print("\n[PIPELINE] -> INTENTO 2: Filtro Otsu...")
    aplicar_filtro_otsu(ruta_imagen_original, ruta_temporal)
    texto_otsu, confianza_otsu = extraer_texto_de_imagen(ruta_temporal)
    print(f"[DIAGNÓSTICO OTSU] -> {texto_otsu} (Confianza: {confianza_otsu}%)")
    resultado_otsu = buscar_patrones_bancarios_universal(texto_otsu)
    
    resultado["numero_referencia"] = resultado["numero_referencia"] or resultado_otsu["numero_referencia"]
    resultado["monto_detectado"] = resultado["monto_detectado"] or resultado_otsu["monto_detectado"]
    if resultado["banco_origen"] == "Desconocido": resultado["banco_origen"] = resultado_otsu["banco_origen"]
    
    # Si usamos datos combinados, promediamos las confianzas válidas
    confianza_final = round((confianza + confianza_otsu) / 2, 2)
    
    if resultado["numero_referencia"] and resultado["monto_detectado"]:
        print("[PIPELINE] ¡Éxito acumulado en Intento 2 (Otsu)!")
        if os.path.exists(ruta_temporal): os.remove(ruta_temporal)
        return {
            "metadatos": {
                "estado": "exito", 
                "filtro": "COMBINADO_OTSU",
                "confianza": confianza_final
            }, 
            "datos": resultado
        }

    # INTENTO 3 ADAPTATIVO
    print("\n[PIPELINE] -> INTENTO 3: Filtro Adaptativo...")
    aplicar_filtro_adaptativo(ruta_imagen_original, ruta_temporal)
    texto_adaptativo, confianza_adap = extraer_texto_de_imagen(ruta_temporal)
    print(f"[DIAGNÓSTICO ADAPTATIVO] -> {texto_adaptativo} (Confianza: {confianza_adap}%)")
    resultado_adaptativo = buscar_patrones_bancarios_universal(texto_adaptativo)
    
    if os.path.exists(ruta_temporal): os.remove(ruta_temporal)
    
    resultado["numero_referencia"] = resultado["numero_referencia"] or resultado_adaptativo["numero_referencia"]
    resultado["monto_detectado"] = resultado["monto_detectado"] or resultado_adaptativo["monto_detectado"]
    if resultado["banco_origen"] == "Desconocido": resultado["banco_origen"] = resultado_adaptativo["banco_origen"]
    
    confianza_final = round((confianza + confianza_otsu + confianza_adap) / 3, 2)
    
    if resultado["numero_referencia"] and resultado["monto_detectado"]:
        print("[PIPELINE] ¡Éxito acumulado en Intento 3 (Adaptativo)!")
        return {
            "metadatos": {
                "estado": "exito", 
                "filtro": "COMBINADO_ADAPTATIVO",
                "confianza": confianza_final
            }, 
            "datos": resultado
        }

    # FALLO TOTAL
    print("[PIPELINE] [ALERTA] Los 3 intentos fallaron parcialmente. Se requiere revisión humana.")
    return {
        "metadatos": {
            "estado": "parcial", 
            "requiere_revision_manual": True,
            "confianza": confianza_final
        }, 
        "datos": resultado
    }

# Para probarlo en local (si les agarra, porque mi pc no lo soporto xd)
# Cambien archivo_prueba por una imagen de ustedes.
if __name__ == "__main__":
    archivo_prueba = "capture_12.jpeg" 
    json_respuesta = ejecutar_pipeline_ocr(archivo_prueba)
    print("\n================ RESPUESTA ================")
    print(json_respuesta)