<?php
/**
 * SISTEMA CONDOMINIOS HAYDEE - RESPALDO AUTOMATIZADO HÍBRIDO BCP
 */

require_once __DIR__ . '/../vendor/autoload.php';

function getMysqldumpPath() {
    if (defined('MYSQLDUMP_PATH') && MYSQLDUMP_PATH !== '') return MYSQLDUMP_PATH;
    return (ENTORNO === 'local') ? '"C:\xampp\mysql\bin\mysqldump.exe"' : 'mysqldump';
}

function getBackupDir() {
    return ROOT_PATH . DIRECTORY_SEPARATOR . 'Backups' . DIRECTORY_SEPARATOR;
}

function ejecutarGarbageCollector($dir, $diasMaximos = 7) {
    $tiempoLimite = time() - ($diasMaximos * 24 * 60 * 60);

    // Limpieza de respaldos SQL comprimidos (.sql.gz)
    $archivosSql = glob($dir . "*.sql.gz");
    foreach ($archivosSql as $archivo) {
        if (filemtime($archivo) < $tiempoLimite) {
            unlink($archivo);
            echo "[LIMPIEZA] Respaldo SQL obsoleto eliminado: " . basename($archivo) . "\n";
        }
    }

    // Limpieza de logs binarios antiguos copias (mysql-bin.0*)
    // para mantener ña  carpeta local limpia de semanas pasadas
    $archivosBin = glob($dir . "mysql-bin.0*");
    foreach ($archivosBin as $archivo) {
        if (filemtime($archivo) < $tiempoLimite) {
            unlink($archivo);
            echo "[LIMPIEZA] Log binario obsoleto eliminado: " . basename($archivo) . "\n";
        }
    }
}

// Configuración Base
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_host = DB_HOST;
$databases = [DB_NAME, DB_SECURITY];
$backup_dir = getBackupDir();
$mysqldump_path = getMysqldumpPath();

if (!is_dir($backup_dir)) mkdir($backup_dir, 0777, true);

// INICIO DEL MONITOREO
ob_start(); 
$exito_total = true; // para saber si todo salió bien hoy

echo "=== INICIANDO PLAN DE CONTINUIDAD DE NEGOCIO (BCP) ===\n";
echo "Entorno detectado: " . strtoupper(ENTORNO) . "\n";

$dia_semana = date('N'); // 1=Lunes, 5=Viernes

$fecha_hoy = date('Y-m-d');
$timestamp = date('Y-m-d_H-i-s');

