<?php
namespace haydee\excepciones;

use haydee\enums\HttpCodigo;

class SeguridadException extends HaydeeException 
{
    public function __construct(string $mensaje, int $codigo = HttpCodigo::PROHIBIDO->value, array $datosAdicionales = []) 
    {
        parent::__construct($mensaje, $codigo);
        $this->setDatosExtra($datosAdicionales);
    }
}