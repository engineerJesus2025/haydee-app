<?php
namespace haydee\excepciones;

use haydee\enums\HttpCodigo;

class NegocioException extends HaydeeException
{
    public function __construct(string $mensaje, int $codigo = HttpCodigo::BAD_REQUEST->value)
    {
        parent::__construct($mensaje, $codigo);
    }
}