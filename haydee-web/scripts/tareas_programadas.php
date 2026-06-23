<?php
require_once __DIR__ . '/../vendor/autoload.php'; 

use haydee\enums\HttpCodigo;
use haydee\modelo\AnioFiscal;

// Seguridad en consola
if (php_sapi_name() !== 'cli') {
    http_response_code(HttpCodigo::PROHIBIDO->value);
    die("Este script solo puede ser ejecutado por el sistema.");
}

echo "Iniciando tareas programadas en entorno: " . ENTORNO . "...\n";

$anio = null;

try {
    $anio = new AnioFiscal();

    echo "Verificando consistencia de Año Fiscal y Caja Chica...\n";
    $res = $anio->realizar_consulta('gestionar_periodos');
    echo $res['mensaje'] . "\n";

} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
} finally {
    if ($anio !== null) $anio->cerrar();
}

echo "Tareas finalizadas.\n";