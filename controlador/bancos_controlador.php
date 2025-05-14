<?php
    require_once("modelo/banco_modelo.php");
    require_once("modelo/usuario_modelo.php");

    $obj_banco = new Usuario();
    $obj_banco = new Banco(); // Objeto banco
 
    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_banco->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $id_banco = $_POST["id_banco"];
            $nombre_banco = $_POST["nombre_banco"];  
            $codigo = $_POST["codigo"];  
            $numero_cuenta = $_POST["numero_cuenta"]; 
            $telefono_afiliado = $_POST["telefono_afiliado"];
            $cedula_afiliada = $_POST["cedula_afiliada"];              

            //se usan los setters correspondientes
            $obj_banco->set_id_banco($id_banco);
            $obj_banco->set_nombre_banco($nombre_banco);
            $obj_banco->set_codigo($codigo);
            $obj_banco->set_numero_cuenta($numero_cuenta);
            $obj_banco->set_telefono_afiliado($telefono_afiliado);
            $obj_banco->set_cedula_afiliada($cedula_afiliada);

            //se ejecuta la funcion:
            echo  json_encode($obj_banco->registrar_banco());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_banco = $_POST["id_banco"];

            //se usan el setter correspondientes
            $obj_banco->set_id_banco($id_banco);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_banco->consultar_banco());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_banco = $_POST["id_banco"];
            $nombre_banco = $_POST["nombre_banco"];  
            $codigo = $_POST["codigo"];  
            $numero_cuenta = $_POST["numero_cuenta"]; 
            $telefono_afiliado = $_POST["telefono_afiliado"];
            $cedula_afiliada = $_POST["cedula_afiliada"];
            // ...

            //se usan los setters correspondientes
            $obj_banco->set_id_banco($id_banco);
            $obj_banco->set_nombre_banco($nombre_banco);
            $obj_banco->set_codigo($codigo);
            $obj_banco->set_numero_cuenta($numero_cuenta);
            $obj_banco->set_telefono_afiliado($telefono_afiliado);
            $obj_banco->set_cedula_afiliada($cedula_afiliada);
            // ....
            
            //se ejecuta la funcion:
            echo  json_encode($obj_banco->editar_banco());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_banco = $_POST["id_banco"];

            //se usan el setter correspondientes
            $obj_banco->set_id_banco($id_banco);

            //se ejecuta la funcion:
            echo  json_encode($obj_banco->eliminar_banco());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($obj_banco->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "correo"){
            $usuario_obj->set_correo($_POST["correo"]);
            echo  json_encode($usuario_obj->verificar_correo());
        }
        if ($validar == "contra"){
            $contra = $_POST["contra"];

            $usuario_obj->set_id_usuario($_POST["id_usuario"]);

            $usuario_validar = $usuario_obj->consultar_usuario();

            echo json_encode(password_verify($contra, $usuario_validar["contrasenia"]));
        }
        exit;
    }
    //FIN de AJAX
    require_once "vista/bancos/bancos_vista.php";
?>