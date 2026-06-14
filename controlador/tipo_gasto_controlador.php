<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_TIPO_GASTO, $operacion);
    
    $reglas = TipoGasto::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        // Pasamos el ID para evitar choques de campos UNIQUE al modificar
        $contexto = ['exclude_id' => $_POST['id_tipo_gasto'] ?? null];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $tipoGasto = new TipoGasto();
    $tipoGasto->set_id_tipo_gasto($_POST['id_tipo_gasto'] ?? null);
    $tipoGasto->set_nombre_tipo_gasto($_POST['nombre_tipo_gasto'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($tipoGasto, Modulo::GESTIONAR_TIPO_GASTO);
    
    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $tipoGasto->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
                break;

            case 'consultar_tipo_gasto':
                $respuesta = $tipoGasto->realizar_consulta('consultar_tipo_gasto');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
                break;

            case 'registrar_tipo_gasto':
                $respuesta = $tipoGasto->realizar_consulta('registrar_tipo_gasto');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::REGISTRAR); }
                break;

            case 'modificar_tipo_gasto':
                $auditor->capturarDatosAnteriores('consultar_tipo_gasto');
                $respuesta = $tipoGasto->realizar_consulta('modificar_tipo_gasto');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::MODIFICAR); }
                break;

            case 'eliminar_tipo_gasto':
                $auditor->capturarDatosAnteriores('consultar_tipo_gasto');
                $respuesta = $tipoGasto->realizar_consulta('eliminar_tipo_gasto');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador tipo gasto: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($tipoGasto)) { $tipoGasto->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            echo json_encode($respuesta);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_TIPO_GASTO);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_TIPO_GASTO);
$btn_nuevo = [
    'target'  => '#modal_tipo_gasto',
    'texto'   => 'Nuevo Tipo de Gasto',
    'tooltip' => 'Registrar Nuevo Tipo de Gasto'
];
$placeholder_buscar = "Buscar tipo de gasto...";

require_once "vista/tipo_gasto/tipo_gasto_vista.php";

