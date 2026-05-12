<?php
namespace haydee\servicios;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GestorTrafico {
    
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

        // EVALUACIÓN DE CIFRADO (Privacidad)
        if (in_array($endpoint, self::$rutasSinCifrado)) {
            return; // El handshake sale por aquí intacto
        }

        // EVALUACIÓN DE JWT (Identidad)
        if (!in_array($endpoint, self::$rutasSinJWT)) {
            // Buscamos el token en las cabeceras HTTP (soporta Apache y Nginx)
            $headers = apache_request_headers();
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $headers['Authorization'] ?? $headers['authorization'] ?? '';

            if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                http_response_code(401);
                echo json_encode(["estatus" => false, "mensaje" => "Falta el token de sesión (JWT) o formato inválido."]);
                exit;
            }

            $jwt = $matches[1];
            try {
                // Si la firma fue alterada o el tiempo expiró, lanza excepción
                $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
                self::$usuarioLogueado = (array) $decoded->data;
            } catch (\Exception $e) {
                http_response_code(401);
                echo json_encode(["estatus" => false, "mensaje" => "Sesión inválida o expirada. Vuelva a ingresar."]);
                exit;
            }
        }

        // PROCESAMIENTO CRIPTOGRÁFICO
        $inputRaw = file_get_contents('php://input');
        // Usamos ?: [] para que si json_decode falla, devuelva un arreglo vacío en lugar de null
        $inputData = json_decode($inputRaw, true) ?: []; 

        // Ahora buscamos en 3 dimensiones: JSON (Cuerpo), POST (Formulario con archivos) y GET (URL)
        $payload = $inputData['payload'] ?? $_POST['payload'] ?? $_GET['payload'] ?? null;
        $iv = $inputData['iv'] ?? $_POST['iv'] ?? $_GET['iv'] ?? null;
        $tag = $inputData['tag'] ?? $_POST['tag'] ?? $_GET['tag'] ?? null;
        $clave_aes_rsa = $inputData['clave_aes_rsa'] ?? $_POST['clave_aes_rsa'] ?? null;

        if (!$payload || !$iv) {
            http_response_code(403);
            echo json_encode(["estatus" => false, "mensaje" => "Acceso denegado. Se requiere canal seguro."]);
            exit;
        }

        self::$esCifrado = true;
        $dispositivoId = $_SERVER['HTTP_X_DISPOSITIVO_ID'] ?? '';

        try {
            if ($clave_aes_rsa) {
                // Handshake Atómico (Login)
                self::$claveActiva = Criptografia::extraerClaveAESdelLogin($clave_aes_rsa);
                $_POST['_temp_aes'] = self::$claveActiva;
                $_POST['_temp_disp'] = $dispositivoId;
            } else {
                // Petición normal (Pagos, Gastos)
                self::$claveActiva = Criptografia::recuperarClaveDispositivo($dispositivoId);
                if (!self::$claveActiva) throw new \Exception("Canal seguro no establecido o llave expirada.");
            }

            $jsonDescifrado = Criptografia::descifrarPayload(
                $payload, self::$claveActiva, $iv, $inputData['tag'] ?? $_GET['tag'] ?? ''
            );
            
            $arregloDescifrado = json_decode($jsonDescifrado, true);
            if (is_array($arregloDescifrado)) {
                // --- RECONSTRUCCIÓN DE ARCHIVOS CIFRADOS ---
                if (isset($arregloDescifrado['_archivos_adjuntos'])) {
                    foreach ($arregloDescifrado['_archivos_adjuntos'] as $campo => $archivo) {
                        // Creamos un archivo físico temporal en el servidor
                        $nombreLimpio = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $archivo['name']);
                        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('enc_') . '_' . $nombreLimpio;
                        
                        // Decodificamos el Base64 y guardamos la imagen
                        file_put_contents($tmpPath, base64_decode($archivo['base64']));
                        
                        // Inyectamos el archivo en $_FILES para engañar a tus APIs
                        $_FILES[$campo] = [
                            'name'     => $archivo['name'],
                            'type'     => $archivo['type'],
                            'tmp_name' => $tmpPath,
                            'error'    => UPLOAD_ERR_OK,
                            'size'     => filesize($tmpPath)
                        ];
                    }
                    unset($arregloDescifrado['_archivos_adjuntos']); // Limpiamos la basura
                }

                // Inyectamos el texto limpio en $_POST y $_GET
                $_POST = array_merge($_POST, $arregloDescifrado);
                $_GET = array_merge($_GET, $arregloDescifrado);
            } else {
                throw new \Exception("El payload descifrado no es un JSON válido.");
            }

        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["estatus" => false, "mensaje" => "Bloqueo de seguridad. Intente mas tarde"]);
            error_log("Error al interceptar Entrada " . $e->getMessage());
            exit;
        }
    }

    public static function interceptarSalida($respuestaJsonOriginal) {
        if (self::$esCifrado && self::$claveActiva) {
            $ivNuevo = random_bytes(12);
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