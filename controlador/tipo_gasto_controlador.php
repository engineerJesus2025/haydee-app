<?php
    use haydee\ayuda\Sesiones;
    Sesiones::verificarSesion();
    Sesiones::verificarPermiso(GESTIONAR_TIPO_GASTO, CONSULTAR);

    use haydee\modelo\TipoGasto;

    $obj_tipo_gasto = new TipoGasto(); // Objeto tipo_gasto

    if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            $obj_tipo_gasto->registrar_bitacora(CONSULTAR, GESTIONAR_TIPO_GASTO, "TODOS LOS TIPOS DE GASTO");
            echo  json_encode($obj_tipo_gasto->realizar_consulta("consultar"));
            
        }

        elseif ($operacion == "registrar") {
            $nombre_tipo_gasto = $_POST["nombre_tipo_gasto"];

            $obj_tipo_gasto->set_nombre_tipo_gasto($nombre_tipo_gasto);

            $resultado = $obj_tipo_gasto->realizar_consulta("registrar");
            if ($resultado["estatus"]) {
                $obj_tipo_gasto->registrar_bitacora(REGISTRAR, GESTIONAR_TIPO_GASTO, $nombre_tipo_gasto);
            }
            echo  json_encode($resultado);
        }
        elseif ($operacion == "consulta_especifica"){
            $id_tipo_gasto = $_POST["id_tipo_gasto"];

            $obj_tipo_gasto->set_id_tipo_gasto($id_tipo_gasto);

            echo  json_encode($obj_tipo_gasto->realizar_consulta("consultar_tipo_gasto"));
            
        }

        elseif ($operacion == "modificar") {
            $id_tipo_gasto = $_POST["id_tipo_gasto"];
            $nombre_tipo_gasto = $_POST["nombre_tipo_gasto"];  

            $obj_tipo_gasto->set_id_tipo_gasto($id_tipo_gasto);
            $obj_tipo_gasto->set_nombre_tipo_gasto($nombre_tipo_gasto);

            $resultado = $obj_tipo_gasto->realizar_consulta("modificar");
            if ($resultado["estatus"]) {
                $obj_tipo_gasto->registrar_bitacora(MODIFICAR, GESTIONAR_TIPO_GASTO, $nombre_tipo_gasto);
            }
            echo  json_encode($resultado);
        }

        elseif ($operacion == "eliminar") {
            $id_tipo_gasto = $_POST["id_tipo_gasto"];

            $obj_tipo_gasto->set_id_tipo_gasto($id_tipo_gasto);
            $tipo_alterado = $obj_tipo_gasto->realizar_consulta("consultar_tipo_gasto");

            $resultado = $obj_tipo_gasto->realizar_consulta("eliminar");

            if ($resultado["estatus"]) {
                if($tipo_alterado){
                    $obj_tipo_gasto->registrar_bitacora(ELIMINAR, GESTIONAR_TIPO_GASTO, $tipo_alterado["nombre_tipo_gasto"]);
                }
            }
            echo  json_encode($resultado);
        }
        
        elseif ($operacion == "lastId"){
            echo json_encode($obj_tipo_gasto->realizar_consulta("lastId"));
        }

        exit;
    }

    require_once "vista/tipo_gasto/tipo_gasto_vista.php";
?>