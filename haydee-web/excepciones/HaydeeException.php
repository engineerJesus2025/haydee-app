<?php
namespace haydee\excepciones;

use Exception;
use Throwable;

class HaydeeException extends Exception 
{
    protected array $datosExtra = [];

    public function __construct($mensaje = "", $codigo = 500, Throwable $previa = null) 
    {
        // Pasamos el mensaje, el codigo HTTP y la excepción previa (si existe) a la clase nativa de PHP
        parent::__construct($mensaje, $codigo, $previa);
    }

    public function getDatosExtra() 
    {
        return $this->datosExtra;
    }

    public function setDatosExtra($datos) 
    {
        $this->datosExtra = $datos;
        return $this;
    }
}