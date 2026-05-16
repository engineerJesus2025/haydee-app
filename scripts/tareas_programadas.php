<?php
// scripts/tareas_programadas.php

// 1. Cargar autoloader subiendo un nivel desde la carpeta 'scripts'
require_once __DIR__ . '/../vendor/autoload.php'; 

use haydee\enums\HttpCodigo;
use haydee\modelo\CajaChica;
use haydee\modelo\AnioFiscal;

// 2. Seguridad en consola
if (php_sapi_name() !== 'cli') {
    http_response_code(HttpCodigo::PROHIBIDO->value);
    die("Este script solo puede ser ejecutado por el sistema.");
}

echo "Iniciando tareas programadas en entorno: " . ENTORNO . "...\n";

$caja = null;
$anio = null;

try {
    $caja = new CajaChica();
    $anio = new AnioFiscal();

    echo "Verificando Caja Chica...\n";
    $resCaja = $caja->realizar_consulta('verificar_caja_mes');
    echo $resCaja['mensaje'] . "\n";

    echo "Verificando Año Fiscal...\n";
    $resAnio = $anio->realizar_consulta('verificar_anio_fiscal');
    echo $resAnio['mensaje'] . "\n";

} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
} finally {
    if ($caja !== null) $caja->cerrar();
    if ($anio !== null) $anio->cerrar();
}

echo "Tareas finalizadas.\n";
