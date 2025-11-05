<?php
require_once "vista/componentes/sesion.php";
require_once "modelo/solicitud_gasto_modelo.php";

$solicitud_gasto_obj = new Solicitud_gasto();
$fecha_actual = date("Y-m");
$presupuestos = $solicitud_gasto_obj->consultar_presupuesto($fecha_actual);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta") {
        $solicitud_gasto_obj->registrar_bitacora(CONSULTAR, GESTIONAR_SOLICITUD_GASTO, "TODAS LAS SOLICITUDES DE GASTO");
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
        $presupuesto_id = $_POST["presupuesto_id"];
        $solicitud_gasto_obj->set_fecha_reporte($_POST["fecha"]);
        $solicitud_gasto_obj->set_descripcion_necesidad($_POST["descripcion"]);
        $solicitud_gasto_obj->set_nombre_solicitante($_POST["nombre"]);
        $solicitud_gasto_obj->set_monto_estimado($_POST["monto_estimado"]);
        $solicitud_gasto_obj->set_estado($_POST["estado"]);
        $solicitud_gasto_obj->set_presupuesto_id($presupuesto_id);
        $solicitud_gasto_obj->set_prioridad($_POST["prioridad"]);

        $respuesta = $solicitud_gasto_obj->realizar_consulta('registrar');

        if ($respuesta["estatus"]) {
            $solicitud_gasto_obj->registrar_bitacora(REGISTRAR, GESTIONAR_SOLICITUD_GASTO, "Solicitud "  . $_POST["descripcion"] . " de " . $_POST["nombre"]);
        }
        echo json_encode($respuesta);
        exit;
    } 
    
    elseif ($operacion == "modificar") {
        $id_solicitud = $_POST["id_solicitud"];
        $presupuesto_id = $_POST["presupuesto_id"];
        $monto_estimado = $_POST["monto_estimado"];

        $solicitud_gasto_obj->set_id_solicitud($id_solicitud);
        $solicitud_gasto_obj->set_fecha_reporte($_POST["fecha"]);
        $solicitud_gasto_obj->set_descripcion_necesidad($_POST["descripcion"]);
        $solicitud_gasto_obj->set_nombre_solicitante($_POST["nombre"]);
        $solicitud_gasto_obj->set_monto_estimado($monto_estimado);
        $solicitud_gasto_obj->set_estado($_POST["estado"]);
        $solicitud_gasto_obj->set_presupuesto_id($presupuesto_id);
        $solicitud_gasto_obj->set_prioridad($_POST["prioridad"]);

        $resultado = $solicitud_gasto_obj->realizar_consulta('modificar');
        if ($resultado["estatus"]) {
            $solicitud_gasto_obj->registrar_bitacora(MODIFICAR, GESTIONAR_SOLICITUD_GASTO, "Solicitud "  . $_POST["descripcion"] . " de " . $_POST["nombre"]);
        }
        echo json_encode($resultado);
        exit;


    } elseif ($operacion == "eliminar") {
        $id_solicitud = $_POST["id_solicitud"];
        $solicitud_gasto_obj->set_id_solicitud($id_solicitud);
        $solicitud_alterada = $solicitud_gasto_obj->realizar_consulta('consultar_solicitud_id');
        $resultado = $solicitud_gasto_obj->realizar_consulta('eliminar');

        if ($resultado["estatus"]) {
            $solicitud_gasto_obj->registrar_bitacora(ELIMINAR, GESTIONAR_SOLICITUD_GASTO, "Solicitud " . $solicitud_alterada["descripcion_necesidad"] . " de " . $solicitud_alterada["nombre_solicitante"]);
        }
        echo json_encode($resultado);
    }

    elseif ($operacion == "ultimo_id") {
        echo json_encode($solicitud_gasto_obj->realizar_consulta('lastId'));
    }

    exit;
}
require_once "vista/solicitud_gasto/solicitud_gasto_vista.php";
