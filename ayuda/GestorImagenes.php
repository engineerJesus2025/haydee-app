<?php
namespace haydee\ayuda;

class GestorImagenes
{
    // CONSTANTES DE SISTEMA DE ARCHIVOS
    private const TAMANO_MAXIMO_BYTES = 5242880; // 5MB
    private const PERMISOS_DIRECTORIO = 0777;
    private const IMAGEN_POR_DEFECTO = 'default.png';
    private const DIRECTORIO_BASE = 'recursos' . DIRECTORY_SEPARATOR . 'img';

    /**
     * Sube una imagen desde un archivo subido.
     */
    public static function subir($archivo, $carpeta, $maxSize = 5242880, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
    {
        // Validar que se haya subido correctamente
        if (!isset($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            $codigoError = $archivo['error'] ?? 'desconocido';
            error_log("GestorImagenes: Error de subida (código $codigoError)");
            return false;
        }

        // Validar tamaño
        if ($archivo['size'] > $maxSize) {
            error_log("GestorImagenes: Archivo demasiado grande ({$archivo['size']} bytes, máximo $maxSize)");
            return false;
        }

        // Validar tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            error_log("GestorImagenes: No se pudo abrir fileinfo");
            return false;
        }
        $mime = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            // Fallback a extensión
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $extMap = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp'
            ];
            if (!isset($extMap[$ext]) || !in_array($extMap[$ext], $allowedTypes)) {
                error_log("GestorImagenes: Tipo de archivo no permitido (MIME: $mime, extensión: $ext)");
                return false;
            }
        }

        // Generar nombre único
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreSanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($archivo['name'], PATHINFO_FILENAME));
        $nombreUnico = $nombreSanitizado . '_' . time() . '_' . rand(100, 999) . '.' . $extension;

        // Ruta destino
        $rutaDestino = self::getRutaCompleta($nombreUnico, $carpeta);

        // Crear directorio si no existe
        $directorio = dirname($rutaDestino);
        if (!is_dir($directorio)) {
            if (!mkdir($directorio, self::PERMISOS_DIRECTORIO, true)) {
                error_log("GestorImagenes: No se pudo crear el directorio $directorio");
                return false;
            }
        }
        // rename($archivo['tmp_name'], $rutaDestino);
        // if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        if (rename($archivo['tmp_name'], $rutaDestino)) {
            return $nombreUnico;
        } else {
            error_log("GestorImagenes: Error al mover el archivo a $rutaDestino");
            return false;
        }
    }

    /**
     * Elimina una imagen del servidor.
     */
    public static function eliminar($nombreArchivo, $carpeta)
    {
        if (empty($nombreArchivo) || $nombreArchivo === self::IMAGEN_POR_DEFECTO) {
            return false;
        }
        $ruta = self::getRutaCompleta($nombreArchivo, $carpeta);
        if (file_exists($ruta)) {
            return unlink($ruta);
        }
        return false;
    }

    /**
     * Obtiene la ruta absoluta de una imagen.
     */
    public static function getRutaCompleta($nombreArchivo, $carpeta)
    {
        // Se asume que la estructura es: raíz del proyecto / recursos / img / $carpeta / $nombreArchivo
        $base = dirname(__DIR__); 
        return $base . DIRECTORY_SEPARATOR . self::DIRECTORIO_BASE . DIRECTORY_SEPARATOR . $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;
    }
}