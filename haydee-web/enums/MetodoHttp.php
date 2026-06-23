<?php
namespace haydee\enums;

enum MetodoHttp: string
{
    case GET    = 'GET';
    case POST   = 'POST';
    case PUT    = 'PUT';
    case DELETE = 'DELETE';
}