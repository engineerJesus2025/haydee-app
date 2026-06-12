<?php
/**
 * Lector y Formateador Masivo de Logs de Auditoría (Batch)
 */

// Configuración de Entorno
$mysqlbinlog = "C:\\xampp\\mysql\\bin\\mysqlbinlog.exe";
// $mysqlbinlog = "mysqlbinlog"; // en linux

// Determinar el directorio de trabajo
$directorio_objetivo = isset($argv[1]) ? rtrim($argv[1], DIRECTORY_SEPARATOR) : __DIR__;

if (!is_dir($directorio_objetivo)) {
    die("[ERROR] El directorio especificado no existe: $directorio_objetivo\n");
}

// Buscar todos los archivos de log binario
$patron_busqueda = $directorio_objetivo . DIRECTORY_SEPARATOR . 'mysql-bin.0*';
$archivos_bin = glob($patron_busqueda);

if (empty($archivos_bin)) {
    die("[INFO] No se encontraron archivos mysql-bin en: $directorio_objetivo\n");
}

// Preparar el reporte de salida
$fecha_reporte = date('Y-m-d_H-i-s');
$archivo_salida = $directorio_objetivo . DIRECTORY_SEPARATOR . "Reporte_Auditoria_{$fecha_reporte}.txt";

$reporte = "========================================================\n";
$reporte .= " REPORTE DE AUDITORÍA FORENSE - CONDOMINIOS HAYDEE\n";
$reporte .= "========================================================\n";
$reporte .= "Fecha de generación : " . date('Y-m-d H:i:s') . "\n";
$reporte .= "Directorio analizado: " . $directorio_objetivo . "\n";
$reporte .= "Archivos procesados : " . count($archivos_bin) . "\n";
$reporte .= "========================================================\n\n";

echo "Iniciando análisis masivo de " . count($archivos_bin) . " archivo(s)...\n";

// Procesamiento secuencial de cada archivo
foreach ($archivos_bin as $binlog_path) {
    $nombre_archivo = basename($binlog_path);
    echo " -> Procesando: $nombre_archivo\n";
    
    $reporte .= ">>> EXTRAYENDO DE: $nombre_archivo <<<\n\n";

    $comando = sprintf(
        '%s --no-defaults -v --base64-output=DECODE-ROWS %s',
        escapeshellarg($mysqlbinlog),
        escapeshellarg($binlog_path)
    );

    exec($comando, $lineas, $codigo_retorno);

    if ($codigo_retorno !== 0) {
        $reporte .= "[ERROR] No se pudo leer este archivo. Saltando...\n\n";
        continue;
    }

    $transaccion_actual = [];
    $capturando = false;

    foreach ($lineas as $linea) {
        // Detectar fecha y hora
        if (strpos($linea, '#') === 0 && preg_match('/#(\d{6}\s+\d{2}:\d{2}:\d{2})\s+server id/', $linea, $matches)) {
            $fecha_cruda = $matches[1];
            $anio = "20" . substr($fecha_cruda, 0, 2);
            $mes = substr($fecha_cruda, 2, 2);
            $dia = substr($fecha_cruda, 4, 2);
            $hora = substr($fecha_cruda, 7);
            $transaccion_actual['fecha'] = "$anio-$mes-$dia $hora";
        }

        // Detectar inicio de sentencia SQL (###)
        if (strpos($linea, '### ') === 0) {
            $operacion = trim(str_replace('### ', '', $linea));
            $transaccion_actual['sql'][] = $operacion;
            $capturando = true;
        } 
        // Fin de bloque SQL
        elseif ($capturando && strpos($linea, '###') === false && trim($linea) !== '') {
            $capturando = false;
            
            $reporte .= "[FECHA] : " . ($transaccion_actual['fecha'] ?? 'Desconocida') . "\n";
            $reporte .= "[ACCIÓN]: \n";
            foreach ($transaccion_actual['sql'] as $sql_line) {
                $reporte .= "  " . $sql_line . "\n"; // Salto por cada columna parseada
            }
            $reporte .= "--------------------------------------------------------\n";
            
            $transaccion_actual['sql'] = [];
        }
    }
    
    $reporte .= "\n"; // Espacio limpio entre archivos procesados
    $lineas = []; // Liberar el array del exec
}

$reporte .= "FIN DEL REPORTE.\n";

// Guardar resultados
file_put_contents($archivo_salida, str_replace("\n", PHP_EOL, $reporte));

echo "========================================================\n";
echo "¡Análisis completado con éxito!\n";
echo "El reporte se ha guardado estructurado en:\n$archivo_salida\n";