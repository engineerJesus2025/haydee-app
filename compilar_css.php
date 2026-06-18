<?php
/**
 * Compilador y Minificador de CSS integrado para Condominios Haydee
 * Ejecución manual bajo demanda.
 */

// Definir el orden estricto de unificación (para respetar la especificidad)
$archivosCSS = [
    __DIR__ . '/recursos/css/src/estilos_generales.css',
    __DIR__ . '/recursos/css/src/header.css',
    __DIR__ . '/recursos/css/src/nav.css',
    __DIR__ . '/recursos/css/src/tabulador.css',
    __DIR__ . '/recursos/css/src/notificaciones.css',
    __DIR__ . '/recursos/css/src/estilos_modal_carga.css',
    __DIR__ . '/recursos/css/src/busqueda_tabla.css',
    __DIR__ . '/recursos/css/src/driver.css',
    // 'recursos/css/src/contrasenias.css'
];

$outputFile = __DIR__ . '/recursos/css/dist/app-core.min.css';
$buffer = "";

echo "Iniciando unificación de CSS...\n";

// Leer y concatenar el contenido de los archivos
foreach ($archivosCSS as $archivo) {
    if (file_exists($archivo)) {
        $buffer .= file_get_contents($archivo) . "\n";
        echo " [+] Combinado: " . basename($archivo) . "\n";
    } else {
        echo " [X] Error: No se encontró el archivo " . basename($archivo) . "\n";
    }
}

// Minificación mediante expresiones regulares 
echo "Minificando código resultante...\n";

// Eliminar comentarios de bloque (/* ... */)
$buffer = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $buffer);

// Eliminar saltos de línea, retornos, tabulaciones y espacios dobles
$buffer = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $buffer);

// Optimizar espacios alrededor de los selectores y llaves
$buffer = preg_replace('/\\s*([\\{\\};,>])\\s*/', '$1', $buffer);

// Guardar el archivo final comprimido
if (file_put_contents($outputFile, $buffer) !== false) {
    echo "\n¡Éxito! Archivo generado en: recursos/css/app-core.min.css\n";
    echo "Tamaño final estimado: " . round(strlen($buffer) / 1024, 2) . " KB\n";
} else {
    echo "\n[ERROR] No se pudo escribir el archivo final.\n";
}