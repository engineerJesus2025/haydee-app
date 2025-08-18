<?php 
require_once "modelo/notificaciones_modelo.php";

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "consultar"){
        $notificaciones_obj = new Notificaciones();
        echo  json_encode($notificaciones_obj->realizar_consulta('consultar'));
    }
    else if ($operacion == "quitar_notificacion"){
        $notificaciones_obj = new Notificaciones();

        $id_notificacion = $_POST["id"];

        $notificaciones_obj->set_id_notificacion($id_notificacion);

        $resultado = $notificaciones_obj->realizar_consulta('marcar_como_activo');
        $indices_notificaciones = array_keys($_SESSION["notificaciones"]);
                
        foreach ($indices_notificaciones as $indice) {        
            if ($_SESSION["notificaciones"][$indice]["id_notificacion"] == $id_notificacion) {
                unset($_SESSION["notificaciones"][$indice]);
            }
        }
        echo json_encode($resultado);
    }
    exit;
}

if($accion == "inicio"){    
    require_once "vista/notificaciones/notificaciones_vista.php";
}

?>