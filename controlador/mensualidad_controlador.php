<?php 
require_once "modelo/mensualidad_modelo.php";
require_once "modelo/apartamentos_modelo.php";
require_once "modelo/notificaciones_modelo.php";
require_once "modelo/usuario_modelo.php";
require_once "modelo/presupuesto_modelo.php";
require_once "modelo/presupuesto_mensualidad_modelo.php";

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "verificar_meses"){
        $mensualidad_obj = new Mensualidad();
        echo json_encode($mensualidad_obj->realizar_consulta('verificarMeses'));
    }
    else if ($operacion == "consultar_mensualidades_mes"){
        $mensualidad_obj = new Mensualidad();
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
        $monto_dolar = $_POST["monto_dolar"];
        $mes = $_POST["mes"];
        $anio = $_POST["anio"];
        $apartamento_id = $_POST["apartamento_id"];

        $mensualidad_obj->set_monto($monto);
        $mensualidad_obj->set_monto_dolar($monto_dolar);
        $mensualidad_obj->set_mes($mes);
        $mensualidad_obj->set_anio($anio);
        $mensualidad_obj->set_apartamento_id($apartamento_id);

        $resultado = $mensualidad_obj->realizar_consulta('registrar');

        if ($resultado["estatus"]) {
            $usuario_obj = new Usuario();
            $registro_usuarios = $usuario_obj->consultar();

            $notificacion_obj = new Notificaciones();
            foreach ($registro_usuarios as $usuarios) {
                $notificacion_obj->set_titulo("Mensualidad de Apartamentos");
                $notificacion_obj->set_descripcion("Ya se asginaron las mensualidades de este mes");
                $notificacion_obj->set_fecha(date("Y-m-d"));
                $notificacion_obj->set_usuario_id($usuarios["id_usuario"]);
                $resultado_notificacion = $notificacion_obj->realizar_consulta('agregar_notificacion');

                if (!$resultado_notificacion["estatus"]) {
                    echo json_encode($resultado_notificacion);
                    exit();
                }
            }
        }
        echo json_encode($resultado);
    }
    else if($operacion == "registrar_presupuestos_mensualidades"){
        $presupuesto_mensualidad_obj = new Presupuesto_mensualidad();

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
    else if ($operacion == "consultar_presupuestos_asociados"){
        $presupuesto_mensualidad_obj = new Presupuesto_mensualidad();

        $ids_mensualidades = explode(",", $_POST["ids_mensualidades"]);

        $resultado = [];
        foreach ($ids_mensualidades as $id_mensualidad) {
            $presupuesto_mensualidad_obj->set_mensualidad_id($id_mensualidad);

            $resultado_consulta = $presupuesto_mensualidad_obj->realizar_consulta('consultar_presupuestos_asociados');
            
            if (!$resultado_consulta["estatus"]) {
                echo json_encode($resultado_consulta);
                exit;
            }
            else{
                array_push($resultado, $resultado_consulta["mensaje"]);
            }
        }
        echo json_encode($resultado);
    }
    else if($operacion == "editar_mensualidad"){
        $mensualidad_obj = new Mensualidad();

        $monto = $_POST["monto"];
        $monto_dolar = $_POST["monto_dolar"];
        $mes = $_POST["mes"];
        $anio = $_POST["anio"];
        $apartamento_id = $_POST["apartamento_id"];
        $id_mensualidad = $_POST["id_mensualidad"];

        $mensualidad_obj->set_monto($monto);
        $mensualidad_obj->set_monto_dolar($monto_dolar);
        $mensualidad_obj->set_mes($mes);
        $mensualidad_obj->set_anio($anio);
        $mensualidad_obj->set_apartamento_id($apartamento_id);
        $mensualidad_obj->set_id_mensualidad($id_mensualidad);

        echo json_encode($mensualidad_obj->realizar_consulta('editar'));
    }
    else if($operacion == "editar_presupuesto_mensualidades"){
        $presupuesto_mensualidad_obj = new Presupuesto_mensualidad();

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
        
        echo json_encode($mensualidad_obj->realizar_consulta('eliminar'));
    }
    exit;
}

$apartamento_obj = new Apartamento();
$registos_apartamentos = $apartamento_obj->consultar_apartamentos_mensualidad();

require_once 'vista/mensualidad/mensualidad_vista.php';
?>