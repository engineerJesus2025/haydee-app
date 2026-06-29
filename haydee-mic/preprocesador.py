import cv2

def aplicar_filtro_otsu(ruta_origen: str, ruta_destino: str) -> bool:
    """
    para capturas digitales perfectas de cualquier banco.
    Elimina fondos de color y deja el texto nítido.
    """
    try:
        imagen = cv2.imread(ruta_origen, cv2.IMREAD_GRAYSCALE)
        if imagen is None:
            return False
            
        # umbral global de Otsu
        _, imagen_final = cv2.threshold(imagen, 0, 255, cv2.THRESH_BINARY | cv2.THRESH_OTSU)
        
        cv2.imwrite(ruta_destino, imagen_final)
        return True
    except Exception as e:
        print(f"Error en Otsu: {e}")
        return False

def aplicar_filtro_adaptativo(ruta_origen: str, ruta_destino: str) -> bool:
    """
    para fotos de recibos con sombras.
    Calcula el umbral por bloques grandes y aplica un filtro de mediana
    para quitar los puntitos de ruido del fondo.
    """
    try:
        imagen = cv2.imread(ruta_origen, cv2.IMREAD_GRAYSCALE)
        if imagen is None:
            return False
            
        # Suavizado Gaussiano inicial
        imagen_suave = cv2.GaussianBlur(imagen, (5, 5), 0)
        
        # Umbral adaptativo con bloque más grande
        imagen_binaria = cv2.adaptiveThreshold(
            imagen_suave, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 41, 5
        )
        
        # FILTRO DE MEDIANA
        # El número 3 es el tamaño del filtro. Borra imperfecciones aisladas sin deformar tu letra.
        imagen_limpia = cv2.medianBlur(imagen_binaria, 3)
        
        cv2.imwrite(ruta_destino, imagen_limpia)
        return True
    except Exception as e:
        print(f"Error en Adaptativo optimizado: {e}")
        return False

# Para probarlo en local (cambien archivo_prueba por una imagen que prefieran, tampoco hace gran cosa, solo le pone filtros a imagenes)
if __name__ == "__main__":
    print("=== PROBANDO LA CAJA DE HERRAMIENTAS DE PROCESAMIENTO ===")
    
    archivo_prueba = "img/capture_1.jpg"  # Tu captura del Banco de Venezuela
    
    # Forzamos la generación del archivo usando el Filtro Otsu
    print("\n[PROBANDO] Generando versión Otsu (Para captures)...")
    salida_otsu = "resultado_otsu.jpg"
    if aplicar_filtro_otsu(archivo_prueba, salida_otsu):
        print(f"¡Éxito! Archivo guardado como: {salida_otsu}")
    else:
        print("Falló el filtro Otsu.")
        
    # Forzamos la generación del archivo usando el Filtro Adaptativo
    print("\n[PROBANDO] Generando versión Adaptativa (Para fotos)...")
    salida_adaptativa = "resultado_adaptativo.jpg"
    if aplicar_filtro_adaptativo(archivo_prueba, salida_adaptativa):
        print(f"¡Éxito! Archivo guardado como: {salida_adaptativa}")
    else:
        print("Falló el filtro Adaptativo.")
        
    print("\n=== PRUEBA FINALIZADA ===")
    print("Revisa tu carpeta. Deberías tener 'resultado_otsu.jpg' y 'resultado_adaptativo.jpg'.")