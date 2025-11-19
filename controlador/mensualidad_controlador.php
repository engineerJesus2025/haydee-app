<?php 
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_MENSUALIDAD, CONSULTAR);

use haydee\modelo\Mensualidad;
use haydee\modelo\Presupuesto;
use haydee\modelo\Notificaciones;
use haydee\modelo\PresupuestoMensualidad;
use haydee\modelo\Apartamento;

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "verificar_meses"){
        $mensualidad_obj = new Mensualidad();
        echo json_encode($mensualidad_obj->realizar_consulta('verificarMeses'));
    }
    else if ($operacion == "consultar_mensualidades_mes"){
        $mensualidad_obj = new Mensualidad();

        $mensualidad_obj->registrar_bitacora(CONSULTAR, GESTIONAR_MENSUALIDAD, "TODOS LAS MENSUALIDADES");

        echo json_encode($mensualidad_obj->realizar_consulta('consultarPorMeses'));
    }
    else if ($operacion == "consultar_mensualidades_apartamentos"){
        $mensualidad_obj = new Mensualidad();   
        $fecha = $_POST["fecha"];

        list($dia,$mes_buscar,$anio_buscar) = explode('/', $fecha);
        
        $mensualidad_obj->set_mes($mes_buscar);
        $mensualidad_obj->set_anio($anio_buscar);

        echo json_encode($mensualidad_obj->realizar_consulta('consultar_mensualidad_apartamentos'));
    }
    else if ($operacion == "consultar_presupuestos_mensualidades"){
        $presupuesto_obj = new Presupuesto();

        $fecha = $_POST["fecha"];
        $fecha_formateada = str_replace('/', '-', $fecha);
        $fecha_ymd = date("Y-m-d", strtotime($fecha_formateada));        
        $presupuesto_obj->set_fecha($fecha_ymd); 

        echo json_encode($presupuesto_obj->realizar_consulta('consultar_presupuestos_mensualidades'));
    }
    else if($operacion == "registrar_mensualidad"){
        $mensualidad_obj = new Mensualidad();
        
        $monto = $_POST["monto"];
        $tasa_dolar = $_POST["tasa_dolar"];
        $mes = $_POST["mes"];
        $anio = $_POST["anio"];
        $apartamento_id = $_POST["apartamento_id"];
        $porcentaje_interes = $_POST["porcentaje_interes"];
        $limite_mensualidad = $_POST["limite_mensualidad"];

        $mensualidad_obj->set_monto($monto);
        $mensualidad_obj->set_tasa_dolar($tasa_dolar);
        $mensualidad_obj->set_mes($mes);
        $mensualidad_obj->set_anio($anio);
        $mensualidad_obj->set_apartamento_id($apartamento_id);
        $mensualidad_obj->set_porcentaje_interes($porcentaje_interes);
        $mensualidad_obj->set_limite_mensualidad($limite_mensualidad);

        $resultado = $mensualidad_obj->realizar_consulta('registrar');
        // lastId
        // if ($resultado["estatus"]) {
        //     $mensualidad_obj->registrar_bitacora(REGISTRAR, GESTIONAR_MENSUALIDAD, "Mensualidad del mes " . $mes . " del ". $anio . ". De " . $monto . " Bs.");

        //     $notificacion_obj = new Notificaciones();
        //     $notificacion_obj->set_titulo("Mensualidad de Apartamentos");
        //     $notificacion_obj->set_descripcion("Ya se asginaron las mensualidades de este mes");
        //     $notificacion_obj->set_fecha(date("Y-m-d"));
        //     $notificacion_obj->set_nombre_modulo('mensualidad');
        //     $notificacion_obj->set_referencia($mes . "/" . $anio);

        //     $resultado_notificacion = $notificacion_obj->realizar_consulta('notificar_administradores');

        //     if (!$resultado_notificacion["estatus"]) {
        //         echo json_encode($resultado_notificacion);
        //         exit();
        //     }
            
        // }
        echo json_encode($resultado);
    }
    else if($operacion == "registrar_presupuestos_mensualidades"){
        $presupuesto_mensualidad_obj = new PresupuestoMensualidad();

        $id_mensualidad = $_POST["id_mensualidad"];
        $id_presupuestos = explode(",", $_POST["id_presupuestos"]);

        $presupuesto_mensualidad_obj->set_mensualidad_id($id_mensualidad);

        $resultado = null;
        foreach ($id_presupuestos as $presupuesto) {
            $presupuesto_mensualidad_obj->set_presupuesto_id($presupuesto);            
            $resultado = $presupuesto_mensualidad_obj->realizar_consulta('registrar_presupuesto_mensualidad');
            
            if (!$resultado["estatus"]) {
                echo json_encode($resultado);
                exit;
            }
        }
        echo json_encode($resultado);
    }
    else if($operacion == "registrar_bitacora"){
        $monto = $_POST["monto"];
        $mes = $_POST["mes"];
        $anio = $_POST["anio"];

        $mensualidad_obj = new Mensualidad();

        $mensualidad_obj->registrar_bitacora(REGISTRAR, GESTIONAR_MENSUALIDAD, "Mensualidad del mes " . $mes . " del ". $anio . ". De " . $monto . " Bs.");

        $notificacion_obj = new Notificaciones();
        $notificacion_obj->set_titulo("Mensualidad de Apartamentos");
        $notificacion_obj->set_descripcion("Ya se asginaron las mensualidades de este mes");
        $notificacion_obj->set_fecha(date("Y-m-d"));
        $notificacion_obj->set_nombre_modulo('mensualidad');
        $notificacion_obj->set_referencia($mes . "/" . $anio);

        $resultado_notificacion = $notificacion_obj->realizar_consulta('notificar_administradores');

        if (!$resultado_notificacion["estatus"]) {
            echo json_encode($resultado_notificacion);
            exit();
        }

        echo json_encode($resultado_notificacion);
    }
    else if ($operacion == "consultar_presupuestos_asociados"){
        $presupuesto_mensualidad_obj = new PresupuestoMensualidad();

        $ids_mensualidades = explode(",", $_POST["ids_mensualidades"]);

        $resultado = [];
        foreach ($ids_mensualidades as $id_mensualidad) {
            $presupuesto_mensualidad_obj->set_mensualidad_id($id_mensualidad);

            $resultado_consulta = $presupuesto_mensualidad_obj->realizar_consulta('consultar_presupuestos_asociados');
            
            
            array_push($resultado, $resultado_consulta);
            
        }
        echo json_encode($resultado);
    }
    else if($operacion == "editar_mensualidad"){
        $mensualidad_obj = new Mensualidad();

        $monto = $_POST["monto"];
        $tasa_dolar = $_POST["tasa_dolar"];
        $mes = $_POST["mes"];
        $anio = $_POST["anio"];
        $apartamento_id = $_POST["apartamento_id"];
        $id_mensualidad = $_POST["id_mensualidad"];
        $porcentaje_interes = $_POST["porcentaje_interes"];
        $limite_mensualidad = $_POST["limite_mensualidad"];

        $mensualidad_obj->set_monto($monto);
        $mensualidad_obj->set_tasa_dolar($tasa_dolar);
        $mensualidad_obj->set_mes($mes);
        $mensualidad_obj->set_anio($anio);
        $mensualidad_obj->set_apartamento_id($apartamento_id);
        $mensualidad_obj->set_id_mensualidad($id_mensualidad);
        $mensualidad_obj->set_porcentaje_interes($porcentaje_interes);
        $mensualidad_obj->set_limite_mensualidad($limite_mensualidad);

        $resultado = $mensualidad_obj->realizar_consulta('editar');
            
        if ($resultado["estatus"]) {
            $mensualidad_obj->registrar_bitacora(MODIFICAR, GESTIONAR_MENSUALIDAD, "Mensualidad del mes " . $mes . " del " . $anio . ". De " . $monto . " Bs.");
        }
            
        echo json_encode($resultado);        
    }
    else if($operacion == "editar_presupuesto_mensualidades"){
        $presupuesto_mensualidad_obj = new PresupuestoMensualidad();

        $id_mensualidad = $_POST["id_mensualidad"];
        $id_presupuestos = $_POST["id_presupuestos"];

        $presupuesto_mensualidad_obj->set_mensualidad_id($id_mensualidad);
        $presupuesto_mensualidad_obj->set_presupuesto_id($id_presupuestos);

        $resultado = $presupuesto_mensualidad_obj->realizar_consulta('editar');

        echo json_encode($resultado);
    }
    else if($operacion == "eliminar_mensualidad"){
        $mensualidad_obj = new Mensualidad();

        $fecha = $_POST["fecha"];
        list($dia,$mes_buscar,$anio_buscar) = explode('/', $fecha);

        $mensualidad_obj->set_mes($mes_buscar);
        $mensualidad_obj->set_anio($anio_buscar);            

        $resultado = $mensualidad_obj->realizar_consulta("eliminar");

        if ($resultado["estatus"]){            
            $mensualidad_obj->registrar_bitacora(ELIMINAR, GESTIONAR_MENSUALIDAD, "Eliminadas menusalidades del mes " . $mes_buscar . " del " . $anio_buscar);
        }            

        echo  json_encode($resultado);
    }
    exit;
}

$apartamento_obj = new Apartamento();
$registos_apartamentos = $apartamento_obj->consultar_apartamentos_mensualidad();

require_once 'vista/mensualidad/mensualidad_vista.php';
?>