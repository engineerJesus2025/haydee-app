<?php
namespace haydee\servicios;

class EscanerComprobantes
{
    private const URL_API = 'https://condohaydee-haydee-ocr.hf.space/v1/ocr'; 
    private const API_KEY = 'HaydeeSegura2026*'; 

    /**
     * gestiona la validación, la llamada a la API y las reglas de negocio.
     */
    public static function procesarPeticion($archivo)
    {
        // Validación inicial del archivo
        if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ['estatus' => false, 'mensaje' => 'No se recibió ninguna imagen válida.'];
        }

        // Comunicación con el microservicio
        $resultadoOCR = self::enviarPeticionApi(
            $archivo['tmp_name'], 
            $archivo['name'], 
            $archivo['type']
        );

        if (!$resultadoOCR['estatus']) {
            return $resultadoOCR;
        }

        // Evaluación de Confianza
        $datosMotor = $resultadoOCR['datos_ocr'];
        $confianza = $datosMotor['metadatos']['confianza'] ?? 0;
        
        if ($datosMotor['metadatos']['estado'] === 'exito' && $confianza >= 75.0) {
            return [
                'estatus' => true,
                'mensaje' => 'Comprobante leído con éxito',
                'monto' => $datosMotor['datos']['monto_detectado'] ?? null,
                'referencia' => $datosMotor['datos']['numero_referencia'] ?? null,
                'banco' => $datosMotor['datos']['banco_origen'] ?? null,
                'fecha' => $datosMotor['datos']['fecha_operacion'] ?? null 
            ];
        } 
        
        // Falla por confianza baja o lectura parcial: Devolvemos los datos recuperados de todos modos
        return [
            'estatus' => false, 
            'mensaje' => 'La calidad de la imagen es baja o faltan datos. Por favor, verifique y complete los valores.',
            'confianza' => $confianza,
            'monto' => $datosMotor['datos']['monto_detectado'] ?? null,
            'referencia' => $datosMotor['datos']['numero_referencia'] ?? null,
            'banco' => $datosMotor['datos']['banco_origen'] ?? null,
            'fecha' => $datosMotor['datos']['fecha_operacion'] ?? null 
        ];
    }

    /**
     * para la comunicación cURL con Python.
     */
    private static function enviarPeticionApi($rutaTemporal, $nombreArchivo, $tipoMime)
    {
        if (!file_exists($rutaTemporal)) { 
            return ['estatus' => false, 'mensaje' => 'El archivo temporal no existe.']; 
        } 

        $archivoCurl = new \CURLFile($rutaTemporal, $tipoMime, $nombreArchivo); 
        $datosPost = ['file' => $archivoCurl]; 

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, self::URL_API); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datosPost); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 
            'X-API-Key: ' . self::API_KEY 
        ]); 

        $respuestaJson = curl_exec($ch); 
        $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        $errorCurl = curl_error($ch); 
        curl_close($ch); 

        if ($errorCurl || $codigoHttp !== 200) { 
            error_log("Error de conexión con Microservicio OCR. HTTP: $codigoHttp. Detalle: $errorCurl"); 
            return ['estatus' => false, 'mensaje' => 'El motor de escaneo no está disponible en este momento.']; 
        } 

        $datosDecodificados = json_decode($respuestaJson, true); 
        
        if (!$datosDecodificados) { 
            return ['estatus' => false, 'mensaje' => 'Respuesta inválida del motor OCR.']; 
        } 

        return [ 
            'estatus' => true, 
            'datos_ocr' => $datosDecodificados 
        ]; 
    }
}
