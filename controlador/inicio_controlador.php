<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Mensualidad;
use haydee\modelo\CarteleraVirtual;
use haydee\servicios\Autenticacion;
// Verificación de sesión (sin permiso porque es la página de inicio)
Sesiones::verificarSesion();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    
    try{
        switch ($operacion) {
            case 'consulta_inicio':
                $cartelera = new CarteleraVirtual();
                $limite = $_POST["limite"] ?? 0;

                $respuesta = $cartelera->consultar_inicio($limite);
                break;

            case 'consulta_inicio_grafico':
                $mensualidad = new Mensualidad();
                $respuesta = $mensualidad->realizar_consulta('consultar_estadisticas_inicio');

                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($cartelera)) {
                $cartelera->cerrar();
            }
            if (isset($mensualidad)) {
                $mensualidad->cerrar();
            }

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Cargar la vista del inicio
require_once "vista/inicio/inicio_vista.php";
