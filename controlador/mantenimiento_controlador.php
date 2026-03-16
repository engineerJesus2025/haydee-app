<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Mantenimiento;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_MANTENIMIENTO, CONSULTAR);

// Instancia de la clase Mantenimiento
$mantenimiento = new Mantenimiento();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try{
        switch ($operacion) {
            case 'generar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                if (in_array($db, ['negocio', 'seguridad'])) {
                    $respuesta = $mantenimiento->generarCopiaSeguridad($db);
                    if ($respuesta['estatus']) {
                        
                        // -> NUEVO: Registro manual <-
                        $detalles = [
                            'accion' => 'Generó copia de seguridad',
                            'base_datos' => strtoupper($db)
                        ];
                        Bitacora::registrar(RESPALDAR, GESTIONAR_MANTENIMIENTO, null, null, $detalles);

                    }
                } else {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Base de datos no válida'];
                }
                break;

            case 'descargar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                if (!in_array($db, ['negocio', 'seguridad'])) {
                    echo json_encode(['estatus' => false, 'mensaje' => 'Base de datos no válida']);
                    exit;
                }

                $backupFile = "recursos/backups/backup_{$db}.sql";

                if (file_exists($backupFile)) {
                    // -> NUEVO: Registro manual antes de descargar <-
                    $detalles = [
                        'accion' => 'Descargó archivo SQL',
                        'base_datos' => strtoupper($db),
                        'archivo' => basename($backupFile)
                    ];
                    Bitacora::registrar(RESPALDAR, GESTIONAR_MANTENIMIENTO, null, null, $detalles);

                    // Forzar la descarga del archivo
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($backupFile));
                    readfile($backupFile);
                    exit;
                } else {
                    echo json_encode(['estatus' => false, 'mensaje' => 'El archivo de respaldo no existe. Primero debe generarlo.']);
                    exit;
                }
                break;

            case 'restaurar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                if (!in_array($db, ['negocio', 'seguridad'])) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Base de datos no válida'];
                    break;
                }

                if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No se subió ningún archivo o hubo un error en la subida.'];
                    break;
                }

                $archivo_tmp = $_FILES['backup_file']['tmp_name'];
                $nombre_archivo = $_FILES['backup_file']['name'];
                
                // ... (Validaciones de extensión y tamaño se mantienen igual) ...
                $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
                if ($extension !== 'sql') {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Solo se permiten archivos SQL.'];
                    break;
                }

                $contenido_sql = file_get_contents($archivo_tmp);
                if (empty(trim($contenido_sql))) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo SQL está vacío.'];
                    break;
                }

                // ... (Validación de seguridad vs negocio se mantiene igual) ...
                $es_seguridad = stripos($contenido_sql, "Database: seguridad_haydee_db") !== false;
                if (($db === 'seguridad' && !$es_seguridad) || ($db === 'negocio' && $es_seguridad)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo no corresponde a la base de datos destino.'];
                    break;
                }

                $respuesta = $mantenimiento->importarSQL($contenido_sql, $db);
                if ($respuesta['estatus']) {
                    
                    // -> NUEVO: Registro manual <-
                    $detalles = [
                        'accion' => 'Restauró base de datos desde archivo local',
                        'base_datos' => strtoupper($db),
                        'archivo_subido' => $nombre_archivo
                    ];
                    Bitacora::registrar(RESTAURAR, GESTIONAR_MANTENIMIENTO, null, null, $detalles);

                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador mantenimiento: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($mantenimiento)) {
                $mantenimiento->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Bitácora de acceso al módulo (solo al cargar la vista por GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Bitacora::registrar(CONSULTAR, GESTIONAR_MANTENIMIENTO);
}

// Cargar la vista
require_once "vista/mantenimiento/mantenimiento_vista.php";