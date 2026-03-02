<?php
    // 1. Inicialización Global
    require_once 'vendor/autoload.php';
    
    session_start();

    // 2. Cargar el mapa de rutas
    // Al hacer require de un archivo que hace 'return', la variable asimila el valor
    $rutas = RUTAS;

    // 3. Capturar la petición del usuario
    // Si no envían módulo, por defecto será 'login'
    $modulo = $_GET['pagina'] ?? 'login'; 
    $accion = $_GET['accion'] ?? 'inicio';

    // 4. Despachar (El Front Controller en acción)
    // Verificamos si el módulo que piden existe en nuestro mapa de rutas
    if (array_key_exists($modulo, $rutas)) {
        
        $archivo_controlador = "controlador/" . $rutas[$modulo];
        
        // Medida de seguridad extra: verificar que el archivo físico realmente exista

        if(is_file($archivo_controlador)) {
            require_once $archivo_controlador;
        } else {
            // El módulo está en rutas.php, pero olvidaste crear el archivo físico
            require_once "vista/error/404_vista.php";
        }

    } else {
        // Intentaron acceder a un módulo que no existe o intentaron alterar la URL
        require_once "vista/error/404_vista.php";
    }
?>