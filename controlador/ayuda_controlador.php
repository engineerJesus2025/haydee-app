<?php 
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();

if($accion == "inicio"){
    require_once "vista/ayuda/ayuda_inicio_vista.php";
}

