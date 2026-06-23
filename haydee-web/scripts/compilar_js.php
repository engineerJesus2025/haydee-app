<?php
/**
 * Compilador y Minificador de JavaScript Profesional
 * Utiliza matthiasmullie/minify
 */

// Cargamos el autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';

use MatthiasMullie\Minify;

$archivosJS = [
    // Helpers
    __DIR__ . '/recursos/js/ayuda/Patrones.js',
    __DIR__ . '/recursos/js/ayuda/Alertas.js',
    __DIR__ . '/recursos/js/ayuda/Peticiones.js',
    __DIR__ . '/recursos/js/ayuda/EstadoInputs.js',
    __DIR__ . '/recursos/js/ayuda/Validador.js',
    __DIR__ . '/recursos/js/ayuda/Tooltips.js',
    __DIR__ . '/recursos/js/ayuda/Tablas.js',
    __DIR__ . '/recursos/js/ayuda/FormatoFechas.js',
    __DIR__ . '/recursos/js/ayuda/Notificaciones.js',
    __DIR__ . '/recursos/js/ayuda/AyudaInteractiva.js',
    __DIR__ . '/recursos/js/ayuda/AtajosTeclado.js',
    __DIR__ . '/recursos/js/ayuda/ComponentesUI.js',
    
    // Globales
    __DIR__ . '/recursos/js/src/header.js',
    __DIR__ . '/recursos/js/src/notificaciones.js',
    __DIR__ . '/recursos/js/src/driver.js',
    __DIR__ . '/recursos/js/src/tema_global.js',
];

$outputFile = __DIR__ . '/recursos/js/dist/app-core.min.js';

echo "Iniciando minificación profesional de JavaScript...\n";

// Inicializamos el minificador de JS
$minifier = new Minify\JS();

foreach ($archivosJS as $archivo) {
    if (file_exists($archivo)) {
        // La librería se encarga de leer, limpiar de forma segura y adjuntar
        $minifier->add($archivo);
        echo " [+] Agregado: " . basename($archivo) . "\n";
    } else {
        echo " [X] Error: No se encontró " . basename($archivo) . "\n";
    }
}

echo "Procesando y comprimiendo código...\n";

// Ejecutamos la minificación y guardamos en el archivo destino
if ($minifier->minify($outputFile)) {
    echo "\n¡Éxito! Archivo generado en: recursos/js/app-core.min.js\n";
    echo "El archivo ahora está 100% minificado (sin comentarios y en una sola línea).\n";
} else {
    echo "\n[ERROR] No se pudo escribir el archivo final.\n";
}