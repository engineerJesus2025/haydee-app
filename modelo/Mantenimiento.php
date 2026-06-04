<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\TipoBaseDatos;

class Mantenimiento extends Conexion
{
    // CONSTANTES DE ENTORNO Y RUTAS
    private const MYSQLDUMP_WIN = '"C:\xampp\mysql\bin\mysqldump.exe"';
    private const MYSQLDUMP_LINUX = 'mysqldump';
    private const MYSQL_WIN = '"C:\xampp\mysql\bin\mysql.exe"';
    private const MYSQL_LINUX = 'mysql';
    private const DIR_BACKUPS = 'Backups';

    // VALIDACIONES
    public static function obtenerReglas($operacion) {
        $tiposCuentaValidos = implode('|', array_column(TipoBaseDatos::cases(), 'value'));

        $reglasGenerales = [
            'db' => [
                'regex' => "/^($tiposCuentaValidos)$/"
            ]
        ];

        // Mapeamos las operaciones que existan en tu controlador
        $camposPorOperacion = [
            'generar_copia_seguridad'   => ['db'],
            'descargar_copia_seguridad' => ['db'],
            'importar_copia_seguridad'  => ['db'], // Para restaurar copias del servidor
            'importar_archivo_sql'      => ['db']  // Para restaurar archivos desde la PC
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
            
            //  @ para suprimir el Warning nativo y manejarlo nosotros
            $fpOut = @gzopen($gzFilename, "wb9");
            $fpIn = @fopen($backup_file, "rb");

            if (!$fpOut || !$fpIn) {
                if ($fpOut) gzclose($fpOut);
                if ($fpIn) fclose($fpIn);
                error_log("Error Backup: No se pudo abrir el buffer para compresión GZIP.");
                return ['estatus' => false, 'mensaje' => 'Error de permisos al comprimir el archivo de seguridad.'];
            }

            while (!feof($fpIn)) gzwrite($fpOut, fread($fpIn, 1024 * 512));
            
            fclose($fpIn);
            gzclose($fpOut);
            unlink($backup_file); // Borramos el .sql original pesado

            return ['estatus' => true, 'mensaje' => 'Copia de seguridad creada y comprimida exitosamente', 'archivo' => basename($gzFilename)];
        } else {
            // Ahora mostramos el error real capturado de la consola
            $error_detalle = implode(" | ", $output);
            error_log("Error Backup: " . $error_detalle);
            return ['estatus' => false, 'mensaje' => 'Error al generar: ' . $error_detalle];
        }
    }

