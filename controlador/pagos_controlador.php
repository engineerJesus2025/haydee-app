<?php
    require_once("modelo/pagos_modelo.php");
    require_once("modelo/banco_modelo.php");

    $obj_pago = new Pagos(); // Objeto banco
    $obj_banco = new Banco();
 
    $registro_banco = $obj_banco->consultar();

    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];
        
        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_pago->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $fecha = $_POST["fecha"];  
            $monto = $_POST["monto"];  
            $tasa_dolar = $_POST["tasa_dolar"]; 
            $estado = $_POST["estado"];
            $metodo_pago = $_POST["metodo_pago"];
            $banco_id = $_POST["banco_id"];
            $referencia = $_POST["referencia"];
            $imagen = $_POST["imagen"];
            $observacion = $_POST["observacion"];

            //se usan los setters correspondientes
            $obj_pago->set_fecha($fecha);
            $obj_pago->set_monto($monto);
            $obj_pago->set_tasa_dolar($tasa_dolar);
            $obj_pago->set_estado($estado);
            $obj_pago->set_metodo_pago($metodo_pago);
            $obj_pago->set_banco_id($banco_id);
            $obj_pago->set_referencia($referencia);
            $obj_pago->set_imagen($imagen);
            $obj_pago->set_observacion($observacion);

            //se ejecuta la funcion:
            echo  json_encode($obj_pago->registrar_pago());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_pago = $_POST["id_pago"];

            //se usan el setter correspondientes
            $obj_pago->set_id_pago($id_pago);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_pago->consultar_pago());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
 
        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $fecha = $_POST["fecha"];  
            $monto = $_POST["monto"];  
            $tasa_dolar = $_POST["tasa_dolar"]; 
            $estado = $_POST["estado"];
            $metodo_pago = $_POST["metodo_pago"];
            $banco_id = $_POST["banco_id"];  
            $referencia = $_POST["referencia"];
            $imagen = $_POST["imagen"];
            $observacion = $_POST["observacion"];
            // ...

            //se usan los setters correspondientes
            $obj_pago->set_fecha($fecha);
            $obj_pago->set_monto($monto);
            $obj_pago->set_tasa_dolar($tasa_dolar);
            $obj_pago->set_estado($estado);
            $obj_pago ->set_metodo_pago($metodo_pago);
            $obj_pago ->set_banco_id($banco_id);
            $obj_pago ->set_referencia($referencia);
            $obj_pago ->set_imagen($imagen);
            $obj_pago ->set_observacion($observacion);
            // ....
            
            //se ejecuta la funcion:
            echo  json_encode($obj_pago->editar_pago());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_pago = $_POST["id_pago"];

            //se usan el setter correspondientes
            $obj_pago->set_id_pago($id_pago);

            //se ejecuta la funcion:
            echo  json_encode($obj_pago->eliminar_pago());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($obj_pago->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "referencia"){
            $obj_pago->set_referencia($_POST["referencia"]);
            echo  json_encode($obj_pago->verificar_pago());
        }
        
        exit;
    }
    //FIN de AJAX
    require_once "vista/pagos/pagos_vista.php";
?>