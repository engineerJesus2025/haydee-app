<?php
namespace haydee\enums;

enum HttpCodigo: int {
    case OK = 200;
    case CREADO = 201;
    case BAD_REQUEST = 400;
    case NO_AUTORIZADO = 401;
    case PROHIBIDO = 403;
    case NO_ENCONTRADO = 404;
    case METODO_NO_PERMITIDO = 405;
    case NO_PROCESABLE = 422;
    case DEMASIADAS_PETICIONES = 429;
    case ERROR_INTERNO = 500;
}