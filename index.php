<?php
    require_once 'vendor/autoload.php';
    
    session_start();
    $pagina = "login_controlador.php";

    $accion = "inicio";

    if(isset($_GET["pagina"]) && isset($_GET["accion"])){
        $pagina = $_GET["pagina"];
        $accion = $_GET["accion"];
    }

    if(is_file("controlador/" . $pagina)){
        require_once "controlador/" . $pagina;
        
    }
    else{
        require_once "vista/error/404_vista.php";
    }
?>