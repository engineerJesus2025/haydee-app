<?php 
require_once "modelo/conciliacion_bancaria_modelo.php";
require_once "modelo/movimientos_bancarios_modelo.php";
require_once "modelo/banco_modelo.php";

$conciliacion_obj = new Conciliacion_bancaria();
$movimientos_obj = new Movimientos_bancarios();
$banco_obj = new Banco();

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "verificar_conciliacion"){
    	$fecha_original = date("Y-m-d"); // Ejemplo de fecha
		$fecha_nueva = strtotime('-1 month', strtotime($fecha_original));
		// $fecha_buscar = date('Y-m-d', $fecha_nueva);

        $conciliacion_obj->set_fecha_inicio($fecha_nueva);

        echo  json_encode($conciliacion_obj->verificar_conciliacion());
    }
    //Despues de cada echo se regresa al javascript como respuesta en json
    elseif ($operacion == "crear_conciliacion") {
        $fecha_original = date("Y-m-d");
        $fecha_nueva = strtotime('-1 month', strtotime($fecha_original));
        
        $fecha_inicio = date('Y-m', $fecha_nueva);
        $fecha_fin = date('Y-m-t', $fecha_nueva);

        $conciliacion_obj->set_fecha_inicio($fecha_inicio . "-01");
        $conciliacion_obj->set_fecha_fin($fecha_fin);
        $conciliacion_obj->set_estado("Sin Conciliar");

        //se ejecuta la funcion:
        echo  json_encode($conciliacion_obj->crear_conciliacion());
        //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
    }
    else if ($operacion == "verificar_meses_conciliados"){

        $conciliacion_obj->set_estado("Sin Conciliar");

        echo  json_encode($conciliacion_obj->verificar_meses_conciliados());  
    }
    else if ($operacion == "buscar_mes"){
        $fecha = strtotime($_POST["fecha"]);
        
        $conciliacion_obj->set_fecha_inicio($fecha);

        echo  json_encode($conciliacion_obj->buscar_mes());  
    }
    else if ($operacion == "consultar_movimientos"){
        $fecha = strtotime($_POST["fecha"]);
        
        $movimientos_obj->set_fecha($fecha);

        echo  json_encode($movimientos_obj->consultar_movimientos());      
    }
    else if ($operacion == "consultar_movimiento"){
        $id = $_POST["id_movimiento"];
        
        $movimientos_obj->set_id_movimiento($id);

        echo  json_encode($movimientos_obj->buscar_movimiento());
    }
    else if ($operacion == "registrar_movimiento"){
        $fecha = $_POST["fecha"];
        $monto = $_POST["monto"];
        $referencia = $_POST["referencia"];
        $tipo_movimiento = $_POST["tipo_movimiento"];
        $banco_id = $_POST["banco_id"];
        $monto_diferencia = $_POST["monto_diferencia"];
        $tipo_diferencia = $_POST["tipo_diferencia"];
        $conciliacion_id = $_POST["conciliacion_id"];
        $gasto_pago = $_POST["gasto_pago"];
        $gasto_pago_id = $_POST["gasto_pago_id"];

        if ($gasto_pago == "Ingreso") {
            $movimientos_obj->set_pago_id($gasto_pago_id);
            $movimientos_obj->set_gasto_id(null);
        }
        else if ($gasto_pago == "Egreso") {
            $movimientos_obj->set_pago_id(null);
            $movimientos_obj->set_gasto_id($gasto_pago_id);
        }
        else{
            $movimientos_obj->set_pago_id(null);
            $movimientos_obj->set_gasto_id(null);
        }

        $movimientos_obj->set_fecha($fecha);
        $movimientos_obj->set_monto($monto);
        $movimientos_obj->set_referencia($referencia);
        $movimientos_obj->set_tipo_movimiento($tipo_movimiento);
        $movimientos_obj->set_banco_id($banco_id);
        $movimientos_obj->set_monto_diferencia($monto_diferencia);
        $movimientos_obj->set_tipo_diferencia($tipo_diferencia);
        $movimientos_obj->set_conciliacion_id($conciliacion_id);        

        echo  json_encode($movimientos_obj->registrar());
    }
    else if ($operacion == "modificar_movimiento"){
        $id_movimiento = $_POST["id_movimiento"];
        $fecha = $_POST["fecha"];
        $monto = $_POST["monto"];
        $referencia = $_POST["referencia"];
        $tipo_movimiento = $_POST["tipo_movimiento"];
        $banco_id = $_POST["banco_id"];
        $monto_diferencia = $_POST["monto_diferencia"];
        $tipo_diferencia = $_POST["tipo_diferencia"];
        $conciliacion_id = $_POST["conciliacion_id"];

        $movimientos_obj->set_id_movimiento($id_movimiento);
        $movimientos_obj->set_fecha($fecha);
        $movimientos_obj->set_monto($monto);
        $movimientos_obj->set_referencia($referencia);
        $movimientos_obj->set_tipo_movimiento($tipo_movimiento);
        $movimientos_obj->set_banco_id($banco_id);
        $movimientos_obj->set_monto_diferencia($monto_diferencia);
        $movimientos_obj->set_tipo_diferencia($tipo_diferencia);
        $movimientos_obj->set_conciliacion_id($conciliacion_id);

        echo  json_encode($movimientos_obj->editar());
    }
    else if ($operacion == "eliminar_movimiento"){
        $id = $_POST["id_movimiento"];
        
        $movimientos_obj->set_id_movimiento($id);

        echo  json_encode($movimientos_obj->eliminar());
    }
    exit;//es salida en ingles... No puede faltar
}

if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "referencia"){
            $movimientos_obj->set_referencia($_POST["referencia"]);
            echo  json_encode($movimientos_obj->verificar_referencia());
        }
        exit;
    }

$registros_bancos = $banco_obj->consultar();

if($accion == "inicio"){    
    require_once "vista/conciliacion_bancaria/conciliacion_bancaria_vista.php";
}


?>