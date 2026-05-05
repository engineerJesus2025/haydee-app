<?php
use haydee\servicios\Sesiones;
use haydee\modelo\AnioFiscal;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\servicios\GestorAuditoria; 
// Verificaciones de seguridad
Sesiones::validarMetodoHTTP(['GET', 'POST']);
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_ANIO_FISCAL, CONSULTAR);

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(GESTIONAR_ANIO_FISCAL, $operacion);

    $reglas = AnioFiscal::obtenerReglas($operacion);

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

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    // Instancia del modelo
    $anioFiscal = new AnioFiscal();
    // Asignación masiva
    $anioFiscal->set_id_anio_fiscal($_POST['id_anio_fiscal'] ?? null);
    $anioFiscal->set_fecha_inicio($_POST['fecha_inicio'] ?? null);
    $anioFiscal->set_fecha_cierre($_POST['fecha_cierre'] ?? null);
    $anioFiscal->set_estado($_POST['estado'] ?? null);
    $anioFiscal->set_descripcion($_POST['descripcion'] ?? null);

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($anioFiscal, GESTIONAR_ANIO_FISCAL);

    try {
        switch ($operacion) {
            case 'consultar_anios_fiscales':
                $respuesta = $anioFiscal->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'registrar':
                $respuesta = $anioFiscal->realizar_consulta('registrar');

                http_response_code($respuesta['estatus'] ? 201 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'consulta_especifica':
                $respuesta = $anioFiscal->realizar_consulta('consultar_anio_fiscal');

                http_response_code($respuesta['estatus'] ? 200 : 404);
                break;

            case 'modificar':
                $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
                $respuesta = $anioFiscal->realizar_consulta('modificar');
                
                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('modificar');
                }
                break;
            case 'eliminar':
                $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
                $respuesta = $anioFiscal->realizar_consulta('eliminar');
                
                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('eliminar');
                }
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(500);
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
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_ANIO_FISCAL);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_ANIO_FISCAL);

$btn_nuevo = [
    'target'  => '#modal_anio_fiscal',
    'texto'   => 'Nuevo Año Fiscal',
    'tooltip' => 'Registrar Nuevo Año Fiscal'
];
$placeholder_buscar = "Buscar año fiscal...";

// Cargar la vista
require_once "vista/anio_fiscal/anio_fiscal_vista.php";