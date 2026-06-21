<?php
namespace haydee\ayuda;

class ManejadorVistas
{
    /**
     * Limpia el buffer de salida y renderiza la pantalla de error de reportes.
     */
    public static function renderizarErrorReporte($mensaje, $titulo = "Inconsistencia de Datos")
    {
        if (ob_get_level() > 0) { 
            ob_end_clean(); 
        }
        require_once "vista/error/error_reportes.php";
        exit;
    }
}