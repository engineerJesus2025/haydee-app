<?php
/**
 * Para ejecutar: php backup_automatico.php
 */

// require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';


// -------------------------------------------------------------------
// Funciones auxiliares (si no quieres depender de la clase Mantenimiento)
// -------------------------------------------------------------------
function getMysqldumpPath() {
    // Si la definiste en tu .env, úsala
    if (defined('MYSQLDUMP_PATH') && MYSQLDUMP_PATH !== '') {
        return MYSQLDUMP_PATH;
    }
    
    // Usamos la constante ENTORNO de tu config.php
    if (ENTORNO === 'local') {
        return '"C:\xampp\mysql\bin\mysqldump.exe"'; // Tu ruta en XAMPP
    }
    
    // Entorno de producción (Alwaysdata / Linux)
    return 'mysqldump';
}

function getBackupDir() {
    // Usa la constante ROOT_PATH definida en config.php
    return ROOT_PATH . DIRECTORY_SEPARATOR . 'Backups' . DIRECTORY_SEPARATOR;
}

function removeDefinerFromSql($filepath) {
    $content = file_get_contents($filepath);
    if ($content === false) {
        return false;
    }
    $content = preg_replace('/\s*DEFINER\s*=\s*[^\s]+/i', '', $content);
    return file_put_contents($filepath, $content) !== false;
}

// -------------------------------------------------------------------
// Configuración
// -------------------------------------------------------------------
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_host = DB_HOST;

// Bases de datos a respaldar (usamos las constantes del sistema)
$databases = [DB_NAME, DB_SECURITY];

$backup_dir = getBackupDir();
$mysqldump_path = getMysqldumpPath();

// Crear directorio si no existe
if (!is_dir($backup_dir)) {
    if (!mkdir($backup_dir, 0777, true)) {
        die("Error: No se pudo crear el directorio de backups: $backup_dir\n");
    }
}

echo "--- INICIANDO RESPALDO AUTOMÁTICO ---\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Directorio de backups: $backup_dir\n";
echo "Ruta mysqldump: $mysqldump_path\n\n";

foreach ($databases as $db) {
    $timestamp = date('Y-m-d_H-i-s');
    $tipo = "AUTOMATICO";
    $filename = $backup_dir . "backup_{$db}_{$timestamp}_{$tipo}.sql";

    // Escapar argumentos para el comando
    $command = sprintf(
        '%s --host=%s --user=%s --password=%s --routines --triggers --databases %s > %s 2>&1',
        $mysqldump_path,
        escapeshellarg($db_host),
        escapeshellarg($db_user),
        escapeshellarg($db_pass),
        escapeshellarg($db),
        escapeshellarg($filename)
    );

    echo "Ejecutando: $command\n";
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);

    if ($return_var === 0 && file_exists($filename) && filesize($filename) > 0) {
        if (removeDefinerFromSql($filename)) {
            echo "[OK] Respaldo de '$db' creado y limpiado: " . basename($filename) . "\n";
        } else {
            echo "[ADVERTENCIA] Respaldo creado pero no se pudo limpiar DEFINER: " . basename($filename) . "\n";
        }
    } else {
        echo "[ERROR] Falló el respaldo de '$db'. Código de retorno: $return_var\n";
        if (!empty($output)) {
            echo "Salida del comando:\n" . implode("\n", $output) . "\n";
        }
    }
    echo "\n";
}

echo "--- FIN DEL PROCESO ---\n";