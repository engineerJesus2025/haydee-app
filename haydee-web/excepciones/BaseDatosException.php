<?php
namespace haydee\excepciones;

use haydee\enums\HttpCodigo;
use Throwable;

class BaseDatosException extends HaydeeException 
{
    public function __construct(string $mensaje = "Error interno de base de datos.", int $codigo = HttpCodigo::ERROR_INTERNO->value, Throwable $previa = null) 
    {
        parent::__construct($mensaje, $codigo, $previa);
    }
}