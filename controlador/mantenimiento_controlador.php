<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\modelo\Mantenimiento;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;

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
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
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
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'generar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                $respuesta = $mantenimiento->generarCopiaSeguridad($db);
                
                // 201 porque creamos un recurso nuevo (un archivo de backup)
                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::ERROR_INTERNO->value);
                
                if ($respuesta['estatus']) {
                    $detalles = ['accion' => 'Generó copia de seguridad', 'base_datos' => strtoupper($db)];
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
                
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::ERROR_INTERNO->value);
                
                if ($respuesta['estatus']) {
                    $detalles = ['accion' => 'Restauró desde servidor', 'base_datos' => strtoupper($db), 'archivo' => $fichero];
                    Bitacora::registrar(Accion::RESTAURAR, Modulo::GESTIONAR_MANTENIMIENTO, null, null, $detalles);
                }
                break;

            case 'importar_archivo_sql':
                $db = $_POST['db'] ?? '';
                
                // Validaciones previas de archivo (400 Bad Request)
                if (!isset($_FILES['fichero']) || $_FILES['fichero']['error'] !== UPLOAD_ERR_OK) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'Error al subir el archivo.'];
                    break;
                }

                // $contenido_sql = file_get_contents($_FILES['fichero']['tmp_name']);
                $contenido_sql = file_get_contents('compress.zlib://' . $_FILES['fichero']['tmp_name']);

                $palabras_prohibidas = ['grant all', 'create user', 'drop database', 'mysql.user'];
                foreach ($palabras_prohibidas as $prohibida) {
                    if (stripos($contenido_sql, $prohibida) !== false) {
                        http_response_code(HttpCodigo::BAD_REQUEST->value);
                        echo json_encode(['estatus' => false, 'mensaje' => 'Seguridad: El archivo contiene sentencias administrativas no autorizadas.']);
                        exit;
                    }
                }

                if ($_FILES['fichero']['size'] > 15 * 1024 * 1024) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    echo json_encode(['estatus' => false, 'mensaje' => 'El archivo supera el límite de tamaño permitido (15MB).']);
                    exit;
                }
                
                // Validación de seguridad cruzada
                $es_seguridad = stripos($contenido_sql, "Database: seguridad_haydee_db") !== false;
                if (($db === 'seguridad' && !$es_seguridad) || ($db === 'negocio' && $es_seguridad)) {
                    http_response_code(HttpCodigo::BAD_REQUEST->value);
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo no corresponde a la base de datos destino.'];
                    break;
                }

                $respuesta = $mantenimiento->importarSQL($contenido_sql, $db);
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::ERROR_INTERNO->value);
                
                if ($respuesta['estatus']) {
                    $detalles = ['accion' => 'Restauró desde PC', 'base_datos' => strtoupper($db)];
                    Bitacora::registrar(Accion::RESTAURAR, Modulo::GESTIONAR_MANTENIMIENTO, null, null, $detalles);
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
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

