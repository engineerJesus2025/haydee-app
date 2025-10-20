<?php
require_once "modelo/solicitud_gasto_modelo.php";

$solicitud_gasto_obj = new Solicitud_gasto();
// <-- CAMBIO: Se ajusta la llamada a la nueva función del modelo
$fecha_actual = date("Y-m");
$presupuestos = $solicitud_gasto_obj->consultar_presupuesto($fecha_actual);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta") {
        echo json_encode($solicitud_gasto_obj->realizar_consulta('consultar'));
        
        exit;
    } 

    elseif ($operacion == "consulta_especifica") {
    $solicitud_gasto_obj->set_id_solicitud($_POST["id_solicitud"]);
    echo json_encode($solicitud_gasto_obj->realizar_consulta('consultar_solicitud_id'));
    exit;
}

    elseif ($operacion == "consultar_presupuesto") {
        // <-- CAMBIO: Se usan las variables correctas
        $presupuesto_id = $_POST["presupuesto_id"];
        $solicitud_gasto_obj->set_presupuesto_id($presupuesto_id);
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
}

    elseif ($operacion == "buscar_presupuesto_por_mes_anio") {
        // <-- CAMBIO: Se construye la fecha y se llama a la función correcta
        $mes = $_POST["mes"];
        $anio = $_POST["anio"];
        $fecha = "$anio-$mes";
        $respuesta = $solicitud_gasto_obj->consultar_presupuesto($fecha);
        echo json_encode($respuesta);
        exit;
    } 
    
    elseif ($operacion == "registrar") {
        // <-- CAMBIO: Se usa presupuesto_id en lugar de presupuesto_mensual_id
        $presupuesto_id = $_POST["presupuesto_id"];
        $solicitud_gasto_obj->set_fecha_reporte($_POST["fecha"]);
        $solicitud_gasto_obj->set_descripcion_necesidad($_POST["descripcion"]);
        $solicitud_gasto_obj->set_nombre_solicitante($_POST["nombre"]);
        $solicitud_gasto_obj->set_monto_estimado($_POST["monto_estimado"]);
        $solicitud_gasto_obj->set_estado($_POST["estado"]);
        $solicitud_gasto_obj->set_presupuesto_id($presupuesto_id); // <-- CAMBIO
        $solicitud_gasto_obj->set_prioridad($_POST["prioridad"]);

        echo json_encode($solicitud_gasto_obj->realizar_consulta('registrar'));
        exit;
    } 
    
    elseif ($operacion == "modificar") {
        // <-- CAMBIO: Se usan los nombres correctos de las variables y $_POST
        $id_solicitud = $_POST["id_solicitud"];
        $presupuesto_id = $_POST["presupuesto_id"];
        $monto_estimado = $_POST["monto_estimado"]; // <-- CAMBIO: Corregido de 'monto' a 'monto_estimado'

        $solicitud_gasto_obj->set_id_solicitud($id_solicitud);
        $solicitud_gasto_obj->set_fecha_reporte($_POST["fecha"]);
        $solicitud_gasto_obj->set_descripcion_necesidad($_POST["descripcion"]);
        $solicitud_gasto_obj->set_nombre_solicitante($_POST["nombre"]);
        $solicitud_gasto_obj->set_monto_estimado($monto_estimado);
        $solicitud_gasto_obj->set_estado($_POST["estado"]);
        $solicitud_gasto_obj->set_presupuesto_id($presupuesto_id); // <-- CAMBIO
        $solicitud_gasto_obj->set_prioridad($_POST["prioridad"]);

        echo json_encode($solicitud_gasto_obj->realizar_consulta('modificar'));
        exit;
    } elseif ($operacion == "eliminar") {
        $id_solicitud = $_POST["id_solicitud"];
        $solicitud_gasto_obj->set_id_solicitud($id_solicitud);
        echo json_encode($solicitud_gasto_obj->realizar_consulta('eliminar'));
    } elseif ($operacion == "ultimo_id") {
        echo json_encode($solicitud_gasto_obj->realizar_consulta('lastId'));
    }

    exit;
}
require_once "vista/solicitud_gasto/solicitud_gasto_vista.php";
