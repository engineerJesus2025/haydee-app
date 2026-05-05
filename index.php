<?php
    require_once "vendor/autoload.php";
    
    session_start();

    $rutas = RUTAS;

    $modulo = $_GET['pagina'] ?? 'login'; 
    $accion = $_GET['accion'] ?? 'inicio';

    if (array_key_exists($modulo, $rutas)) {
        
        $archivo_controlador = ROOT_PATH . "/controlador/" . $rutas[$modulo];

        if(is_file($archivo_controlador)) {
            require_once $archivo_controlador;
        } else {
            http_response_code(404);
            require_once ROOT_PATH . "/vista/error/404_vista.php";
        }
    } else {
        http_response_code(404);
        require_once ROOT_PATH . "/vista/error/404_vista.php";
    }
?>