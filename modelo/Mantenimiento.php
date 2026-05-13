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

    // VALIDACIONES CENTRALIZADAS
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
        $db_copiar = ($db === TipoBaseDatos::NEGOCIO) ? DB_NAME : DB_SECURITY;
        $mysqldump_path = $this->getMysqldumpPath();
        $backup_dir = $this->getBackupDir();
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $tipo = "MANUAL";

        $backup_file = $backup_dir . "backup_{$db_copiar}_{$timestamp}_{$tipo}.sql";

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

    /**
     * Importa un contenido SQL directamente a la base de datos especificada.
     * @param string $contenido_sql El texto del archivo .sql
     * @param string $db 'negocio' o 'seguridad'
     * @return array Respuesta con estatus y mensaje
     */
    public function importarSQL($contenido_sql, $db)
    {
        $nombre_db = ($db === 'negocio') ? DB_NAME : DB_SECURITY;
        $mysql_path = $this->getMysqlPath();
        
        // Creamos un archivo temporal para el contenido SQL
        $temp_file = tempnam(sys_get_temp_dir(), 'restore_');
        file_put_contents($temp_file, $contenido_sql);

        // Limpiamos DEFINERs para evitar errores de permisos
        $this->removeDefinerFromSql($temp_file);

        // Construcción del comando (usando credenciales de tus constantes)
        // -f obliga a continuar incluso si hay errores menores
        $comando = sprintf(
            "%s --host=%s --user=%s --password=%s %s < %s 2>&1",
            $mysql_path,
            DB_HOST,
            DB_USER,
            DB_PASS,
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