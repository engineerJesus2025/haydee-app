<?php
    require_once("modelo/personas_modelo.php");
    require_once("modelo/apartamentos_modelo.php");
    require_once("modelo/personas_apartamentos.php");

    $obj_apartamento = new Apartamento(); // Objeto apartamento
    $obj_persona = new Personas(); // Objeto persona
    $obj_personas_apartamentos = new Personas_apartamentos(); // Objeto persona_apartamentos
 
    $registro_apartamento = $obj_apartamento->consultar();

    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_persona->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json

        elseif ($operacion == "registrar") {
            //se guardan las variables a registrar
            $nombre = $_POST["nombre"];  
            $apellido = $_POST["apellido"]; 
            $cedula = $_POST["cedula"];
            $telefono = $_POST["telefono"];
            $correo = $_POST["correo"];
            $fecha_nacimiento = $_POST["fecha_nacimiento"];
            $sexo = $_POST["sexo"];     

            $apartamento_id = $_POST["apartamento_id"];
            $tipo_vinculo = $_POST["tipo_vinculo"];  

            //se usan los setters correspondientes
            $obj_persona->set_nombre($nombre);
            $obj_persona->set_apellido($apellido);
            $obj_persona->set_cedula($cedula);
            $obj_persona->set_telefono($telefono);
            $obj_persona->set_correo($correo);
            $obj_persona->set_fecha_nacimiento($fecha_nacimiento);
            $obj_persona->set_sexo($sexo);
            
            $resultado_registro_persona = $obj_persona->registrar_persona();
 
            // Tabla puente
            if($resultado_registro_persona["estatus"]){
                $ultima_persona = $obj_persona->lastId();
                $persona_id = $ultima_persona["mensaje"];

                $obj_personas_apartamentos->set_persona_id($persona_id);
                $obj_personas_apartamentos->set_apartamento_id($apartamento_id);
                $obj_personas_apartamentos->set_tipo_vinculo($tipo_vinculo);

                $resultado_puente = $obj_personas_apartamentos->registrar_persona_apartamento();

                if($resultado_puente["estatus"]){
                    echo json_encode([
                        "estatus" => true,
                        "mensaje" => "Persona y relación registrados correctamente"
                    ]);
                }else{
                    echo json_encode([
                        "estatus" => false,
                        "mensaje" => "Error al registrar relación en tabla puente"
                    ]);
                }
            }else{
                echo json_encode([
                    "estatus" => false,
                    "mensaje" => "Error al registrar persona"
                ]);
            }

            exit;
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_persona = $_POST["id_persona"];
            //var_dump("ID recibido en el controlador:", $id_persona);

            //se usan el setter correspondientes
            $obj_persona->set_id_persona($id_persona);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_persona->consultar_persona());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_persona = $_POST["id_persona"];
            $nombre = $_POST["nombre"];  
            $apellido = $_POST["apellido"]; 
            $cedula = $_POST["cedula"];
            $telefono = $_POST["telefono"];
            $correo = $_POST["correo"];
            $fecha_nacimiento = $_POST["fecha_nacimiento"];
            $sexo = $_POST["sexo"];
            $apartamento_id = $_POST["apartamento_id"];
            $tipo_vinculo = $_POST["tipo_vinculo"];
            // ...

            //se usan los setters correspondientes
            $obj_persona->set_id_persona($id_persona);
            $obj_persona->set_nombre($nombre);
            $obj_persona->set_apellido($apellido);
            $obj_persona->set_cedula($cedula);
            $obj_persona->set_telefono($telefono);
            $obj_persona->set_correo($correo);
            $obj_persona->set_fecha_nacimiento($fecha_nacimiento);
            $obj_persona->set_sexo($sexo);
            // ....

            $obj_personas_apartamentos->set_apartamento_id($apartamento_id);
            $obj_personas_apartamentos->set_tipo_vinculo($tipo_vinculo);
            $obj_personas_apartamentos->set_persona_id($id_persona);

            //se ejecuta la funcion:
            $registro_modificado = $obj_persona->editar_persona();
            
            if($registro_modificado["estatus"]){
                
                $resultado_puente = $obj_personas_apartamentos->editar_persona_apartamento();

                if ($resultado_puente["estatus"]) {
                    echo json_encode([
                        "estatus" => true,
                        "mensaje" => "Persona y relación actualizados correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "estatus" => false,
                        "mensaje" => "Persona actualizado, pero error al actualizar relación en tabla puente"
                    ]);
                }
            } else {
                echo json_encode([
                    "estatus" => false,
                    "mensaje" => "Error al actualizar a la persona"
                ]);
            }
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_persona = $_POST["id_persona"];

            //se usan el setter correspondientes
            $obj_persona->set_id_persona($id_persona);

            //se ejecuta la funcion:
            echo  json_encode($obj_persona->eliminar_persona());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($obj_persona->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "cedula"){
            $obj_persona->set_cedula($_POST["cedula"]);
            echo  json_encode($obj_persona->verificar_persona());
        }
        
        exit;
    }
    //FIN de AJAX
    require_once "vista/personas/personas_vista.php";
?>