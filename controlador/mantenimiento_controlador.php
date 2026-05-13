<?php
use haydee\enums\Modulo;
use haydee\enums\Accion;

use haydee\servicios\Sesiones;
use haydee\modelo\Mantenimiento;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;

// Verificaciones de seguridad
Sesiones::autorizarAcceso(Modulo::GESTIONAR_MANTENIMIENTO, Accion::CONSULTAR);

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    // Verificar permiso específico por acción (403 si falla)
    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_MANTENIMIENTO, $operacion);

    // Validación de reglas (400 si falla)
    $reglas = Mantenimiento::obtenerReglas($operacion);
    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? 404 : 400;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $mantenimiento = new Mantenimiento();
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'obtener_copias':
                $respuesta = $mantenimiento->obtenerCopias();
                http_response_code($respuesta['estatus'] ? 200 : 400);
                break;

            case 'generar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                $respuesta = $mantenimiento->generarCopiaSeguridad($db);
                
                // 201 porque creamos un recurso nuevo (un archivo de backup)
                http_response_code($respuesta['estatus'] ? 201 : 500);
                
                if ($respuesta['estatus']) {
                    $detalles = ['accion' => 'GenerÃ³ copia de seguridad', 'base_datos' => strtoupper($db)];
                    Bitacora::registrar(Accion::RESPALDAR, Modulo::GESTIONAR_MANTENIMIENTO, null, null, $detalles);
                }
                break;

            case 'descargar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                $respuesta = null; // Para que el finally no imprima JSON

                $mantenimiento->descargarCopiaSeguridad($db);
                exit;

            case 'importar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                $fichero = $_POST['fichero'] ?? '';
                $respuesta = $mantenimiento->importarCopiaSeguridad($db, $fichero);
                
                http_response_code($respuesta['estatus'] ? 200 : 500);
                
                if ($respuesta['estatus']) {
                    $detalles = ['accion' => 'RestaurÃ³ desde servidor', 'base_datos' => strtoupper($db), 'archivo' => $fichero];
                    Bitacora::registrar(Accion::RESTAURAR, Modulo::GESTIONAR_MANTENIMIENTO, null, null, $detalles);
                }
                break;

            case 'importar_archivo_sql':
                $db = $_POST['db'] ?? '';
                
                // Validaciones previas de archivo (400 Bad Request)
                if (!isset($_FILES['fichero']) || $_FILES['fichero']['error'] !== UPLOAD_ERR_OK) {
                    http_response_code(400);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Error al subir el archivo.'];
                    break;
                }

                $contenido_sql = file_get_contents($_FILES['fichero']['tmp_name']);
                
                // Validación de seguridad cruzada
                $es_seguridad = stripos($contenido_sql, "Database: seguridad_haydee_db") !== false;
                if (($db === 'seguridad' && !$es_seguridad) || ($db === 'negocio' && $es_seguridad)) {
                    http_response_code(400);
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo no corresponde a la base de datos destino.'];
                    break;
                }

                $respuesta = $mantenimiento->importarSQL($contenido_sql, $db);
                http_response_code($respuesta['estatus'] ? 200 : 500);
                
                if ($respuesta['estatus']) {
                    $detalles = ['accion' => 'RestaurÃ³ desde PC', 'base_datos' => strtoupper($db)];
                    Bitacora::registrar(Accion::RESTAURAR, Modulo::GESTIONAR_MANTENIMIENTO, null, null, $detalles);
                }
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en Mantenimiento: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            $mantenimiento->cerrar();
            Bitacora::cerrarConexionBitacora();
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Carga normal de la vista (GET)
Bitacora::registrar(Accion::CONSULTAR, Modulo::GESTIONAR_MANTENIMIENTO);
require_once "vista/mantenimiento/mantenimiento_vista.php";
