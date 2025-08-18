<?php 
require_once "modelo/mensualidad_modelo.php";
require_once "modelo/gastos_modelo.php";
require_once "modelo/gastos_mensualidades_modelo.php";
require_once "modelo/apartamentos_modelo.php";
require_once "modelo/notificaciones_modelo.php";
require_once "modelo/usuario_modelo.php";

// $mensualidad_obj = new Mensualidad();
// $gastos_mensualidades_obj = new Gastos_mensualidades();
// $gastos_obj = new Gastos();
// $apartamento_obj = new Apartamento();
// $notificacion_obj = new Notificaciones();
// $usuario_obj = new Usuario();

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "verificar_meses"){
        $mensualidad_obj = new Mensualidad();
        echo json_encode($mensualidad_obj->verificarMeses());
    }
    if ($operacion == "consultar_mensualidades"){
        $mensualidad_obj = new Mensualidad();
        echo json_encode($mensualidad_obj->consultar());
    }
    else if ($operacion == "consultar_mensualidad"){
        $mensualidad_obj = new Mensualidad();   
        $fecha = $_POST["fecha"];

        list($dia,$mes_buscar,$anio_buscar) = explode('/', $fecha);
        
        $mensualidad_obj->set_mes($mes_buscar);
        $mensualidad_obj->set_anio($anio_buscar);

        echo json_encode($mensualidad_obj->consultar_mensualidad());
    }
    else if ($operacion == "consultar_gastos"){
        $gastos_obj = new Gastos();
        $fecha = $_POST["fecha"];
        //AJA
        $gastos_obj->set_fecha($fecha);        

        echo json_encode($gastos_obj->consultar_gastos());
    }
    else if($operacion == "registrar_mensualidad"){
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

        $resultado = $mensualidad_obj->registrar();

        if ($resultado["estatus"]) {
            $usuario_obj = new Usuario();
            $registro_usuarios = $usuario_obj->consultar();

            $notificacion_obj = new Notificaciones();
            foreach ($registro_usuarios as $usuarios) {
                $notificacion_obj->set_titulo("Mensualidad de Apartamentos");
                $notificacion_obj->set_descripcion("Ya se asginaron las mensualidades de este mes");
                $notificacion_obj->set_fecha(date("Y-m-d"));
                $notificacion_obj->set_usuario_id($usuarios["id_usuario"]);
                $resultado_notificacion = $notificacion_obj->agregar_notificacion();

                if (!$resultado_notificacion["estatus"]) {
                    echo json_encode($resultado_notificacion);
                    exit();
                }
            }
        }
        echo json_encode($resultado);
    }
    else if($operacion == "registrar_gastos_mensualidades"){
        $gastos_mensualidades_obj = new Gastos_mensualidades();
        $id_mensualidad = $_POST["id_mensualidad"];
        $id_gastos = explode(",", $_POST["id_gastos"]);

        $gastos_mensualidades_obj->set_mensualidad_id($id_mensualidad);

        $resultado = null;
        foreach ($id_gastos as $gasto) {
            $gastos_mensualidades_obj->set_gasto_id($gasto);            
            $resultado = $gastos_mensualidades_obj->registrar();
            
            if (!$resultado["estatus"]) {
                echo json_encode($resultado);
                exit;
            }
        }
        echo json_encode($resultado);
    }
    else if ($operacion == "consultar_gastos_asociados"){
        $gastos_mensualidades_obj = new Gastos_mensualidades();
        $ids_mensualidades = explode(",", $_POST["ids_mensualidades"]);

        $resultado = [];
        foreach ($ids_mensualidades as $id_mensualidad) {
            $gastos_mensualidades_obj->set_mensualidad_id($id_mensualidad);

            $resultado_consulta = $gastos_mensualidades_obj->consultar_gastos_asociados();
            
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
        $tasa_dolar = $_POST["tasa_dolar"];
        $mes = $_POST["mes"];
        $anio = $_POST["anio"];
        $apartamento_id = $_POST["apartamento_id"];
        $id_mensualidad = $_POST["id_mensualidad"];

        $mensualidad_obj->set_monto($monto);
        $mensualidad_obj->set_tasa_dolar($tasa_dolar);
        $mensualidad_obj->set_mes($mes);
        $mensualidad_obj->set_anio($anio);
        $mensualidad_obj->set_apartamento_id($apartamento_id);
        $mensualidad_obj->set_id_mensualidad($id_mensualidad);

        echo json_encode($mensualidad_obj->editar());
    }
    else if($operacion == "editar_gastos_mensualidades"){
        $gastos_mensualidades_obj = new Gastos_mensualidades();

        $id_mensualidad = $_POST["id_mensualidad"];
        $id_gastos = $_POST["id_gastos"];

        $gastos_mensualidades_obj->set_mensualidad_id($id_mensualidad);
        $gastos_mensualidades_obj->set_gasto_id($id_gastos);

        $resultado = $gastos_mensualidades_obj->editar();

        echo json_encode($resultado);
    }
    else if($operacion == "eliminar_mensualidad"){
        $mensualidad_obj = new Mensualidad();

        $fecha = $_POST["fecha"];
        list($dia,$mes_buscar,$anio_buscar) = explode('/', $fecha);

        $mensualidad_obj->set_mes($mes_buscar);
        $mensualidad_obj->set_anio($anio_buscar);
        
        echo json_encode($mensualidad_obj->eliminar());
    }
    exit;
}

$apartamento_obj = new Apartamento();
$registos_apartamentos = $apartamento_obj->consultar_apartamentos_mensualidad();

require_once 'vista/mensualidad/mensualidad_vista.php';
?>