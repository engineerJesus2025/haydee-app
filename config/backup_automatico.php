<?php
// CONFIGURACIÓN
$db_user = 'root';
$db_pass = '';
$databases = ['haydee2', 'seguridad_haydee2'];

$backup_dir = __DIR__ . '/Backups/'; //Modificiar para que se vaya a backups, o mover el scrip

$mysqldump_path = '"C:\xamppNew\mysql\bin\mysqldump.exe"';

// Verificar si existe la carpeta, si no, crearla
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

echo "--- INICIANDO RESPALDO AUTOMÁTICO ---\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";

foreach ($databases as $db) {
    $filename = $backup_dir . $db . "_" . date('Y-m-d_H-i-s') . "_AUTOMATICO" .".sql";
    
    // Comando para generar el SQL
    // --routines incluye Store Procedures y Triggers
    $command = "\"$mysqldump_path\" --user=$db_user --password=$db_pass --routines --triggers --databases $db > \"$filename\"";
    
    system($command, $output);
    
    if (file_exists($filename) && filesize($filename) > 0) {
        echo "[OK] Respaldo de '$db' creado exitosamente: \n    $filename \n";
    } else {
        echo "[ERROR] Falló el respaldo de '$db'. Verifica la ruta de mysqldump.\n";
    }
}

echo "--- FIN DEL PROCESO ---\n";
?>
