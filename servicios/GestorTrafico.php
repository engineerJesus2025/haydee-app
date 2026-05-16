<?php
namespace haydee\servicios;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use haydee\enums\HttpCodigo;

class GestorTrafico {
    private const JWT_ALGORITMO = 'HS256';
    private const BYTES_IV_NUEVO = 12;
    
    public static $claveActiva = null;
    public static $esCifrado = false;
    public static $usuarioLogueado = null;

    // Lista de endpoints que escapan del túnel criptográfico
    private static $rutasSinCifrado = [
        'handshake'
    ];

    // Lista de endpoints que no requieren identidad (JWT)
    private static $rutasSinJWT = [
        'handshake',
        'login'
    ];

    public static function interceptarEntrada($endpoint) {
        if (in_array($endpoint, self::$rutasSinCifrado)) return;

        // EVALUACIÓN DE JWT (Identidad)
        if (!in_array($endpoint, self::$rutasSinJWT)) {
            $headers = apache_request_headers();
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $headers['Authorization'] ?? $headers['authorization'] ?? '';

            if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                http_response_code(HttpCodigo::NO_AUTORIZADO->value);
                echo json_encode(["estatus" => false, "mensaje" => "Falta el token de seguridad."]);
                exit; 
            }

            try {
                $decoded = JWT::decode($matches[1], new Key(JWT_SECRET, self::JWT_ALGORITMO));
                self::$usuarioLogueado = (array) $decoded->data;
            } catch (\Exception $e) {
                // ESTE ES EL 401 QUE REACT NATIVE DEBE CAPTURAR PARA RENOVAR SESIÓN
                http_response_code(HttpCodigo::NO_AUTORIZADO->value);
                echo json_encode(["estatus" => false, "mensaje" => "Sesión inválida o expirada."]);
                exit; 
            }
        }

        // PROCESAMIENTO CRIPTOGRÁFICO
        $inputRaw = file_get_contents('php://input');
        $inputData = json_decode($inputRaw, true) ?: []; 

        $payload = $inputData['payload'] ?? $_POST['payload'] ?? $_GET['payload'] ?? null;
        $iv = $inputData['iv'] ?? $_POST['iv'] ?? $_GET['iv'] ?? null;
        $clave_aes_rsa = $inputData['clave_aes_rsa'] ?? $_POST['clave_aes_rsa'] ?? null;

        if (!$payload || !$iv) {
            http_response_code(HttpCodigo::PROHIBIDO->value);
            echo json_encode(["estatus" => false, "mensaje" => "Acceso denegado. Se requiere canal seguro."]);
            exit; // Texto plano, porque no tenemos llave aún
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
                // (Mantenemos tu lógica de archivos intacta)
                if (isset($arregloDescifrado['_archivos_adjuntos'])) {
                    foreach ($arregloDescifrado['_archivos_adjuntos'] as $campo => $archivo) {
                        $nombreLimpio = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $archivo['name']);
                        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('enc_') . '_' . $nombreLimpio;
                        file_put_contents($tmpPath, base64_decode($archivo['base64']));
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
                throw new \Exception("JSON inválido.");
            }

        } catch (\Exception $e) {
            http_response_code(HttpCodigo::PROHIBIDO->value); // 403 para no confundir con JWT
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
}