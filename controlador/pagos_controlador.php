<?php
    require_once("modelo/pagos_modelo.php");
    require_once("modelo/banco_modelo.php");

    $obj_pago = new Pagos(); // Objeto pago
    $obj_banco = new Banco(); // Objeto banco
    
    $meses = [
        "01" => "Enero",
        "02" => "Febrero",
        "03" => "Marzo",
        "04" => "Abril",
        "05" => "Mayo",
        "06" => "Junio",
        "07" => "Julio",
        "08" => "Agosto",
        "09" => "Septiembre",
        "10" => "Octubre",
        "11" => "Noviembre",
        "12" => "Diciembre",
    ];

    $registro_mensualidad = $obj_pago->consultarMensualidad();
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
            $imagen = '';
            $observacion = $_POST["observacion"];

            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                $nombre_original = $_FILES['imagen']['name'];
                $temporal = $_FILES['imagen']['tmp_name'];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

                // ESTO ES PARA QUE SE GUARDE LA IMAGEN CON EL NOMBRE ORIGINAL + UNOS NUMEROS RANDOM PARA EVITAR DUPLICACION
                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;

                // AQUI ES DONDE SE VA A GUARDAR LA IMAGEN FISICAMENTE
                $ruta_destino = "recursos/img/";
                // ESTO NUNCA VA A PASAR!! PERO POR SI ACASO (BASICAMENTE SI NO EXISTE LA RUTA, SE CREA)
                if (!is_dir($ruta_destino)) {
                    mkdir($ruta_destino, 0777, true);
                }
                // AQUI SE CAMBIA LA RUTA DE LA IMAGEN, DE UNA IMAGEN TEMPORAL A LA RUTA DE DESTINO FISICA
                move_uploaded_file($temporal, $ruta_destino . $imagen);
            }

            $mensualidad_id = $_POST["mensualidad_id"];
            $resultado = $obj_pago->lastIdPagoMensualidad();
            $pago_id = $resultado["mensaje"];

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
            $obj_pago->set_mensualidad_id($mensualidad_id);
            $obj_pago->set_pago_id($pago_id);

            $obj_pago->registrar_pagos_mensualidad();

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
            $id_pago = $_POST["id_pago"];
            $fecha = $_POST["fecha"];  
            $monto = $_POST["monto"];  
            $tasa_dolar = $_POST["tasa_dolar"]; 
            $estado = $_POST["estado"];
            $metodo_pago = $_POST["metodo_pago"];
            $banco_id = $_POST["banco_id"];  
            $referencia = $_POST["referencia"];
            $observacion = $_POST["observacion"];
            // ...

            $eliminar_imagen = isset($_POST["eliminar_imagen"]);

            $imagen = $obj_pago->obtener_imagen_actual();
            if (!empty($imagen)) {
                $ruta_imagen = "recursos/img/" . $imagen;

                // Eliminar imagen si el usuario lo pidió y el archivo existe
                if ($eliminar_imagen && file_exists($ruta_imagen) && is_file($ruta_imagen)) {
                    unlink($ruta_imagen);
                    $imagen = '';
                }
            } else {
                $ruta_imagen = ''; // No hay imagen previa
            }

            //  Reemplazo de imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                // Si hay imagen previa, se elimina
                if (!empty($imagen) && file_exists("recursos/img/" . $imagen) && is_file("recursos/img/" . $imagen)) {
                    unlink("recursos/img/" . $imagen);
                }
                
                // LO MISMO QUE EN REGISTRAR
                $nombre_original = $_FILES['imagen']['name'];
                $temporal = $_FILES['imagen']['tmp_name'];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;

                move_uploaded_file($temporal, "recursos/img/" . $imagen);
            }

            //se usan los setters correspondientes
            $obj_pago->set_id_pago($id_pago);
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
            $obj_pago->set_id_pago($id_pago);

            //  Consultar la imagen antes de eliminar
            $datos = $obj_pago->consultar_pago(); // debe retornar imagen

            if ($datos && isset($datos[0]["imagen"]) && $datos[0]["imagen"] !== "") {
                $nombre_imagen = $datos[0]["imagen"];
                $ruta = "recursos/img/" . $nombre_imagen;

                // Eliminar imagen físicamente 
                if (file_exists($ruta) && is_file($ruta)) {
                    unlink($ruta);
                }
            }

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