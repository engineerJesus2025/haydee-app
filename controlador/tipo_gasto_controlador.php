<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_TIPO_GASTO, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    
    $reglas = TipoGasto::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        // Pasamos el ID para evitar choques de campos UNIQUE al modificar
        $contexto = ['exclude_id' => $_POST['id_tipo_gasto'] ?? null];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $tipoGasto = new TipoGasto();
    $tipoGasto->set_id_tipo_gasto($_POST['id_tipo_gasto'] ?? null);
    $tipoGasto->set_nombre_tipo_gasto($_POST['nombre_tipo_gasto'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($tipoGasto, GESTIONAR_TIPO_GASTO);
    
    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $tipoGasto->realizar_consulta('consultar');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('consultar'); }
                break;

            case 'consultar_tipo_gasto':
                $respuesta = $tipoGasto->realizar_consulta('consultar_tipo_gasto');
                break;

            case 'registrar_tipo_gasto':
                $respuesta = $tipoGasto->realizar_consulta('registrar_tipo_gasto');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('registrar'); }
                break;

            case 'modificar_tipo_gasto':
                $auditor->capturarDatosAnteriores('consultar_tipo_gasto');
                $respuesta = $tipoGasto->realizar_consulta('modificar_tipo_gasto');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('modificar'); }
                break;

            case 'eliminar_tipo_gasto':
                $auditor->capturarDatosAnteriores('consultar_tipo_gasto');
                $respuesta = $tipoGasto->realizar_consulta('eliminar_tipo_gasto');
                if ($respuesta['estatus']) { $auditor->registrarAuditoria('eliminar'); }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador tipo gasto: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($tipoGasto)) { $tipoGasto->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_TIPO_GASTO);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_TIPO_GASTO);
require_once "vista/tipo_gasto/tipo_gasto_vista.php";