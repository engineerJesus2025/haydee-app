<?php 
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_CAJA_CHICA, CONSULTAR);

use haydee\modelo\Gastos;
use haydee\modelo\CajaChica;
use haydee\modelo\DetallesGasto;
use haydee\modelo\MovimientosCaja;


if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "consultar_cajas_chicas"){
        $caja_obj = new CajaChica();

        $caja_obj->registrar_bitacora(CONSULTAR, GESTIONAR_CAJA_CHICA, "TODOS LAS CAJAS");

        echo  json_encode($caja_obj->realizar_consulta('consultar'));
    }
    else if ($operacion == "consultar_movimientos_caja"){
        $movimientos_caja_obj = new MovimientosCaja();        

        $caja_chica_id = $_POST["caja_chica_id"];

        $movimientos_caja_obj->set_caja_chica_id($caja_chica_id);

        echo  json_encode($movimientos_caja_obj->realizar_consulta('consultar_movimientos_caja'));
    }
    else if ($operacion == "consultar_movimiento"){
        $movimientos_caja_obj = new MovimientosCaja();

        $id_movimiento_caja = $_POST["id_movimiento_caja"];

        $movimientos_caja_obj->set_id_movimiento_caja($id_movimiento_caja);

        echo  json_encode($movimientos_caja_obj->realizar_consulta('consultar_movimiento'));
    }
    else if ($operacion == "registrar"){
        $movimientos_caja_obj = new MovimientosCaja();

        $concepto = $_POST["concepto"];
        $monto = $_POST["monto"];
        $fecha = $_POST["fecha"];
        $estado = "Pendiente por reposicion";
        $caja_chica_id = $_POST["caja_chica_id"];
        $gasto_id = null;

        $movimientos_caja_obj->set_concepto($concepto);    
        $movimientos_caja_obj->set_monto($monto);
        $movimientos_caja_obj->set_fecha($fecha);
        $movimientos_caja_obj->set_estado($estado);
        $movimientos_caja_obj->set_caja_chica_id($caja_chica_id);
        $movimientos_caja_obj->set_gasto_id($gasto_id);

        $resultado = $movimientos_caja_obj->realizar_consulta('registrar');

        if ($resultado["estatus"]) {
            $movimientos_caja_obj->registrar_bitacora(REGISTRAR, GESTIONAR_CAJA_CHICA, "Movimiento de: " . $monto . " Bs. El " . $fecha);
        }

        echo  json_encode($resultado);
    }
    else if ($operacion == "editar"){
        $movimientos_caja_obj = new MovimientosCaja();

        $concepto = $_POST["concepto"];
        $monto = $_POST["monto"];
        $fecha = $_POST["fecha"];

        $id_movimiento_caja = $_POST["id_movimiento_caja"];

        $movimientos_caja_obj->set_concepto($concepto);
        $movimientos_caja_obj->set_monto($monto);
        $movimientos_caja_obj->set_fecha($fecha);

        $movimientos_caja_obj->set_id_movimiento_caja($id_movimiento_caja);

        $resultado = $movimientos_caja_obj->realizar_consulta('editar');

        if ($resultado["estatus"]) {
            $movimientos_caja_obj->registrar_bitacora(MODIFICAR, GESTIONAR_CAJA_CHICA, "Movimiento de: " . $monto . " Bs. El " . $fecha);
        }

        echo  json_encode($resultado);
    }
    else if ($operacion == "editar_observacion"){
        $caja_obj = new CajaChica();

        $descripcion = $_POST["descripcion"];
        $id_caja = $_POST["id_caja"];
        
        $caja_obj->set_descripcion($descripcion);
        $caja_obj->set_id_caja_chica($id_caja);

        echo  json_encode($caja_obj->realizar_consulta('editar_descripcion'));
    }
    else if ($operacion == "eliminar"){
        $movimientos_caja_obj = new MovimientosCaja();

        $id_movimiento_caja = $_POST["id_movimiento_caja"];

        $movimientos_caja_obj->set_id_movimiento_caja($id_movimiento_caja);

        $movimento_alterado = $movimientos_caja_obj->realizar_consulta('consultar_movimiento');

        $resultado = $movimientos_caja_obj->realizar_consulta("eliminar");

        if ($resultado["estatus"]){
            if ($movimento_alterado) {
                $movimientos_caja_obj->registrar_bitacora(ELIMINAR, GESTIONAR_CAJA_CHICA, "Movimiento de: " . $movimento_alterado['monto'] . " Bs. De fecha " . $movimento_alterado['fecha']);
            }
            else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta para la bitácora"];
            }
        }            

        echo  json_encode($resultado);        
    }
    else if ($operacion == "reponer_caja"){
        $movimientos_caja_obj = new MovimientosCaja();
        $gastos_obj = new Gastos();
        $detalles_gastos_obj = new DetallesGasto();

        $id_caja_chica = $_POST["id_caja_chica"];
        $monto = $_POST["monto"];

        $gastos_obj->set_tipo("variable");
        $gastos_obj->set_descripcion_gasto("Reposición de Caja Chica");
        $gastos_obj->set_tipo_gasto_id(10);
        $gastos_obj->set_solicitud_id(null);
        $gastos_obj->set_proveedor_id(null);

        $resultado_registro_gasto = $gastos_obj->realizar_consulta("registrar");

        if (!$resultado_registro_gasto["estatus"]) {
            echo json_encode($resultado_registro_gasto);
            exit();
        }

        $gasto_id = $gastos_obj->realizar_consulta("lastId")["mensaje"];

        $detalles_gastos_obj->set_fecha(date("Y-m-d"));
        $detalles_gastos_obj->set_monto($monto);
        $detalles_gastos_obj->set_metodo_pago("Efectivo");
        $detalles_gastos_obj->set_descripcion_detalle_gasto("Reposición de Caja Chica");
        $detalles_gastos_obj->set_gasto_id($gasto_id);

        $resultado_registro_detalle = $detalles_gastos_obj->realizar_consulta('registrar');

        if (!$resultado_registro_detalle["estatus"]) {
            echo json_encode($resultado_registro_detalle);
            exit();
        }

        $movimientos_caja_obj->set_monto($monto);
        $movimientos_caja_obj->set_caja_chica_id($id_caja_chica);
        $movimientos_caja_obj->set_gasto_id($gasto_id);

        $resultado_reponer_caja = $movimientos_caja_obj->realizar_consulta('reponer_caja');

        if ($resultado_reponer_caja["estatus"]) {
            $gastos_obj->registrar_bitacora(REGISTRAR, GESTIONAR_GASTOS, "Reposición de caja por " . $monto . " Bs. El " . date("d-m-Y"));
        }

        echo  json_encode($resultado_reponer_caja);
    }

    exit;
}
if (isset($_POST["validar"])) {
        $validar = $_POST["validar"];

        if ($validar == "validar_clave_foranea") {
            $caja_obj = new CajaChica();

            $tabla = $_POST["tabla"];
            $nombre_clave = $_POST["nombre_clave"];
            $valor = $_POST["valor"];
            
            $resultado = $caja_obj->realizar_consulta('validar_clave_foranea',["tabla"=>$tabla,"nombre_clave"=>$nombre_clave,"valor"=>$valor]);
            
            echo json_encode($resultado);
        }
        exit;
    }
if($accion == "inicio"){    
    require_once "vista/caja_chica/caja_chica_vista.php";
}
?>
