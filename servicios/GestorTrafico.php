<?php
namespace haydee\servicios;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use haydee\enums\HttpCodigo;
use haydee\modelo\Rol;
use haydee\servicios\Sesiones;

class GestorTrafico {
    private const JWT_ALGORITMO = 'HS256';
    private const BYTES_IV_NUEVO = 12;
    
    public static $claveActiva = null;
    public static $esCifrado = false;
    public static $usuarioLogueado = null;
    
    // Rastreador estático para la limpieza segura de archivos temporales
    private static array $archivosTemporales = [];

    // Lista de endpoints que escapan del túnel criptográfico
    private static $rutasSinCifrado = [
        'handshake'
    ];

    // Lista de endpoints que no requieren identidad (JWT)
    private static $rutasSinJWT = [
        'handshake',
        'login',
        'recuperar',
        'refrescar'
    ];

    public static function interceptarEntrada($endpoint) {
        if (in_array($endpoint, self::$rutasSinCifrado)) return;

        // ==================== EVALUACIÓN DE JWT (IDENTIDAD) ====================
        if (!in_array($endpoint, self::$rutasSinJWT)) {
            
            self::$usuarioLogueado = Sesiones::validarAutenticacionJWT();
        }

        // ==================== PROCESAMIENTO CRIPTOGRÁFICO ====================
        $inputRaw = file_get_contents('php://input');
        $inputData = json_decode($inputRaw, true) ?: []; 

        $payload = $inputData['payload'] ?? $_POST['payload'] ?? $_GET['payload'] ?? null;
        $iv = $inputData['iv'] ?? $_POST['iv'] ?? $_GET['iv'] ?? null;
        $clave_aes_rsa = $inputData['clave_aes_rsa'] ?? $_POST['clave_aes_rsa'] ?? null;

        if (!$payload || !$iv) {
            http_response_code(HttpCodigo::PROHIBIDO->value);
            echo json_encode(["estatus" => false, "mensaje" => "Acceso denegado. Se requiere canal seguro."]);
            exit;
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
                if (!self::$claveActiva) throw new \Exception("Canal seguro no establecido.");
            }

            $jsonDescifrado = Criptografia::descifrarPayload($payload, self::$claveActiva, $iv, $inputData['tag'] ?? $_GET['tag'] ?? '');
            
            $arregloDescifrado = json_decode($jsonDescifrado, true);
            if (is_array($arregloDescifrado)) {
                
                // Reconstrucción controlada de archivos adjuntos (Móvil)
                if (isset($arregloDescifrado['_archivos_adjuntos'])) {
                    foreach ($arregloDescifrado['_archivos_adjuntos'] as $campo => $archivo) {
                        $nombreLimpio = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $archivo['name']);
                        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('enc_') . '_' . $nombreLimpio;
                        
                        file_put_contents($tmpPath, base64_decode($archivo['base64']));
                        
                        // Guardamos la ruta para eliminarla al finalizar la petición
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

                // Inyección unificada en superglobales: Los endpoints leerán de aquí directamente limpia el payload
                $_POST = array_merge($_POST, $arregloDescifrado);
                $_GET = array_merge($_GET, $arregloDescifrado);
            } else {
                throw new \Exception("JSON inválido.");
            }

        } catch (\Exception $e) {
            http_response_code(HttpCodigo::PROHIBIDO->value);
            echo json_encode(["estatus" => false, "mensaje" => "Bloqueo criptográfico."]);
            exit;
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

    /**
     * Recorre y destruye todos los archivos temporales creados manualmente en la petición.
     * Previene ataques DoS de llenado de almacenamiento en disco duro.
     */
    public static function limpiarArchivosTemporales() {
        foreach (self::$archivosTemporales as $rutaArchivo) {
            if (file_exists($rutaArchivo)) {
                @unlink($rutaArchivo);
            }
        }
    }
}