    public function descargarCopiaSeguridad($db)
    {
        try {
            // Generar el backup temporal
            $resultado = $this->generarCopiaSeguridad($db);
            
            if (!$resultado['estatus']) {
                throw new Exception($resultado['mensaje']);
            }

            $archivo_nombre = $resultado['archivo'];
            // Armamos la ruta absoluta hacia la carpeta Backups
            $ruta_archivo = $this->getBackupDir() . $archivo_nombre;

            if (!file_exists($ruta_archivo)) {
                throw new Exception("El archivo fue generado pero no se encuentra en el directorio: " . $archivo_nombre);
            }

            // Cabeceras optimizadas para archivos GZIP
            header('Content-Type: application/x-gzip');
            header('Content-Disposition: attachment; filename="' . $archivo_nombre . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($ruta_archivo));

            ob_clean();
            flush();
            readfile($ruta_archivo);

            // Eliminar el archivo temporal después de la descarga
            unlink($ruta_archivo);
            exit;

        } catch (Exception $e) {
            // Ahora sí quedará registro en el Log del servidor
            error_log("Error Descarga Mantenimiento: " . $e->getMessage());
            
            // Codificamos el mensaje para enviarlo seguro por la URL
            $msj_codificado = urlencode($e->getMessage());
            header("Location: ?pagina=mantenimiento&accion=inicio&e=1&msg={$msj_codificado}");
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
                // Filtramos para enviar SOLO archivos que terminen en .sql o .sql.gz
                if ($fichero !== '.' && $fichero !== '..' && preg_match('/\.sql(\.gz)?$/i', $fichero)) {
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
            return ['estatus' => false, 'mensaje' => 'El archivo de copia no existe en el servidor.'];
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
            // Si es un .sql plano viejo, lo copiamos al temporal
            copy($ruta_completa, $temp_sql);
        }

        // Borramos las firmas DEFINER=root de los Triggers antes de inyectar
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

        // Limpiar el archivo temporal de la memoria del servidor
        if (file_exists($temp_sql)) {
            unlink($temp_sql);
        }

        if ($resultado === 0) {
            return ['estatus' => true, 'mensaje' => 'Copia de seguridad importada exitosamente.'];
        } else {
            // Devolvemos el error limpio al frontend para que el administrador sepa qué falló
            $error_detalle = implode(" | ", $output);
            error_log("Error Restauración: " . $error_detalle);
            return ['estatus' => false, 'mensaje' => 'Fallo de MySQL: ' . $error_detalle];
        }
    }

    /**
     * Importa un contenido SQL directamente a la base de datos especificada.
     * @param string $contenido_sql El texto del archivo .sql
     * @param string $db 'negocio' o 'seguridad'
     * @return array Respuesta con estatus y mensaje
     */
    public function importarSQL($contenido_sql, $db)
    {
        $nombre_db = ($db === TipoBaseDatos::NEGOCIO->value) ? DB_NAME : DB_SECURITY;
        $mysql_path = $this->getMysqlPath();
        
        // Creamos un archivo temporal para el contenido SQL
        $temp_file = tempnam(sys_get_temp_dir(), 'restore_');
        
        // VALIDACIÓN DE ERRORES: Verificar si se pudo escribir el archivo en RAM/Disco
        if (file_put_contents($temp_file, $contenido_sql) === false) {
            error_log("Error Restauración: Fallo al escribir el archivo temporal en el servidor.");
            return ['estatus' => false, 'mensaje' => 'Error de E/S en el servidor al preparar la restauración.'];
        }

        $this->removeDefinerFromSql($temp_file);

        file_put_contents($temp_file, $contenido_sql);

        // Llamamos al método para obtener las credenciales administrativas protegidas
        $creds = $this->obtenerCredencialesAdmin();

        // Construcción del comando blindado
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
        
        // Borramos el archivo temporal
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
            return [
                'estatus' => false, 
                'mensaje' => "Error al procesar el archivo SQL. Verifique el formato."
            ];
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
        return (DIRECTORY_SEPARATOR === '\\') ? self::MYSQLDUMP_WIN : self::MYSQLDUMP_LINUX;
    }

    private function getMysqlPath()
    {
        return (DIRECTORY_SEPARATOR === '\\') ? self::MYSQL_WIN : self::MYSQL_LINUX;
    }

    /**
     * Obtiene el directorio donde se almacenan los backups.
     */
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

    /**
     * Elimina las cláusulas DEFINER del archivo SQL para permitir importación sin SUPER
     */
    private function removeDefinerFromSql($filepath)
    {
        $content = file_get_contents($filepath);
        // Elimina cualquier ocurrencia de DEFINER=usuario@host (con o sin backticks)
        $content = preg_replace('/\s*DEFINER\s*=\s*[^\s]+/i', '', $content);
        file_put_contents($filepath, $content);
    }

    private function obtenerCredencialesAdmin()
    {
        if (DIRECTORY_SEPARATOR === '\\') { // Si es Windows (Local)
            return [
                'user' => getenv('DB_BACKUP_USER') ?: 'root',
                'pass' => getenv('DB_BACKUP_PASS') ?: ''
            ];
        }
        // En producción (AlwaysData), devolvemos las credenciales estándar
        return [
            'user' => DB_USER,
            'pass' => DB_PASS
        ];
    }

}