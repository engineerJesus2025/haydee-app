<?php
    use haydee\ayuda\Sesiones;
    Sesiones::verificarSesion();

    use haydee\modelo\Apartamento;
    use haydee\modelo\TipoGasto;
    use haydee\modelo\Mensualidad;
    use haydee\modelo\Presupuesto;
    use haydee\modelo\DetallesPresupuesto;
    use haydee\modelo\PresupuestoMensualidad;

    if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta"){
            $presupuesto_obj = new Presupuesto();

            $presupuesto_obj->registrar_bitacora(CONSULTAR, GESTIONAR_PRESUPUESTO, "TODOS LOS PRESUPUESTOS");

            echo  json_encode($presupuesto_obj->realizar_consulta('consultar'));
        }
        else if($operacion == "consultar_meses_faltantes"){
            $presupuesto_obj = new Presupuesto();
            echo  json_encode($presupuesto_obj->realizar_consulta('consultar_meses_faltantes'));
        }
        else if($operacion == "consultar_tipo_gastos"){
            $tipo_gasto_obj = new TipoGasto();
            echo  json_encode($tipo_gasto_obj->realizar_consulta('consultar'));
        }
        else if ($operacion == "consultar_apartamentos"){
            $obj_apartamento = new Apartamento();
            echo  json_encode($obj_apartamento->realizar_consulta('consultar'));
        }
        elseif ($operacion == "registrar") {
            $presupuesto_obj = new Presupuesto();
            
            $fecha = $_POST["fecha"];
            $cuota_reserva = $_POST["cuota_reserva"];
            $observacion = $_POST["observacion"];

            $presupuesto_obj->set_fecha($fecha);
            $presupuesto_obj->set_cuota_reserva($cuota_reserva);
            $presupuesto_obj->set_observacion($observacion);

            $resultado = $presupuesto_obj->realizar_consulta("registrar");

            if ($resultado["estatus"]) {
                $presupuesto_obj->registrar_bitacora(REGISTRAR, GESTIONAR_PRESUPUESTO, "Presupuesto del " . $fecha);
            }
            
            echo  json_encode($resultado);
        }
        elseif ($operacion == "registrar_detalles_presupuestos"){
            $detalles_presupuesto_obj = new DetallesPresupuesto();

            $nombres_detalles_presupuestos = explode(",", $_POST["nombres_detalles_presupuestos"]);
            $montos_detalles_presupuestos = explode(",", $_POST["montos_detalles_presupuestos"]);
            $presupuesto_id = $_POST["presupuesto_id"];
            $tipo_gasto_id = $_POST["tipo_gasto_id"];
            
            $detalles_presupuesto_obj->set_presupuesto_id($presupuesto_id);
            $detalles_presupuesto_obj->set_tipo_gasto_id($tipo_gasto_id);            

            foreach ($nombres_detalles_presupuestos as $indice => $nombre) {
                $detalles_presupuesto_obj->set_nombre_detalle($nombre);
                $detalles_presupuesto_obj->set_monto_detalle($montos_detalles_presupuestos[$indice]);

                $resultado = $detalles_presupuesto_obj->realizar_consulta('registrar');
                
                if (!$resultado["estatus"]) {
                    echo json_encode($resultado);
                    exit;
                }
            }
            echo json_encode($resultado);
        }
        elseif ($operacion == "registrar_mensualidad"){
            $mensualidad_obj = new Mensualidad();

            $monto = $_POST["monto"];
            $tasa_dolar = $_POST["tasa_dolar"];
            $mes = $_POST["mes"];
            $anio = $_POST["anio"];
            $apartamento_id = $_POST["apartamento_id"];

            $mensualidad_obj->set_monto($monto);
            $mensualidad_obj->set_tasa_dolar($tasa_dolar);
            $mensualidad_obj->set_mes($mes);
            $mensualidad_obj->set_anio($anio);
            $mensualidad_obj->set_apartamento_id($apartamento_id);
            $mensualidad_obj->set_porcentaje_interes(10);
            $mensualidad_obj->set_limite_mensualidad(15);

            echo  json_encode($mensualidad_obj->realizar_consulta('registrar'));
        }
        elseif ($operacion == "registrar_presupuesto_mensualidad"){
            $presupuesto_mensualidad_obj = new PresupuestoMensualidad();

            $mensualidad_id = $_POST["mensualidad_id"];
            $presupuesto_id = $_POST["presupuesto_id"];

            $presupuesto_mensualidad_obj->set_mensualidad_id($mensualidad_id);
            $presupuesto_mensualidad_obj->set_presupuesto_id($presupuesto_id);

            echo  json_encode($presupuesto_mensualidad_obj->realizar_consulta('registrar'));
        }
        elseif ($operacion == "registrar_presupuesto_mensualidad"){
            $presupuesto_mensualidad_obj = new PresupuestoMensualidad();

            $mensualidad_id = $_POST["mensualidad_id"];
            $presupuesto_id = $_POST["presupuesto_id"];

            $presupuesto_mensualidad_obj->set_mensualidad_id($mensualidad_id);
            $presupuesto_mensualidad_obj->set_presupuesto_id($presupuesto_id);

            echo  json_encode($presupuesto_mensualidad_obj->realizar_consulta('registrar'));
        }

        elseif ($operacion == "consulta_especifica"){
            $presupuesto_obj = new Presupuesto();
            $id_presupuesto = $_POST["id_presupuesto"];
            $presupuesto_obj->set_id_presupuesto($id_presupuesto);
            echo  json_encode($presupuesto_obj->realizar_consulta('consultar_presupuesto'));
        }
        elseif ($operacion == "consultar_detalles_presupuestos"){
            $detalles_presupuesto_obj = new DetallesPresupuesto();

            $id_presupuesto = $_POST["id_presupuesto"];

            $detalles_presupuesto_obj->set_presupuesto_id($id_presupuesto);
            echo  json_encode($detalles_presupuesto_obj->realizar_consulta('consultar_detalles_presupuestos'));
        }
        elseif ($operacion == "editar_presupuesto") {
            $presupuesto_obj = new Presupuesto();
            $id_presupuesto = $_POST["id_presupuesto"];
            $fecha = $_POST["fecha"];
            $cuota_reserva = $_POST["cuota_reserva"];
            $observacion = $_POST["observacion"];

            $presupuesto_obj->set_fecha($fecha);
            $presupuesto_obj->set_cuota_reserva($cuota_reserva);
            $presupuesto_obj->set_observacion($observacion);
            $presupuesto_obj->set_id_presupuesto($id_presupuesto);

            $resultado = $presupuesto_obj->realizar_consulta("editar");

            if ($resultado["estatus"]) {
                $presupuesto_obj->registrar_bitacora(MODIFICAR, GESTIONAR_PRESUPUESTO, "Presupuesto del " . $fecha);
            }
            
            echo  json_encode($resultado);
        }
        else if ($operacion == "eliminar_detalles_presupuestos") {
            $detalles_presupuesto_obj = new DetallesPresupuesto();

            $presupuesto_id = $_POST["presupuesto_id"];

            $detalles_presupuesto_obj->set_presupuesto_id($presupuesto_id);
            
            $resultado = $detalles_presupuesto_obj->realizar_consulta('eliminar');

            if (!$resultado["estatus"]) {
                echo json_encode($resultado);
                exit();
            }
            echo json_encode($resultado);
        }
        elseif ($operacion == "eliminar") {
            $presupuesto_obj = new Presupuesto();

            $id_presupuesto = $_POST["id_presupuesto"];
            $presupuesto_obj->set_id_presupuesto($id_presupuesto);
            
            $presupuesto_eliminado = $presupuesto_obj->realizar_consulta('consultar_presupuesto');

            $resultado = $presupuesto_obj->realizar_consulta("eliminar");

            if ($resultado["estatus"]){
                if ($presupuesto_eliminado) {
                    $presupuesto_obj->registrar_bitacora(ELIMINAR, GESTIONAR_PRESUPUESTO, "Presupuesto del " . $presupuesto_eliminado["fecha"]);
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta para la bitácora"];
                }
            }            

            echo  json_encode($resultado);
        }
        elseif ($operacion == "ultimo_id"){
            $presupuesto_obj = new Presupuesto();
            echo json_encode($presupuesto_obj->realizar_consulta('lastId'));
        }
        exit;
    }      
    require_once "vista/presupuesto_mensual/presupuesto_vista.php";    
?>