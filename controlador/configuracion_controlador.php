<?php
require_once "modelo/conexion.php";
    if($accion == "inicio"){
        unset($_SESSION["mensaje"]);
        require_once "vista/configuracion/configuracion.php";
    }

?>