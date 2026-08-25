<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\modelo\Mantenimiento;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_MANTENIMIENTO, $operacion);

    $reglas = Mantenimiento::obtenerReglas($operacion);
    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $mantenimiento = new Mantenimiento();
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $codigoExito = HttpCodigo::OK->value;

    switch ($operacion) {
        case 'obtener_copias':
            $respuesta = $mantenimiento->obtenerCopias();
            break;

        case 'generar_copia_seguridad':
            $db = $_POST['db'] ?? '';
            $respuesta = $mantenimiento->generarCopiaSeguridad($db);
            
            if ($respuesta['estatus']) {
                $codigoExito = HttpCodigo::CREADO->value;
                $detalles = ['accion' => 'Generó copia de seguridad', 'base_datos' => strtoupper($db)];
                Bitacora::registrar(Accion::RESPALDAR, Modulo::GESTIONAR_MANTENIMIENTO, null, null, $detalles);
            }
            break;

        case 'descargar_copia_seguridad':
            $db = $_POST['db'] ?? '';
            $mantenimiento->descargarCopiaSeguridad($db);
            exit;

        case 'importar_copia_seguridad':
            $db = $_POST['db'] ?? '';
            $fichero = $_POST['fichero'] ?? '';
            $respuesta = $mantenimiento->importarCopiaSeguridad($db, $fichero);
            
            if ($respuesta['estatus']) {
                $detalles = ['accion' => 'Restauró desde servidor', 'base_datos' => strtoupper($db), 'archivo' => $fichero];
                Bitacora::registrar(Accion::RESTAURAR, Modulo::GESTIONAR_MANTENIMIENTO, null, null, $detalles);
            }
            break;

        case 'importar_archivo_sql':
            $db = $_POST['db'] ?? '';
            
            if (!isset($_FILES['fichero']) || $_FILES['fichero']['error'] !== UPLOAD_ERR_OK) {
                throw new HaydeeException('Error al subir el archivo.', HttpCodigo::BAD_REQUEST->value);
            }

            $contenido_sql = file_get_contents('compress.zlib://' . $_FILES['fichero']['tmp_name']);

            $palabras_prohibidas = ['grant all', 'create user', 'drop database', 'mysql.user'];
            foreach ($palabras_prohibidas as $prohibida) {
                if (stripos($contenido_sql, $prohibida) !== false) {
                    throw new HaydeeException('Seguridad: El archivo contiene sentencias administrativas no autorizadas.', HttpCodigo::BAD_REQUEST->value);
                }
            }

            if ($_FILES['fichero']['size'] > 15 * 1024 * 1024) {
                throw new HaydeeException('El archivo supera el límite de tamaño permitido (15MB).', HttpCodigo::BAD_REQUEST->value);
            }
            
            $es_seguridad = stripos($contenido_sql, "Database: seguridad_haydee_db") !== false;
            if (($db === 'seguridad' && !$es_seguridad) || ($db === 'negocio' && $es_seguridad)) {
                throw new HaydeeException('El archivo no corresponde a la base de datos destino.', HttpCodigo::BAD_REQUEST->value);
            }

            $respuesta = $mantenimiento->importarSQL($contenido_sql, $db);
            
            if ($respuesta['estatus']) {
                $detalles = ['accion' => 'Restauró desde PC', 'base_datos' => strtoupper($db)];
                Bitacora::registrar(Accion::RESTAURAR, Modulo::GESTIONAR_MANTENIMIENTO, null, null, $detalles);
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    $mantenimiento->cerrar();
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

Bitacora::registrar(Accion::CONSULTAR, Modulo::GESTIONAR_MANTENIMIENTO);
require_once "vista/mantenimiento/mantenimiento_vista.php";