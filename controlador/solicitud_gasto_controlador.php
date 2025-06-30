<?php
require_once "modelo/solicitud_gasto_modelo.php";

$solicitud_gasto_obj = new Solicitud_gasto();
$mes = date("m");
$anio = date("Y");
$presupuestos = $solicitud_gasto_obj->consultar_presupuesto_mensual($mes, $anio);
//CAMBIAR CUANDO SE HAGA EL MODULO DE PRESUPUESTO MENSUAL
// $presupuesto_mensual_obj = new Presupuesto_mensual();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    if ($operacion == "consulta") {
        echo json_encode($solicitud_gasto_obj->consultar());
        exit;
    } elseif ($operacion == "consultar_presupuesto") {
        $presupuesto_mensual_id = $_POST["presupuesto_id"];
        $solicitud_gasto_obj->set_presupuesto_mensual_id($presupuesto_mensual_id);
        $respuesta = $solicitud_gasto_obj->consultar_presupuesto_disponible();
        echo json_encode($respuesta);
                exit;

    }
    elseif ($operacion == "meses_anios_con_presupuesto") {
    $respuesta = $solicitud_gasto_obj->listar_meses_anios_con_presupuesto();
    echo json_encode([
        "estatus" => true,
        "data" => $respuesta
    ]);
    exit;
} elseif ($operacion == "buscar_presupuesto_por_mes_anio") {
    $mes = $_POST["mes"];
    $anio = $_POST["anio"];
    $respuesta = $solicitud_gasto_obj->consultar_presupuesto_mensual($mes, $anio);
    echo json_encode($respuesta);
    exit;

    } elseif ($operacion == "registrar") {
        $fecha_reporte = $_POST["fecha"];
        $mes = date("m", strtotime($fecha_reporte));
        $anio = date("Y", strtotime($fecha_reporte));

        $descripcion_necesidad = $_POST["descripcion"];
        $nombre_solicitante = $_POST["nombre"];
        $monto_estimado = $_POST["monto_estimado"];
        $presupuesto_mensual_id = $_POST["presupuesto_mensual_id"];
        $prioridad = $_POST["prioridad"];
        $estado = $_POST["estado"];

        $solicitud_gasto_obj->set_fecha_reporte($fecha_reporte);
        $solicitud_gasto_obj->set_descripcion_necesidad($descripcion_necesidad);
        $solicitud_gasto_obj->set_nombre_solicitante($nombre_solicitante);
        $solicitud_gasto_obj->set_monto_estimado($monto_estimado);
        $solicitud_gasto_obj->set_estado($estado);
        $solicitud_gasto_obj->set_presupuesto_mensual_id($presupuesto_mensual_id);
        $solicitud_gasto_obj->set_prioridad($prioridad);

        echo json_encode($solicitud_gasto_obj->registrar());
                exit;

    } elseif ($operacion == "consulta_especifica") {
        $id_solicitud = $_POST["id_solicitud"];
        $solicitud_gasto_obj->set_id_solicitud($id_solicitud);
        echo json_encode($solicitud_gasto_obj->consultar_solicitud_id());
        exit;

    } elseif ($operacion == "listar_solicitudes_mes") {
        echo json_encode($solicitud_gasto_obj->listar_solicitud_mes());
                exit;

    } elseif ($operacion == "filtrar_solicitudes_mes") {
        $fecha_reporte = $_POST["fecha_reporte"];
        $solicitud_gasto_obj->set_fecha_reporte($fecha_reporte);
        echo json_encode($solicitud_gasto_obj->filtrar_por_mes());
                exit;

    } elseif ($operacion == "modificar") {
        $id_solicitud = $_POST["id_solicitud"];
        $fecha_reporte = $_POST["fecha"];
        $mes = date("m", strtotime($fecha_reporte));
        $anio = date("Y", strtotime($fecha_reporte));

        $descripcion_necesidad = $_POST["descripcion"];
        $nombre_solicitante = $_POST["nombre"];
        $monto_estimado = $_POST["monto"];
        $estado = $_POST["estado"];
        $presupuesto_mensual_id = $_POST["presupuesto_mensual_id"];
        $prioridad = $_POST["prioridad"];

        $solicitud_gasto_obj->set_id_solicitud($id_solicitud);
        $solicitud_gasto_obj->set_fecha_reporte($fecha_reporte);
        $solicitud_gasto_obj->set_descripcion_necesidad($descripcion_necesidad);
        $solicitud_gasto_obj->set_nombre_solicitante($nombre_solicitante);
        $solicitud_gasto_obj->set_monto_estimado($monto_estimado);
        $solicitud_gasto_obj->set_estado($estado);
        $solicitud_gasto_obj->set_presupuesto_mensual_id($presupuesto_mensual_id);
        $solicitud_gasto_obj->set_prioridad($prioridad);

        echo json_encode($solicitud_gasto_obj->editar_solicitud());
                exit;

    } elseif ($operacion == "eliminar") {
        $id_solicitud = $_POST["id_solicitud"];
        $solicitud_gasto_obj->set_id_solicitud($id_solicitud);
        echo json_encode($solicitud_gasto_obj->eliminar_solicitud());
    } elseif ($operacion == "ultimo_id") {
        echo json_encode($solicitud_gasto_obj->lastId());
    }

    exit;
}
require_once "vista/solicitud_gasto/solicitud_gasto_vista.php";
