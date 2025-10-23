<?php 
require_once "vista/componentes/sesion.php";
require_once "modelo/bitacora_modelo.php";

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