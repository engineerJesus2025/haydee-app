<?php 
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();

use haydee\modelo\Bitacora;

if (isset($_POST["operacion"])){
	$operacion = $_POST["operacion"];

	if ($operacion == "consultar"){
		$bitacora = new Bitacora();
		echo  json_encode($bitacora->realizar_consulta('consultar'));            
	}
    exit;
}
    require_once "vista/bitacora/bitacora_vista.php";
?>