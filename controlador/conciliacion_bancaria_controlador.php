<?php 
require_once "modelo/conciliacion_bancaria_modelo.php";

$conciliacion_obj = new Conciliacion_bancaria();

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

    exit;//es salida en ingles... No puede faltar
    }


if($accion == "inicio"){    
    require_once "vista/conciliacion_bancaria/conciliacion_bancaria_vista.php";
}


?>