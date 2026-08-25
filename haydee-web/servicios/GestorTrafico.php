<?php
namespace haydee\servicios;

use haydee\enums\HttpCodigo;
use haydee\excepciones\SeguridadException;
use haydee\excepciones\HaydeeException;

class GestorTrafico {
    private const BYTES_IV_NUEVO = 12;
    
    public static $claveActiva = null;
    public static $esCifrado = false;
    
    private static array $archivosTemporales = [];

    private static $rutasSinCifrado = [
        'handshake',
        'recuperar'
    ];

    public static function interceptarEntrada($endpoint) {
        if (in_array($endpoint, self::$rutasSinCifrado)) return;

        $inputRaw = file_get_contents('php://input');
        $inputData = json_decode($inputRaw, true) ?: []; 

        $payload = $inputData['payload'] ?? $_POST['payload'] ?? $_GET['payload'] ?? null;
        $iv = $inputData['iv'] ?? $_POST['iv'] ?? $_GET['iv'] ?? null;
        $clave_aes_rsa = $inputData['clave_aes_rsa'] ?? $_POST['clave_aes_rsa'] ?? null;

        if (!$payload || !$iv) {
            throw new SeguridadException("Acceso denegado. Se requiere establecer un canal criptográfico seguro.");
        }

        self::$esCifrado = true;
        $dispositivoId = $_SERVER['HTTP_X_DISPOSITIVO_ID'] ?? '';

        try {
            if ($clave_aes_rsa) {
                self::$claveActiva = Criptografia::extraerClaveAESdelLogin($clave_aes_rsa);
                $_POST['_temp_aes'] = self::$claveActiva;
                $_POST['_temp_disp'] = $dispositivoId;
            } else {
                self::$claveActiva = Criptografia::recuperarClaveDispositivo($dispositivoId);
                if (!self::$claveActiva) throw new SeguridadException("Dispositivo no reconocido. Canal seguro no establecido.");
            }

            $jsonDescifrado = Criptografia::descifrarPayload($payload, self::$claveActiva, $iv, $inputData['tag'] ?? $_GET['tag'] ?? '');
            
            $arregloDescifrado = json_decode($jsonDescifrado, true);
            if (is_array($arregloDescifrado)) {
                
                if (isset($arregloDescifrado['_archivos_adjuntos'])) {
                    foreach ($arregloDescifrado['_archivos_adjuntos'] as $campo => $archivo) {
                        $nombreLimpio = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $archivo['name']);
                        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('enc_') . '_' . $nombreLimpio;
                        
                        file_put_contents($tmpPath, base64_decode($archivo['base64']));
                        self::$archivosTemporales[] = $tmpPath;

                        $_FILES[$campo] = [
                            'name'     => $archivo['name'],
                            'type'     => $archivo['type'],
                            'tmp_name' => $tmpPath,
                            'error'    => UPLOAD_ERR_OK,
                            'size'     => filesize($tmpPath)
                        ];
                    }
                    unset($arregloDescifrado['_archivos_adjuntos']); 
                }

                $_POST = array_merge($_POST, $arregloDescifrado);
                $_GET = array_merge($_GET, $arregloDescifrado);
            } else {
                throw new HaydeeException("El bloque de datos descifrado no contiene un JSON válido.", HttpCodigo::BAD_REQUEST->value);
            }

        } catch (\Throwable $e) {
            throw new SeguridadException("Bloqueo criptográfico: Se ha detectado una anomalía en el túnel de datos.", HttpCodigo::PROHIBIDO->value, ['detalle' => $e->getMessage()]);
        }
    }

    public static function interceptarSalida($respuestaJsonOriginal) {
        if (self::$esCifrado && self::$claveActiva) {
            $ivNuevo = random_bytes(self::BYTES_IV_NUEVO);
            $tagNuevo = '';

            $payloadCifrado = Criptografia::cifrarPayload($respuestaJsonOriginal, self::$claveActiva, $ivNuevo, $tagNuevo);
            
            return json_encode([
                'cifrado' => true,
                'payload' => base64_encode($payloadCifrado),
                'iv'      => base64_encode($ivNuevo),
                'tag'     => base64_encode($tagNuevo)
            ]);
        }
        return $respuestaJsonOriginal;
    }

    public static function limpiarArchivosTemporales() {
        foreach (self::$archivosTemporales as $rutaArchivo) {
            if (file_exists($rutaArchivo)) {
                @unlink($rutaArchivo);
            }
        }
    }

    public static function abortarConCifrado(array $respuesta, int $codigoHttp = 400) {
        http_response_code($codigoHttp);
        $jsonRespuesta = json_encode($respuesta);
        echo self::interceptarSalida($jsonRespuesta);
        self::limpiarArchivosTemporales();
        exit;
    }
}