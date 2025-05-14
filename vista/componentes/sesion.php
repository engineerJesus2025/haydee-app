<?php 
	if(!(isset($_SESSION["usuario"]))){
		header("Location:?pagina=login_controlador.php&accion=inicio");
	}
?>