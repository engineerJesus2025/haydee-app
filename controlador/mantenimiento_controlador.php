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
                        Bitacora::registrar(REGISTRAR, GESTIONAR_MANTENIMIENTO, "Copia de seguridad generada: $db");
                    }
                } else {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Base de datos no válida'];
                }
                break;

            case 'descargar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                if (in_array($db, ['negocio', 'seguridad'])) {
                    // Este método no retorna JSON, envía el archivo directamente
                    $mantenimiento->descargarCopiaSeguridad($db);
                    exit; // No se debe seguir ejecutando
                } else {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Base de datos no válida'];
                }
                break;

            case 'obtener_copias':
                $respuesta = $mantenimiento->obtenerCopias();
                break;

            case 'importar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                $fichero = $_POST['fichero'] ?? '';
                if (in_array($db, ['negocio', 'seguridad']) && !empty($fichero)) {
                    $respuesta = $mantenimiento->importarCopiaSeguridad($db, $fichero);
                    if ($respuesta['estatus']) {
                        Bitacora::registrar(REGISTRAR, GESTIONAR_MANTENIMIENTO, "Copia importada: $fichero en $db");
                    }
                } else {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Parámetros inválidos'];
                }
                break;

            case 'importar_archivo_sql':
                // Validación del archivo subido
                if (!isset($_FILES['fichero']) || $_FILES['fichero']['error'] !== UPLOAD_ERR_OK) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo no se subió correctamente.'];
                    break;
                }

                $archivo = $_FILES['fichero'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                if ($extension !== 'sql') {
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo debe ser formato SQL.'];
                    break;
                }

                $max_size = 50 * 1024 * 1024; // 50 MB
                if ($archivo['size'] > $max_size) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo no debe pesar más de 50 MB.'];
                    break;
                }

                $contenido_sql = file_get_contents($archivo['tmp_name']);
                if ($contenido_sql === false) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Error al leer el archivo.'];
                    break;
                }

                // Detectar la base de datos destino basado en el contenido
                $db = $mantenimiento->detectarBaseDesdeSQL($contenido_sql);
                if (!$db) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No se pudo determinar la base de datos destino. Asegúrate de que el archivo contenga "Database: seguridad_haydee_db" para seguridad o déjalo sin esa marca para negocio.'];
                    break;
                }

                $respuesta = $mantenimiento->importarSQL($contenido_sql, $db);
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_MANTENIMIENTO, "Archivo SQL importado en $db: " . $archivo['name']);
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
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

// Bitácora de acceso al módulo (solo al cargar la vista)
Bitacora::registrar(CONSULTAR, GESTIONAR_MANTENIMIENTO, "Acceso a módulo de mantenimiento");

// Cargar la vista
require_once 'vista/mantenimiento/mantenimiento_vista.php';