try {
    $dsn = "mysql:host=$db_host;charset=utf8mb4";
    
    $backup_user = DB_BACKUP_USER;
    $backup_pass = DB_BACKUP_PASS;
    
    $pdo = new PDO($dsn, $backup_user, $backup_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $db_user = $backup_user;
    $db_pass = $backup_pass;

} catch (PDOException $e) {
    $exito_total = false;
    die("[ERROR FATAL] No se pudo conectar al motor para backups: " . $e->getMessage() . "\n");
}

foreach ($databases as $db) {
    echo "\nProcesando Base de Datos: $db\n";
    $filename = $backup_dir . "backup_{$db}_{$timestamp}_AUTOMATICO.sql";

    if (ENTORNO === 'local') {
        if ($dia_semana == 5) {
            echo "[LOCAL - VIERNES] Ejecutando Respaldo TOTAL...\n";
            $command = sprintf(
                '%s --host=%s --user=%s --password=%s --opt --single-transaction --routines --triggers --databases %s > %s 2>&1',
                $mysqldump_path, escapeshellarg($db_host), escapeshellarg($db_user), escapeshellarg($db_pass), escapeshellarg($db), escapeshellarg($filename)
            );
            exec($command, $output, $return_var);

            if ($return_var === 0) {
                echo "[LOCAL] Reiniciando Logs Binarios (RESET MASTER)...\n";
                $pdo->exec("RESET MASTER;"); 
            } else {
                $exito_total = false;
            }
        } else {
            echo "[LOCAL - DIARIO] Ejecutando Corte de Logs (FLUSH LOGS)...\n";
            $pdo->exec("FLUSH LOGS;"); 
            $mysql_data_dir = "C:\\xampp\\mysql\\data\\";
            exec("copy " . escapeshellarg($mysql_data_dir . "mysql-bin.0*") . " " . escapeshellarg($backup_dir) . " > nul");
            echo "[LOCAL] Tiques de transacciones copiados con éxito.\n";
            continue; 
        }
    } 
    else {
        if ($dia_semana == 5) {
            echo "[CLOUD - VIERNES] Ejecutando Respaldo TOTAL...\n";
            $command = sprintf(
                '%s --host=%s --user=%s --password=%s --opt --single-transaction --routines --triggers --databases %s > %s 2>&1',
                $mysqldump_path, escapeshellarg($db_host), escapeshellarg($db_user), escapeshellarg($db_pass), escapeshellarg($db), escapeshellarg($filename)
            );
        } else {
            echo "[PROCESO] Iniciando Dump Parcial (Incremental Diario)...\n";
            
            // Tablas transaccionales que sufren modificaciones diarias
            $tablas_incrementales = ['detalles_pagos', 'detalles_gastos', 'movimientos_caja'];
            
            // Creamos el archivo limpio para el volcado de hoy
            file_put_contents($backup_file_sql, "/* CONDOMINIOS HAYDEE - RESPALDO PARCIAL DIARIO " . date('Y-m-d') . " */\n\n");

            foreach ($tablas_incrementales as $tabla) {
                echo "[PARCIAL] Exportando datos del día para la tabla: $tabla...\n";
                
                // --no-create-info: Evita que se genere el DROP TABLE y CREATE TABLE, impidiendo la pérdida de datos históricos.
                // --skip-add-drop-table: Asegura que no se inyecten estructuras destructivas en el archivo.
                // --replace: Si al restaurar el parcial un registro ya existe, lo reemplaza en lugar de dar un error de llave duplicada.
                $comando = sprintf(
                    "%s -h %s -u %s -p%s --no-create-info --skip-add-drop-table --replace %s %s --where=\"fecha='%s'\" >> %s 2>&1",
                    $mysqldump,
                    escapeshellarg($db_host),
                    escapeshellarg($db_user),
                    escapeshellarg($db_pass),
                    escapeshellarg($db_name),
                    escapeshellarg($tabla),
                    $fecha_hoy,
                    escapeshellarg($backup_file_sql)
                );

                exec($comando, $salida, $codigo_retorno);

                if ($codigo_retorno !== 0) {
                    $exito_total = false;
                    echo "[ERROR] Falló el volcado parcial de la tabla $tabla. Código: $codigo_retorno\n";
                }
            }
        }
        exec($command, $output, $return_var);
        if ($return_var !== 0) $exito_total = false;
    }

    if (isset($return_var) && $return_var === 0 && file_exists($filename) && filesize($filename) > 0) {
        echo "[SISTEMA] Comprimiendo archivo SQL...\n";
        $gzFilename = $filename . ".gz";
        $fpOut = gzopen($gzFilename, "wb9");
        $fpIn = fopen($filename, "rb");
        while (!feof($fpIn)) gzwrite($fpOut, fread($fpIn, 1024 * 512));
        fclose($fpIn);
        gzclose($fpOut);
        unlink($filename); 
        echo "[OK] Respaldo generado y optimizado.\n";
    } else {
        echo "[ERROR] Falló la generación del archivo SQL.\n";
        if (!empty($output)) echo "Detalle: " . implode("\n", $output) . "\n";
    }
}

ejecutarGarbageCollector($backup_dir, 7);
echo "\n=== PROCESO FINALIZADO ===\n";


// FIN DEL MONITOREO Y ESCRITURA DEL ARCHIVO TXT
$salida_consola = ob_get_clean();

$log_file = $backup_dir . 'estado_respaldos.txt';
$success_file = $backup_dir . '.ultimo_exito'; // Archivo oculto para guardar la fecha

// Leemos la última fecha de éxito guardada (si existe)
$fecha_ultimo_exito = file_exists($success_file) ? file_get_contents($success_file) : 'Nunca';

// Si hoy todo salió bien, actualizamos la fecha de éxito
if ($exito_total) {
    $fecha_ultimo_exito = date('Y-m-d H:i:s');
    file_put_contents($success_file, $fecha_ultimo_exito);
}

$contenido_txt = "========================================================" . PHP_EOL;
$contenido_txt .= "REPORTE DE RESPALDOS - CONDOMINIOS HAYDEE" . PHP_EOL;
$contenido_txt .= "========================================================" . PHP_EOL;
$contenido_txt .= "ÚLTIMO RESPALDO EXITOSO: " . $fecha_ultimo_exito . PHP_EOL;
$contenido_txt .= "--------------------------------------------------------" . PHP_EOL;
$contenido_txt .= "BITÁCORA DE LA EJECUCIÓN ACTUAL (" . date('Y-m-d H:i:s') . "):" . PHP_EOL;

// Convertimos cualquier salto de línea genérico de la consola al formato del sistema operativo actual
$contenido_txt .= str_replace(array("\r\n", "\n", "\r"), PHP_EOL, $salida_consola);

// Sobrescribimos el archivo TXT para no gastar espacio
file_put_contents($log_file, $contenido_txt);

// Imprimimos en pantalla por si se está ejecutando manualmente en la consola
echo $contenido_txt;