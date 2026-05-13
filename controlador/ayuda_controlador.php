<?php 
use haydee\servicios\Sesiones;

// Protección básica (Red, HTTP, Sesión iniciada)
Sesiones::autorizarAcceso();

if($accion == "inicio"){
    require_once "vista/ayuda/ayuda_inicio_vista.php";
}

