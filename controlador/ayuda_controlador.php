<?php 
use haydee\servicios\Sesiones;
Sesiones::validarMetodoHTTP(['GET', 'POST']);
Sesiones::verificarSesion();

if($accion == "inicio"){
    require_once "vista/ayuda/ayuda_inicio_vista.php";
}

