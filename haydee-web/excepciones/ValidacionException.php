<?php
namespace haydee\excepciones;

use haydee\enums\HttpCodigo;

class ValidacionException extends HaydeeException 
{
    private array $errores;

    public function __construct(string $mensaje, array $errores = [], int $codigo = HttpCodigo::NO_PROCESABLE->value) 
    {
        parent::__construct($mensaje, $codigo);
        $this->errores = $errores;
        
        $this->setDatosExtra(['errores' => $errores]);
    }

    public function getErrores(): array 
    {
        return $this->errores;
    }
}