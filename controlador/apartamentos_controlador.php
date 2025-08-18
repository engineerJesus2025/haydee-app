<?php
    require_once("modelo/apartamentos_modelo.php");
    require_once("modelo/habitantes_modelo.php");
    require_once("modelo/habitantes_apartamentos_modelo.php");

    $obj_apartamento = new Apartamento(); // Objeto Apartamento
    $obj_habitante = new Habitantes(); // Objeto Habitante
    $obj_habitantes_apartamentos = new Habitantes_apartamentos(); // Objeto Habitantes_Apartamentos

    $registro_apartamento = $obj_apartamento->consultar();
 
    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){

            echo  json_encode($obj_apartamento->consultar());

        }elseif ($operacion == "consultar_habitantes") {
            $obj_habitantes_apartamentos->set_apartamento_id($_POST["id_apartamento"]);
            echo json_encode($obj_habitantes_apartamentos->consultar_habitantes_por_apartamento());
            exit;
        }elseif ($operacion == "registrar_habitantes"){
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
                echo json_encode([
                    "estatus" => false,
                    "mensaje" => "Error al registrar habitante"
                ]);
            }

            exit;
        }elseif ($operacion == "consulta_especifica_habitante"){

            $id_habitante = $_POST["id_habitante"];
            $obj_habitante->set_id_habitante($id_habitante);
            echo  json_encode($obj_habitante->consultar_habitante());

        }elseif ($operacion == "modificar_habitantes"){
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
            
            $obj_habitante->set_id_habitante($id_habitante);
            $obj_habitante->set_nombre($nombre);
            $obj_habitante->set_apellido($apellido);
            $obj_habitante->set_cedula($cedula);
            $obj_habitante->set_telefono($telefono);
            $obj_habitante->set_correo($correo);
            $obj_habitante->set_fecha_nacimiento($fecha_nacimiento);
            $obj_habitante->set_sexo($sexo);

            $obj_habitantes_apartamentos->set_apartamento_id($apartamento_id);
            $obj_habitantes_apartamentos->set_tipo_vinculo($tipo_vinculo);
            $obj_habitantes_apartamentos->set_habitante_id($id_habitante);

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
        }elseif ($operacion == "eliminar_habitantes"){

            $id_habitante = $_POST["id_habitante"];
            $obj_habitante->set_id_habitante($id_habitante);
            echo  json_encode($obj_habitante->eliminar_habitante());

        }elseif ($operacion == "ultimo_id_habitante") {

            echo json_encode($obj_habitante->lastId());

        }elseif ($operacion == "registrar") {
            $nro_apartamento = $_POST["nro_apartamento"];  
            $porcentaje_participacion = $_POST["porcentaje_participacion"];
            $gas = $_POST["gas"]; 
            $agua = $_POST["agua"];
            $alquilado = $_POST["alquilado"];

            $obj_apartamento->set_nro_apartamento($nro_apartamento);
            $obj_apartamento->set_porcentaje_participacion($porcentaje_participacion);
            $obj_apartamento->set_gas($gas);
            $obj_apartamento->set_agua($agua);
            $obj_apartamento->set_alquilado($alquilado);

            echo  json_encode($obj_apartamento->registrar_apartamento());
        }elseif ($operacion == "consulta_especifica"){
            $id_apartamento = $_POST["id_apartamento"];

            $obj_apartamento->set_id_apartamento($id_apartamento);

            $datos_apartamento = $obj_apartamento->consultar_apartamento();
            $detalles = $obj_apartamento->consultar_detalles();

            $respuesta = [
                "apartamento" => $datos_apartamento,
                "detalles" => $detalles
            ];

            echo json_encode($respuesta);
        }elseif ($operacion == "modificar") {
            $id_apartamento = $_POST["id_apartamento"];
            $nro_apartamento = $_POST["nro_apartamento"];  
            $porcentaje_participacion = $_POST["porcentaje_participacion"];
            $gas = $_POST["gas"]; 
            $agua = $_POST["agua"];
            $alquilado = $_POST["alquilado"];

            $obj_apartamento->set_id_apartamento($id_apartamento);
            $obj_apartamento->set_nro_apartamento($nro_apartamento);
            $obj_apartamento->set_porcentaje_participacion($porcentaje_participacion);
            $obj_apartamento->set_gas($gas);
            $obj_apartamento->set_agua($agua);
            $obj_apartamento->set_alquilado($alquilado);
            
            echo  json_encode($obj_apartamento->editar_apartamento());
        }elseif ($operacion == "eliminar") {
            $id_apartamento = $_POST["id_apartamento"];

            $obj_apartamento->set_id_apartamento($id_apartamento);

            echo  json_encode($obj_apartamento->eliminar_apartamento());
        }elseif ($operacion == "ultimo_id"){
            echo json_encode($obj_apartamento->lastId());
        }

        exit;
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"];
        if ($validar == "nro_apartamento"){
            $obj_apartamento->set_nro_apartamento($_POST["nro_apartamento"]);
            echo  json_encode($obj_apartamento->verificar_apartamento());
        }

        if ($validar == "cedula"){
            $obj_habitante->set_cedula($_POST["cedula"]);
            echo  json_encode($obj_habitante->verificar_habitante());
        }
        
        exit;
    }

    require_once "vista/apartamentos/apartamentos_vista.php";
?>