<?php    
    use haydee\ayuda\Sesiones;
    Sesiones::verificarSesion();

    use haydee\modelo\AnioFiscal;

    if (isset($_POST["operacion"])){        
        $operacion = $_POST["operacion"];

        if ($operacion == "consultar_anios_fiscales"){
            $anio_fiscal_obj = new AnioFiscal();
            $anio_fiscal_obj->registrar_bitacora(CONSULTAR, GESTIONAR_ANIO_FISCAL, "TODOS LOS AÑOS FISCALES");

            echo  json_encode($anio_fiscal_obj->realizar_consulta("consultar"));
        }

        elseif ($operacion == "registrar") {
            $anio_fiscal_obj = new AnioFiscal();

            $fecha_inicio = $_POST["fecha_inicio"];
            $fecha_cierre = $_POST["fecha_cierre"];
            $estado = $_POST["estado"];
            $descripcion = $_POST["descripcion"];

            $anio_fiscal_obj->set_fecha_inicio($fecha_inicio);
            $anio_fiscal_obj->set_fecha_cierre($fecha_cierre);
            $anio_fiscal_obj->set_estado($estado);
            $anio_fiscal_obj->set_descripcion($descripcion);

            $resultado = $anio_fiscal_obj->realizar_consulta("registrar");

            if ($resultado["estatus"]) {
                $anio_fiscal_obj->registrar_bitacora(REGISTRAR, GESTIONAR_ANIO_FISCAL, $fecha_inicio . " - " . $estado);
            }
            
            echo  json_encode($resultado);
        }
        elseif ($operacion == "consulta_especifica"){
            $anio_fiscal_obj = new AnioFiscal();

            $id_anio_fiscal = $_POST["id_anio_fiscal"];

            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);

            echo  json_encode($anio_fiscal_obj->realizar_consulta("consultar_anio_fiscal"));
        }

        elseif ($operacion == "modificar") {
            $anio_fiscal_obj = new AnioFiscal();

            $id_anio_fiscal = $_POST["id_anio_fiscal"];
            $fecha_inicio = $_POST["fecha_inicio"];
            $fecha_cierre = $_POST["fecha_cierre"];
            $estado = $_POST["estado"];
            $descripcion = $_POST["descripcion"];
            
            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);
            $anio_fiscal_obj->set_fecha_inicio($fecha_inicio);
            $anio_fiscal_obj->set_fecha_cierre($fecha_cierre);
            $anio_fiscal_obj->set_estado($estado);
            $anio_fiscal_obj->set_descripcion($descripcion);

            $resultado = $anio_fiscal_obj->realizar_consulta("editar");
            
            if ($resultado["estatus"]) {
                $anio_fiscal_obj->registrar_bitacora(MODIFICAR, GESTIONAR_ANIO_FISCAL, $fecha_inicio . " - " . $estado);
            }
            
            echo  json_encode($resultado);
        }

        elseif ($operacion == "eliminar") {
            $anio_fiscal_obj = new AnioFiscal();
            
            $id_anio_fiscal = $_POST["id_anio_fiscal"];
            
            $anio_fiscal_obj->set_id_anio_fiscal($id_anio_fiscal);

            $anio_alterado = $anio_fiscal_obj->realizar_consulta('consultar_anio_fiscal');

            $resultado = $anio_fiscal_obj->realizar_consulta("eliminar");

            if ($resultado["estatus"]){
                if ($anio_alterado) {
                    $anio_fiscal_obj->registrar_bitacora(ELIMINAR, GESTIONAR_ANIO_FISCAL, $anio_alterado["fecha_inicio"] . " - " . $anio_alterado["estado"]);
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta para la bitácora"];
                }
            }            

            echo  json_encode($resultado);
        }

        exit;
    }
    require_once "vista/anio_fiscal/anio_fiscal_vista.php";

?>