<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Mantenimiento extends Conexion
{
    private const MYSQLDUMP_WIN = '"C:\xampp\mysql\bin\mysqldump.exe"';
    private const MYSQLDUMP_LINUX = 'mysqldump';
    private const MYSQL_WIN = '"C:\xampp\mysql\bin\mysql.exe"';
    private const MYSQL_LINUX = 'mysql';
    private const DIR_BACKUPS = 'Backups';

    private const DB_ADMIN_USER_DEFAULT = 'root';
    private const DB_ADMIN_PASS_DEFAULT = '';

    public static function obtenerReglas($operacion) {
        $tiposCuentaValidos = implode('|', array_column(TipoBaseDatos::cases(), 'value'));

        $reglasGenerales = [
            'db' => [
                'regex' => "/^($tiposCuentaValidos)$/"
            ]
        ];

        $camposPorOperacion = [
            'generar_copia_seguridad'   => ['db'],
            'descargar_copia_seguridad' => ['db'],
            'importar_copia_seguridad'  => ['db'],
            'importar_archivo_sql'      => ['db']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function generarCopiaSeguridad($db)
    {
        $db_copiar = ($db === TipoBaseDatos::NEGOCIO->value || $db === 'negocio') ? DB_NAME : DB_SECURITY;
        
        $mysqldump_path = $this->getMysqldumpPath();
        $backup_dir = $this->getBackupDir();
        if (!is_dir($backup_dir)) mkdir($backup_dir, 0777, true);

        $timestamp = date('Y-m-d_H-i-s');
        $backup_file = $backup_dir . "backup_{$db_copiar}_{$timestamp}_MANUAL.sql";

        $creds = $this->obtenerCredencialesAdmin();

        $comando = sprintf(
            "%s --host=%s --user=%s --password=%s --routines --events --triggers %s > %s 2>&1",
            $mysqldump_path,
            escapeshellarg(DB_HOST),
            escapeshellarg($creds['user']),
            escapeshellarg($creds['pass']),
            escapeshellarg($db_copiar),
            escapeshellarg($backup_file)
        );

        exec($comando, $output, $resultado);

        if ($resultado === 0) {
            $this->removeDefinerFromSql($backup_file);
            
            $gzFilename = $backup_file . '.gz';
            
            $fpOut = @gzopen($gzFilename, "wb9");
            $fpIn = @fopen($backup_file, "rb");

            if (!$fpOut || !$fpIn) {
                if ($fpOut) gzclose($fpOut);
                if ($fpIn) fclose($fpIn);
                throw new NegocioException('Error de permisos al comprimir el archivo de seguridad.', HttpCodigo::ERROR_INTERNO->value);
            }

            while (!feof($fpIn)) gzwrite($fpOut, fread($fpIn, 1024 * 512));
            
            fclose($fpIn);
            gzclose($fpOut);
            unlink($backup_file);

            return ['estatus' => true, 'mensaje' => 'Copia de seguridad creada y comprimida exitosamente', 'archivo' => basename($gzFilename)];
        } else {
            $error_detalle = implode(" | ", $output);
            error_log("Error Backup: " . $error_detalle);
            throw new NegocioException('Error al generar la copia de seguridad: ' . $error_detalle, HttpCodigo::ERROR_INTERNO->value);
        }
    }

    public function descargarCopiaSeguridad($db)
    {
        $resultado = $this->generarCopiaSeguridad($db);
        $archivo_nombre = $resultado['archivo'];
        $ruta_archivo = $this->getBackupDir() . $archivo_nombre;

        if (!file_exists($ruta_archivo)) {
            throw new NegocioException("El archivo fue generado pero no se encuentra en el directorio: " . $archivo_nombre, HttpCodigo::NO_ENCONTRADO->value);
        }

        header('Content-Type: application/x-gzip');
        header('Content-Disposition: attachment; filename="' . $archivo_nombre . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($ruta_archivo));

        ob_clean();
        flush();
        readfile($ruta_archivo);

        unlink($ruta_archivo);
        exit;
    }

    public function obtenerCopias()
    {
        $directorio = $this->getBackupDir();
        if (!is_dir($directorio)) {
            throw new NegocioException('El directorio de backups no existe.', HttpCodigo::NO_ENCONTRADO->value);
        }

        $ficheros = scandir($directorio);
        $archivos = [];
        if ($ficheros !== false) {
            foreach ($ficheros as $fichero) {
                if ($fichero !== '.' && $fichero !== '..' && preg_match('/\.sql(\.gz)?$/i', $fichero)) {
                    $archivos[] = $fichero;
                }
            }
        } else {
            throw new NegocioException('No se pudo leer el directorio de backups.', HttpCodigo::ERROR_INTERNO->value);
        }

        return ['estatus' => true, 'datos' => $archivos];
    }

    public function importarCopiaSeguridad($db, $fichero)
    {
        $directorio = $this->getBackupDir();
        $ruta_completa = $directorio . $fichero;

        if (!file_exists($ruta_completa)) {
            throw new NegocioException('El archivo de copia no existe en el servidor.', HttpCodigo::NO_ENCONTRADO->value);
        }

        $dbname = ($db === 'negocio' || $db === TipoBaseDatos::NEGOCIO->value) ? DB_NAME : DB_SECURITY;
        $mysql_path = $this->getMysqlPath();
        $es_gz = (pathinfo($ruta_completa, PATHINFO_EXTENSION) === 'gz');
        
        $temp_sql = tempnam(sys_get_temp_dir(), 'restore_');

        if ($es_gz) {
            $fpIn = gzopen($ruta_completa, 'rb');
            $fpOut = fopen($temp_sql, 'wb');
            while (!gzeof($fpIn)) fwrite($fpOut, gzread($fpIn, 1024 * 512));
            fclose($fpOut);
            gzclose($fpIn);
        } else {
            copy($ruta_completa, $temp_sql);
        }

        $this->removeDefinerFromSql($temp_sql);

        $creds = $this->obtenerCredencialesAdmin();

        $comando = sprintf(
            "%s --host=%s --user=%s --password=%s %s < %s 2>&1",
            $mysql_path,
            escapeshellarg(DB_HOST),
            escapeshellarg($creds['user']),
            escapeshellarg($creds['pass']),
            escapeshellarg($dbname),
            escapeshellarg($temp_sql)
        );
        
        exec($comando, $output, $resultado);

        if (file_exists($temp_sql)) {
            unlink($temp_sql);
        }

        if ($resultado === 0) {
            return ['estatus' => true, 'mensaje' => 'Copia de seguridad importada exitosamente.'];
        } else {
            $error_detalle = implode(" | ", $output);
            error_log("Error Restauración: " . $error_detalle);
            throw new NegocioException('Fallo de MySQL: ' . $error_detalle, HttpCodigo::ERROR_INTERNO->value);
        }
    }

    public function importarSQL($contenido_sql, $db)
    {
        $nombre_db = ($db === TipoBaseDatos::NEGOCIO->value) ? DB_NAME : DB_SECURITY;
        $mysql_path = $this->getMysqlPath();
        
        $temp_file = tempnam(sys_get_temp_dir(), 'restore_');
        
        if (file_put_contents($temp_file, $contenido_sql) === false) {
            throw new NegocioException('Error de E/S en el servidor al preparar la restauración.', HttpCodigo::ERROR_INTERNO->value);
        }

        $this->removeDefinerFromSql($temp_file);

        $creds = $this->obtenerCredencialesAdmin();

        $comando = sprintf(
            "%s --host=%s --user=%s --password=%s %s < %s 2>&1",
            $mysql_path,
            escapeshellarg(DB_HOST),
            escapeshellarg($creds['user']),
            escapeshellarg($creds['pass']),
            escapeshellarg($nombre_db),
            escapeshellarg($temp_file)
        );

        exec($comando, $output, $return_var);
        
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }

        if ($return_var === 0) {
            return [
                'estatus' => true, 
                'mensaje' => "Base de datos " . strtoupper($db) . " restaurada con éxito."
            ];
        } else {
            error_log("Error restaurando SQL: " . implode("\n", $output));
            throw new NegocioException("Error al procesar el archivo SQL. Verifique el formato.", HttpCodigo::BAD_REQUEST->value);
        }
    }

    private function getMysqldumpPath()
    {
        return (DIRECTORY_SEPARATOR === '\\') ? self::MYSQLDUMP_WIN : self::MYSQLDUMP_LINUX;
    }

    private function getMysqlPath()
    {
        return (DIRECTORY_SEPARATOR === '\\') ? self::MYSQL_WIN : self::MYSQL_LINUX;
    }

    private function getBackupDir()
    {
        $base = dirname(__DIR__); 
        return $base . DIRECTORY_SEPARATOR . self::DIR_BACKUPS . DIRECTORY_SEPARATOR;
    }

    public function detectarBaseDesdeSQL($contenido_sql)
    {
        if (strpos($contenido_sql, "Database: " . DB_SECURITY) !== false) {
            return TipoBaseDatos::SEGURIDAD;
        }
        return TipoBaseDatos::NEGOCIO;
    }

    private function removeDefinerFromSql($filepath)
    {
        $content = file_get_contents($filepath);
        $content = preg_replace('/\s*DEFINER\s*=\s*[^\s]+/i', '', $content);
        file_put_contents($filepath, $content);
    }

    private function obtenerCredencialesAdmin()
    {
        if (DIRECTORY_SEPARATOR === '\\') { 
            return [
                'user' => self::DB_ADMIN_USER_DEFAULT, 
                'pass' => self::DB_ADMIN_PASS_DEFAULT
            ];
        }
        return [
            'user' => DB_USER,
            'pass' => DB_PASS
        ];
    }
}