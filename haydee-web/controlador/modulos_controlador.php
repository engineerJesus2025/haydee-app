<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Modulos;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_MODULOS, $operacion);
    
    $reglas = Modulos::obtenerReglas($operacion);

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

    $obj_modulo = new Modulos();
    $obj_modulo->set_id_modulo($_POST['id_modulo'] ?? null);
    $obj_modulo->set_nombre($_POST['nombre'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($obj_modulo, Modulo::GESTIONAR_MODULOS);
    
    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $obj_modulo->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
                break;

            case 'consultar_modulo':
                $respuesta = $obj_modulo->realizar_consulta('consultar_modulo');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
                break;

            case 'registrar_modulo':
                $respuesta = $obj_modulo->realizar_consulta('registrar_modulo');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::REGISTRAR); }
                break;

            case 'modificar_modulo':
                $auditor->capturarDatosAnteriores('consultar_modulo');
                $respuesta = $obj_modulo->realizar_consulta('modificar_modulo');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::MODIFICAR); }
                break;

            case 'eliminar_modulo':
                $auditor->capturarDatosAnteriores('consultar_modulo');
                $respuesta = $obj_modulo->realizar_consulta('eliminar_modulo');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador modulos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($obj_modulo)) { $obj_modulo->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            echo json_encode($respuesta);
            exit;
        }
    }
}

// Bloque de vista...
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_MODULOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_MODULOS);
$btn_nuevo = [
    'target'  => '#modal_modulo',
    'texto'   => 'Nuevo Módulo',
    'tooltip' => 'Registrar Nuevo Módulo'
];
$placeholder_buscar = "Buscar módulo...";

require_once "vista/modulos/modulos_vista.php";

