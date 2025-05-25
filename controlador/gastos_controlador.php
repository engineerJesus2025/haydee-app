<?php
require_once "modelo/gastos_modelo.php";
require_once "modelo/banco_modelo.php";
require_once "modelo/proveedores_modelo.php";

$gastos_obj = new Gastos();
$banco_obj = new Banco();
$proveedor_obj = new Proveedores();

$proveedores = $proveedor_obj->consultar();
$banco = $banco_obj->consultar();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    if ($operacion == "consulta") {
        echo json_encode($gastos_obj->consultar());
    } elseif ($operacion == "registrar") {
        $fecha = $_POST["fecha"];
        $mes = date("m", strtotime($fecha));
        $anio = date("Y", strtotime($fecha));
        $monto = $_POST["monto"];

        // Verificamos si ya existe el mes y el año de gasto_mes y sino, se crea
        $gasto_mes = $gastos_obj->consultar_gasto_mes($mes, $anio);
        if (!$gasto_mes) {
            $gastos_obj->registrar_gasto_mes($mes, $anio);
            $gasto_mes = $gastos_obj->consultar_gasto_mes($mes, $anio);
        }
        $gastos_obj->set_gasto_mes_id($gasto_mes["id_gasto_mes"]);

        $tipo_gasto = $_POST["tipo_gasto"];
        $tasa_dolar = $_POST["tasa_dolar"];
        $metodo_pago = $_POST["metodo_pago"];
        $referencia = $_POST["referencia"];
        $imagen = $_POST["imagen"];
        $descripcion_gasto = $_POST["descripcion_gasto"];
        $banco = $_POST["banco"];
        $proveedor = $_POST["proveedor"];

        echo json_encode($gastos_obj->registrar());

    } elseif ($operacion == "listar_gastos_mes") {
        echo json_encode($gastos_obj->listar_gastos_mes());
    } elseif ($operacion == "filtrar_gastos_mes") {
        $id_mes = $_POST["gasto_mes_id"];
        echo json_encode($gastos_obj->filtrar_por_mes($id_mes));
    } elseif ($operacion == "totales_metodo_pago") {
        $id_mes = $_POST["gasto_mes_id"];
        echo json_encode($gastos_obj->total_por_metodo_pago($id_mes));
    } 
    
    
    
    elseif ($operacion == "consulta_especifica") {
        $id_gasto = $_POST["id_gasto"];
        $gastos_obj->set_id_gasto($id_gasto);
        echo json_encode($gastos_obj->consultar_gasto_id());

    } elseif ($operacion == "modificar") {
        $id_gasto = $_POST["id_gasto"];
        $fecha = $_POST["fecha"];
        $monto = $_POST["monto"];
        $tipo_gasto = $_POST["tipo_gasto"];
        $tasa_dolar = $_POST["tasa_dolar"];
        $metodo_pago = $_POST["metodo_pago"];
        $referencia = $_POST["referencia"];
        $imagen = $_POST["imagen"];
        $descripcion_gasto = $_POST["descripcion_gasto"];
        $banco = $_POST["banco"];
        $proveedor = $_POST["proveedor"];

        $gastos_obj->set_id_gasto($id_gasto);
        $gastos_obj->set_fecha($fecha);
        $gastos_obj->set_monto($monto);
        $gastos_obj->set_tipo_gasto($tipo_gasto);
        $gastos_obj->set_tasa_dolar($tasa_dolar);
        $gastos_obj->set_metodo_pago($metodo_pago);
        $gastos_obj->set_referencia($referencia);
        $gastos_obj->set_imagen($imagen);
        $gastos_obj->set_descripcion_gasto($descripcion_gasto);
        $gastos_obj->set_banco_id($banco);
        $gastos_obj->set_proveedor_id($proveedor);
        echo json_encode($gastos_obj->editar_gasto());

    } elseif ($operacion == "eliminar") {
        $id_gasto = $_POST["id_gasto"];
        $gastos_obj->set_id_gasto($id_gasto);
        echo json_encode($gastos_obj->eliminar_gasto());

    } elseif ($operacion == "ultimo_id") {
        echo json_encode($gastos_obj->lastId());
    }

    exit;
}

require_once "vista/gastos/gastos_vista.php";


