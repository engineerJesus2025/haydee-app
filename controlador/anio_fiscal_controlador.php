<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\modelo\AnioFiscal;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria; 

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_ANIO_FISCAL, $operacion);

    $reglas = AnioFiscal::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    // Instancia del modelo
    $anioFiscal = new AnioFiscal();
    // Asignacion masiva
    $anioFiscal->set_id_anio_fiscal($_POST['id_anio_fiscal'] ?? null);
    $anioFiscal->set_fecha_inicio($_POST['fecha_inicio'] ?? null);
    $anioFiscal->set_fecha_cierre($_POST['fecha_cierre'] ?? null);
    $anioFiscal->set_estado($_POST['estado'] ?? null);
    $anioFiscal->set_descripcion($_POST['descripcion'] ?? null);

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($anioFiscal, Modulo::GESTIONAR_ANIO_FISCAL);

    try {
        switch ($operacion) {
            case 'consultar_anios_fiscales':
                $respuesta = $anioFiscal->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::CONSULTAR);
                }
                break;

            case 'registrar':
                $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
                $respuesta = $anioFiscal->realizar_consulta('registrar');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::REGISTRAR);
                }
                break;

            case 'consulta_especifica':
                $respuesta = $anioFiscal->realizar_consulta('consultar_anio_fiscal');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
                break;

            case 'modificar':
                $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
                $respuesta = $anioFiscal->realizar_consulta('modificar');
                
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::MODIFICAR);
                }
                break;
            case 'eliminar':
                $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
                $respuesta = $anioFiscal->realizar_consulta('eliminar');
                
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::ELIMINAR);
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            $anioFiscal->cerrar();
            Bitacora::cerrarConexionBitacora();
            echo json_encode($respuesta);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_ANIO_FISCAL);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_ANIO_FISCAL);

$btn_nuevo = [
    'target'  => '#modal_anio_fiscal',
    'texto'   => 'Nuevo Año Fiscal',
    'tooltip' => 'Registrar Nuevo Año Fiscal'
];
$placeholder_buscar = "Buscar año fiscal...";

// Cargar la vista
require_once "vista/anio_fiscal/anio_fiscal_vista.php";

