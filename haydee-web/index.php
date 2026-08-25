<?php
// declare(strict_types=1); // Debatible

use haydee\enums\HttpCodigo;
use haydee\servicios\Sesiones;
use haydee\servicios\Endpoints;
use haydee\servicios\Excepciones;
use haydee\excepciones\HaydeeException;

require_once __DIR__ . "/vendor/autoload.php";

if (session_status() === PHP_SESSION_NONE) {session_start();}

$modulo = $_GET['pagina'] ?? 'login';
$accion = $_GET['accion'] ?? 'inicio';
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    if (!array_key_exists($modulo, Endpoints::MAPA)) {
        throw new HaydeeException("Modulo no definido en el mapa: '{$modulo}'", HttpCodigo::NO_ENCONTRADO->value);
    }

    $configRuta = Endpoints::MAPA[$modulo];

    // Firewall, Autenticación Web, Anti-Flood y Método HTTP
    Sesiones::autorizarAcceso($configRuta, $metodo);

    $archivoControlador = ROOT_PATH . "/controlador/" . $configRuta[Endpoints::CONF_ARCHIVO];

    if (!is_file($archivoControlador)) {
        throw new HaydeeException("El archivo del controlador mapeado no existe físicamente: '{$archivoControlador}'", HttpCodigo::NO_ENCONTRADO->value);
    }
    
    // requerimos el controlador
    require_once $archivoControlador;

} catch (\Throwable $e) {
    Excepciones::procesar($e, false); 
}