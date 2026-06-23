<?php
namespace haydee\servicios;

use haydee\enums\TipoBaseDatos;

class GestorTasa
{
    private const API_URL = 'https://ve.dolarapi.com/v1/dolares/oficial';
    private const TIMEOUT_CONEXION = 3;

    private static function obtenerRutaCache() {
        // Apunta a carpeta 'config'
        // return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'tasa_dolar.json';

        // Apunta a directorio de archivos temporales del servidor local (por ejemplo, /tmp en sistemas Linux o C:\Windows\Temp en XAMPP/Windows).
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'haydee_tasa_dolar.json';
    }

    public static function obtener()
    {
        $rutaArchivo = self::obtenerRutaCache();

        if (file_exists($rutaArchivo)) {
            $cache = json_decode(file_get_contents($rutaArchivo), true);
            
            if (isset($cache['fecha']) && $cache['fecha'] === date('Y-m-d')) {
                return (float)$cache['tasa'];
            }
        }

        return self::actualizar();
    }

    private static function actualizar()
    {
        $rutaArchivo = self::obtenerRutaCache();

        try {
            $contexto = stream_context_create([
                'http' => ['timeout' => self::TIMEOUT_CONEXION]
            ]);

            $respuesta = @file_get_contents(self::API_URL, false, $contexto); 
            
            if ($respuesta === false) {
                throw new \Exception("Fallo de red al conectar con DolarAPI.");
            }

            $data = json_decode($respuesta, true);
            if (!isset($data['promedio'])) { 
                throw new \Exception("Formato de respuesta inválido.");
            }

            $tasaCalculada = (float)$data['promedio']; 

            $datosCache = [
                'tasa' => $tasaCalculada,
                'fecha' => date('Y-m-d')
            ];

            file_put_contents($rutaArchivo, json_encode($datosCache));
            return $tasaCalculada;

        } catch (\Exception $e) {
            error_log("Error en GestorTasa: " . $e->getMessage());

            if (file_exists($rutaArchivo)) {
                $cacheAnterior = json_decode(file_get_contents($rutaArchivo), true);
                if (isset($cacheAnterior['tasa'])) {
                    return (float)$cacheAnterior['tasa'];
                }
            }

            return 1.0; // por si hay un fallo
        }
    }
}