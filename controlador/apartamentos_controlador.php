<?php
    use haydee\ayuda\Sesiones;
    Sesiones::verificarSesion();

    use haydee\modelo\Apartamento;
    use haydee\modelo\Habitantes;
    use haydee\modelo\HabitantesApartamentos;
 
    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            $obj_apartamento = new Apartamento(); // Objeto Apartamento
            echo  json_encode($obj_apartamento->realizar_consulta('consultar'));

        }elseif ($operacion == "consultar_habitantes") {
            $obj_habitantes_apartamentos = new HabitantesApartamentos(); // Objeto Habitantes_Apartamentos
            $obj_habitantes_apartamentos->set_apartamento_id($_POST["id_apartamento"]);
            echo json_encode($obj_habitantes_apartamentos->realizar_consulta('consultar'));
            exit;
        }elseif ($operacion == "registrar_habitantes"){
            $obj_habitante = new Habitantes(); // Objeto Habitante
            $obj_habitantes_apartamentos = new HabitantesApartamentos(); // Objeto Habitantes_Apartamentos

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
            
            $resultado_registro_habitante = $obj_habitante->realizar_consulta('registrar');
 
            // Tabla puente
            if($resultado_registro_habitante["estatus"]){
                $ultima_habitante = $obj_habitante->realizar_consulta('lastId');
                $habitante_id = $ultima_habitante["last_id"];

                $obj_habitantes_apartamentos->set_habitante_id($habitante_id);
                $obj_habitantes_apartamentos->set_apartamento_id($apartamento_id);
                $obj_habitantes_apartamentos->set_tipo_vinculo($tipo_vinculo);

                $resultado_puente = $obj_habitantes_apartamentos->realizar_consulta('registrar');

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

            $obj_habitante = new Habitantes(); // Objeto Habitante
            $id_habitante = $_POST["id_habitante"];
            $obj_habitante->set_id_habitante($id_habitante);
            echo  json_encode($obj_habitante->realizar_consulta('consulta_especifica'));

        }elseif ($operacion == "modificar_habitantes"){
            $obj_habitante = new Habitantes(); // Objeto Habitante
            $obj_habitantes_apartamentos = new HabitantesApartamentos(); // Objeto Habitantes_Apartamentos

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

            $registro_modificado = $obj_habitante->realizar_consulta('modificar');
            
            if($registro_modificado["estatus"]){
                
                $resultado_puente = $obj_habitantes_apartamentos->realizar_consulta('modificar');

                if ($resultado_puente["estatus"]) {
                    echo json_encode([
                        "estatus" => true,
                        "mensaje" => "Habitante y relación actualizados correctamente",
                        'err'=>$resultado_puente["mensaje"]
                    ]);
                } else {
                    echo json_encode([
                        "estatus" => false,
                        "mensaje" => "Habitante actualizado, pero error al actualizar relación en tabla puente",
                        'err'=>$resultado_puente["mensaje"]
                    ]);
                }
            } else {
                echo json_encode([
                    "estatus" => false,
                    "mensaje" => "Error al actualizar al habitante",
                    'err'=>$registro_modificado["mensaje"]
                ]);
            }
        }elseif ($operacion == "eliminar_habitantes"){
            
            $obj_habitante = new Habitantes(); // Objeto Habitante
            $id_habitante = $_POST["id_habitante"];
            $obj_habitante->set_id_habitante($id_habitante);
            echo  json_encode($obj_habitante->realizar_consulta('eliminar'));

        }elseif ($operacion == "ultimo_id_habitante") {

            $obj_habitante = new Habitantes(); // Objeto Habitante
            echo json_encode($obj_habitante->realizar_consulta('lastId'));

        }elseif ($operacion == "registrar") {
            $obj_apartamento = new Apartamento(); // Objeto Apartamento

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

            echo  json_encode($obj_apartamento->realizar_consulta('registrar'));
        }elseif ($operacion == "consulta_especifica"){
            $obj_apartamento = new Apartamento();
            $id_apartamento = $_POST["id_apartamento"];

            $obj_apartamento->set_id_apartamento($id_apartamento);

            $datos_apartamento = $obj_apartamento->realizar_consulta('consulta_especifica');
            $detalles = $obj_apartamento->consultar_detalles();

            $respuesta = [
                "apartamento" => $datos_apartamento,
                "detalles" => $detalles
            ];

            echo json_encode($respuesta);
        }elseif ($operacion == "modificar") {
            $obj_apartamento = new Apartamento(); // Objeto Apartamento

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
            
            echo  json_encode($obj_apartamento->realizar_consulta('modificar'));
        }elseif ($operacion == "eliminar") {
            $obj_apartamento = new Apartamento(); // Objeto Apartamento
            $id_apartamento = $_POST["id_apartamento"];

            $obj_apartamento->set_id_apartamento($id_apartamento);

            echo  json_encode($obj_apartamento->realizar_consulta('eliminar'));
        }elseif ($operacion == "ultimo_id"){
            $obj_apartamento = new Apartamento(); // Objeto Apartamento
            echo json_encode($obj_apartamento->realizar_consulta('lastId'));
        }

        exit;
    }
 
    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"];
        if ($validar == "nro_apartamento"){
            $obj_apartamento = new Apartamento(); // Objeto Apartamento
            $obj_apartamento->set_nro_apartamento($_POST["nro_apartamento"]);
            echo  json_encode($obj_apartamento->realizar_consulta('validar'));

        }elseif ($validar == "cedula"){
            $obj_habitante = new Habitantes(); // Objeto Habitante
            $obj_habitante->set_cedula($_POST["cedula"]);
            echo  json_encode($obj_habitante->realizar_consulta('validar'));

        }elseif ($validar == "tipo_vinculo"){
            $obj_habitantes_apartamentos = new HabitantesApartamentos(); // Objeto Habitantes_Apartamentos
            $obj_habitantes_apartamentos->set_tipo_vinculo($_POST["tipo_vinculo"]);
            $obj_habitantes_apartamentos->set_apartamento_id($_POST["apartamento_id"]);
            echo  json_encode($obj_habitantes_apartamentos->realizar_consulta('validar'));
        }        
        elseif ($validar == "validar_clave_foranea") {  
            $obj_habitante = new Habitantes(); // Objeto Habitante      
            $tabla = $_POST["tabla"];
            $nombre_clave = $_POST["nombre_clave"];
            $valor = $_POST["valor"];
            
            $resultado = $obj_habitante->realizar_consulta('validar_clave_foranea',["tabla"=>$tabla,"nombre_clave"=>$nombre_clave,"valor"=>$valor]);
            
            echo json_encode($resultado);
        }
        
        exit;
    }

    require_once "vista/apartamentos/apartamentos_vista.php";
?>