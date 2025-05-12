<?php 

require_once "modelo/bitacora_modelo.php";

if($accion == "inicio"){
    $bitacora = new Bitacora();
    $registros = $bitacora->consultar();
    require_once "vista/bitacora/bitacora_vista.php";
}

?>