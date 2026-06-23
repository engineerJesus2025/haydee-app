<?php
namespace haydee\servicios;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;
use haydee\modelo\ClaveSesion;

/*
Nota: 
RSA (Rivest-Shamir-Adleman) es uno de los algoritmos de criptografía asimétrica (o de clave pública) 
AES significa Advanced Encryption Standard (Estándar de Cifrado Avanzado). Es un algoritmo de cifrado por bloques simétrico
*/

class Criptografia {
    private const RSA_KEY_SIZE = 4096;
    private const AES_MODE = 'gcm';
    private const HASH_ALGO = 'sha256';
    
    // Genera un par de llaves RSA para el Handshake ----- Ya no se usa
    public static function generarLlavesRSA() {
        $private = RSA::createKey(self::RSA_KEY_SIZE);
        $public = $private->getPublicKey();
        
        return [
            'privada' => $private->toString('PKCS8'),
            'publica' => $public->toString('PKCS8')
        ];
    }

    // Descifra la clave AES que viene de la App (cifrada con RSA)
    public static function descifrarClaveAES($claveCifradaBase64, $llavePrivadaPem) {
        $privateKey = RSA::load($llavePrivadaPem)
            ->withPadding(RSA::ENCRYPTION_OAEP);
            
        return $privateKey->decrypt(base64_decode($claveCifradaBase64));
    }

    // Descifra los datos (payload) usando AES-256-GCM
    public static function descifrarPayload($payloadBase64, $claveAES, $ivBase64, $tagBase64) {
        $aes = new AES(self::AES_MODE);
        $aes->setKey($claveAES);
        $aes->setNonce(base64_decode($ivBase64));
        $aes->setTag(base64_decode($tagBase64));
        
        return $aes->decrypt(base64_decode($payloadBase64));
    }

    // Este método es llamado por el GestorTrafico para descifrar la llave AES en el Login
    public static function extraerClaveAESdelLogin($claveRsaBase64) {
        $llavePrivadaPem = file_get_contents(ROOT_PATH . '/config/llave_servidor_privada.pem');
        
        // OBLIGAMOS a PHP a usar SHA-256 para que coincida exactamente con la App
        $privateKey = RSA::load($llavePrivadaPem)
            ->withPadding(RSA::ENCRYPTION_OAEP)
            ->withHash(self::HASH_ALGO)
            ->withMGFHash(self::HASH_ALGO);
            
        return $privateKey->decrypt(base64_decode($claveRsaBase64));
    }

    // Este método asume la responsabilidad del registro en la BD (Lo llama login_api.php)
    public static function vincularDispositivoUsuario($dispositivo_id, $usuario_id, $clave_aes_raw) {
        $modelo = new ClaveSesion();
        // Guardamos la clave en Base64 para evitar problemas de codificación en MySQL
        return $modelo->guardarClave($dispositivo_id, $usuario_id, base64_encode($clave_aes_raw));
    }

    // Este método lo usa el GestorTrafico para las peticiones normales (Ej: Pagos)
    public static function recuperarClaveDispositivo($dispositivo_id) {
        $modelo = new ClaveSesion();
        $claveB64 = $modelo->obtenerClave($dispositivo_id);
        return $claveB64 ? base64_decode($claveB64) : null;
    }

    // Cifra los datos (payload) de salida usando AES-256-GCM
    public static function cifrarPayload($datosJSON, $claveAESRaw, $ivRaw, &$tagRaw) {
        $aes = new AES(self::AES_MODE);
        $aes->setKey($claveAESRaw);
        $aes->setNonce($ivRaw);
        
        $textoCifrado = $aes->encrypt($datosJSON);
        $tagRaw = $aes->getTag(); 
        
        return $textoCifrado;
    }

    /**
     * Genera un código numérico criptográficamente seguro (OTP) Para los correos de recuperacion de la app
     */
    public static function generarOTP(int $longitud = 6): string 
    {
        // Calculamos los límites matemáticos según la longitud
        // Ej: para 6 dígitos -> min = 100000, max = 999999
        $min = 10 ** ($longitud - 1);
        $max = (10 ** $longitud) - 1;
        
        return (string) random_int($min, $max);
    }

    /**
     * Rompe la vinculación criptográfica eliminando el registro del dispositivo.
     */
    public static function desvincularDispositivo($dispositivo_id) {
        $modelo = new ClaveSesion();
        $resultado = $modelo->eliminarClave($dispositivo_id);
        $modelo->cerrar(); 
        
        return $resultado;
    }

}