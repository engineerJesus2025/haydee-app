<?php 

require_once "modelo/notificaciones_modelo.php";

$notificaciones = new Notificaciones();

if($accion == "inicio"){
    $registros = $notificaciones->consultar();
    require_once "vista/notificaciones/notificaciones_vista.php";
}
if($accion == "quitar"){
    header('Content-Type: application/json'); // <-- Muy importante

    $id_notificacion = $_POST["id"];	

    $resultado = $notificaciones->marcar_como_leida($id_notificacion);
    $indices_notificaciones = array_keys($_SESSION["notificaciones"]);
            
    foreach ($indices_notificaciones as $indice) {        
        if ($_SESSION["notificaciones"][$indice]["id_notificacion"] == $id_notificacion) {
            unset($_SESSION["notificaciones"][$indice]);
        }        
    }    

    echo json_encode(['ok' => $resultado]);
    exit;
}

?>