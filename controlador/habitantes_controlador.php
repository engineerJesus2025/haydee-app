<?php
    use haydee\ayuda\Sesiones;
    Sesiones::verificarSesion();

    use haydee\modelo\Habitantes;
    use haydee\modelo\Apartamento;
    use haydee\modelo\HabitantesApartamentos;

    $obj_apartamento = new Apartamento(); // Objeto apartamento
    $obj_habitante = new Habitantes(); // Objeto habitante
    $obj_habitantes_apartamentos = new HabitantesApartamentos(); // Objeto habiantes_apartamentos
 
    $registro_apartamento = $obj_apartamento->consultar();

    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_habitante->consultar());
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
            $obj_habitante->set_nombre($nombre);
            $obj_habitante->set_apellido($apellido);
            $obj_habitante->set_cedula($cedula);
            $obj_habitante->set_telefono($telefono);
            $obj_habitante->set_correo($correo);
            $obj_habitante->set_fecha_nacimiento($fecha_nacimiento);
            $obj_habitante->set_sexo($sexo);
            
            $resultado_registro_habitante = $obj_habitante->registrar_habitante();
 
            // Tabla puente
            if($resultado_registro_habitante["estatus"]){
                $ultima_habitante = $obj_habitante->lastId();
                $habitante_id = $ultima_habitante["mensaje"];

                $obj_habitantes_apartamentos->set_habitante_id($habitante_id);
                $obj_habitantes_apartamentos->set_apartamento_id($apartamento_id);
                $obj_habitantes_apartamentos->set_tipo_vinculo($tipo_vinculo);

                $resultado_puente = $obj_habitantes_apartamentos->registrar_habitante_apartamento();

                if($resultado_puente["estatus"]){
                    echo json_encode([
                        "estatus" => true,
                        "mensaje" => "Habitante y relación registrados correctamente"
                    ]);
                }else{
                    echo json_encode([
                        "estatus" => false,
                        "mensaje" => "Error al registrar relación en tabla puente"
                    ]);
                }
            }else{
                echo json_encode($resultado_registro_habitante);
            }

            exit;
        }
        elseif ($operacion == "consulta_especifica"){
            //se guardan el id para buscar
            $id_habitante = $_POST["id_habitante"];
            //var_dump("ID recibido en el controlador:", $id_habitante);

            //se usan el setter correspondientes
            $obj_habitante->set_id_habitante($id_habitante);

            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_habitante->consultar_habitante());
            // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }

        elseif ($operacion == "modificar") {
            //se guardan las variables a modificar
            $id_habitante = $_POST["id_habitante"];
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
            $obj_habitante->set_id_habitante($id_habitante);
            $obj_habitante->set_nombre($nombre);
            $obj_habitante->set_apellido($apellido);
            $obj_habitante->set_cedula($cedula);
            $obj_habitante->set_telefono($telefono);
            $obj_habitante->set_correo($correo);
            $obj_habitante->set_fecha_nacimiento($fecha_nacimiento);
            $obj_habitante->set_sexo($sexo);
            // ....

            $obj_habitantes_apartamentos->set_apartamento_id($apartamento_id);
            $obj_habitantes_apartamentos->set_tipo_vinculo($tipo_vinculo);
            $obj_habitantes_apartamentos->set_habitante_id($id_habitante);

            //se ejecuta la funcion:
            $registro_modificado = $obj_habitante->editar_habitante();
            
            if($registro_modificado["estatus"]){
                
                $resultado_puente = $obj_habitantes_apartamentos->editar_habitante_apartamento();

                if ($resultado_puente["estatus"]) {
                    echo json_encode([
                        "estatus" => true,
                        "mensaje" => "Habitante y relación actualizados correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "estatus" => false,
                        "mensaje" => "Habitante actualizado, pero error al actualizar relación en tabla puente"
                    ]);
                }
            } else {
                echo json_encode([
                    "estatus" => false,
                    "mensaje" => "Error al actualizar al habitante"
                ]);
            }
        }

        elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_habitante = $_POST["id_habitante"];

            //se usan el setter correspondientes
            $obj_habitante->set_id_habitante($id_habitante);

            //se ejecuta la funcion:
            echo  json_encode($obj_habitante->eliminar_habitante());
            //igual puse para que siempre retorne un arreglo que dara true o false de acuerdo al resultado
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($obj_habitante->lastId());
        }

        exit;//es salida en ingles... No puede faltar
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "cedula"){
            $obj_habitante->set_cedula($_POST["cedula"]);
            echo  json_encode($obj_habitante->verificar_habitante());
        }
        
        exit;
    }
    //FIN de AJAX
    require_once "vista/habitantes/habitantes_vista.php";
?>