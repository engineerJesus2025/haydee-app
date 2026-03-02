<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Mantenimiento extends Conexion
{
    public function generarCopiaSeguridad($db)
    {
        $db_copiar = ($db === 'negocio') ? DB_NAME : DB_SECURITY;
        $mysqldump_path = $this->getMysqldumpPath();
        $backup_dir = $this->getBackupDir();
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }

        $backup_file = $backup_dir . 'backup_' . $db_copiar . '_' . date('Y-m-d-H-i-s') . '.sql';

        $comando = $mysqldump_path
            . " --host=" . DB_HOST
            . " --user=" . DB_USER
            . " --password=" . DB_PASS
            . " --routines --events --triggers"
            . " " . $db_copiar
            . " > " . $backup_file;

        system($comando . " 2>&1", $resultado);

        if ($resultado === 0) {
            // Limpiar el archivo de cláusulas DEFINER
            $this->removeDefinerFromSql($backup_file);
            return ['estatus' => true, 'mensaje' => 'Copia de seguridad creada exitosamente', 'archivo' => $backup_file];
        } else {
            return ['estatus' => false, 'mensaje' => 'Error al crear la copia de seguridad', 'comando' => $comando];
        }
    }

    public function descargarCopiaSeguridad($db)
    {
        $db_copiar = ($db === 'negocio') ? DB_NAME : DB_SECURITY;
        $backup_dir = $this->getBackupDir();
        $backup_file = $backup_dir . 'backup_' . $db_copiar . '_' . date('Y-m-d-H-i-s') . '.sql';

        // Generar el backup temporal
        $resultado = $this->generarCopiaSeguridad($db);
        if (!$resultado['estatus']) {
            // Si falla, redirigir con error
            header('Location: ?pagina=mantenimiento&accion=inicio&e=1');
            exit;
        }

        $archivo = $resultado['archivo'];

        if (file_exists($archivo)) {
            // Cabeceras para forzar la descarga
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($archivo));

            ob_clean();
            flush();
            readfile($archivo);

            // Eliminar el archivo temporal después de la descarga
            unlink($archivo);
            exit;
        } else {
            header('Location: ?pagina=mantenimiento&accion=inicio&e=1');
            exit;
        }
    }

    public function obtenerCopias()
    {
        $directorio = $this->getBackupDir();
        if (!is_dir($directorio)) {
            return ['estatus' => false, 'mensaje' => 'El directorio de backups no existe'];
        }

        $ficheros = scandir($directorio);
        $archivos = [];
        if ($ficheros !== false) {
            foreach ($ficheros as $fichero) {
                if ($fichero !== '.' && $fichero !== '..') {
                    $archivos[] = $fichero;
                }
            }
        } else {
            return ['estatus' => false, 'mensaje' => 'No se pudo leer el directorio de backups'];
        }

        return ['estatus' => true, 'datos' => $archivos];
    }

    public function importarCopiaSeguridad($db, $fichero)
    {
        $directorio = $this->getBackupDir();
        $ruta_completa = $directorio . $fichero;

        if (!file_exists($ruta_completa)) {
            return ['estatus' => false, 'mensaje' => 'El archivo de copia no existe'];
        }

        $dbname = ($db === 'negocio') ? DB_NAME : DB_SECURITY;
        $mysql_path = $this->getMysqlPath();

        // Construir comando de importación
        $comando = $mysql_path . " --host=" . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASS . " " . $dbname . " < " . escapeshellarg($ruta_completa) . " 2>&1";
        
        system($comando, $resultado);

        if ($resultado === 0) {
            return ['estatus' => true, 'mensaje' => 'Copia de seguridad importada exitosamente'];
        } else {
            return ['estatus' => false, 'mensaje' => 'Error al importar la copia de seguridad'];
        }
    }

    public function importarSQL($sql, $db = 'negocio')
    {
        // Guardar SQL temporalmente en un archivo
        $tmpFile = tempnam(sys_get_temp_dir(), 'sql_import_');
        file_put_contents($tmpFile, $sql);

        $dbname = ($db === 'negocio') ? DB_NAME : DB_SECURITY;
        $mysql_path = $this->getMysqlPath();

        $comando = $mysql_path . " --host=" . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASS . " " . $dbname . " < " . escapeshellarg($tmpFile) . " 2>&1";
        
        system($comando, $resultado);
        unlink($tmpFile); // Limpiar archivo temporal

        if ($resultado === 0) {
            return ['estatus' => true, 'mensaje' => 'Importación exitosa'];
        } else {
            return ['estatus' => false, 'mensaje' => 'Error al ejecutar el comando mysql'];
        }
    }

    // -----------------------------------------------------------------
    // Métodos auxiliares privados
    // -----------------------------------------------------------------

    /**
     * Obtiene la ruta del ejecutable mysqldump según el entorno.
     */
    private function getMysqldumpPath()
    {
        // Para entorno local (XAMPP en Windows)
        if (DIRECTORY_SEPARATOR === '\\') {
            return '"C:\xampp\mysql\bin\mysqldump.exe"';
        }
        // Para Linux/Unix (hosting)
        return 'mysqldump';
    }

    private function getMysqlPath()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return '"C:\xampp\mysql\bin\mysql.exe"';
        }
        return 'mysql';
    }

    /**
     * Obtiene el directorio donde se almacenan los backups.
     */
    private function getBackupDir()
    {
        // Se asume que la estructura es: raíz del proyecto / recursos / Backups /
        $base = dirname(__DIR__); // Sube un nivel desde modelo/ a la raíz
        return $base . DIRECTORY_SEPARATOR . 'recursos' . DIRECTORY_SEPARATOR . 'Backups' . DIRECTORY_SEPARATOR;
    }

    public function detectarBaseDesdeSQL($contenido_sql)
    {
        if (strpos($contenido_sql, "Database: seguridad_haydee_db") !== false) {
            return 'seguridad';
        }
        // Por defecto, asumimos negocio
        return 'negocio';
    }

    /**
     * Elimina las cláusulas DEFINER del archivo SQL para permitir importación sin SUPER
     * @param string $filepath Ruta completa al archivo .sql
     */
    private function removeDefinerFromSql($filepath)
    {
        $content = file_get_contents($filepath);
        // Elimina cualquier ocurrencia de DEFINER=usuario@host (con o sin backticks)
        $content = preg_replace('/\s*DEFINER\s*=\s*[^\s]+/i', '', $content);
        file_put_contents($filepath, $content);
    }
}