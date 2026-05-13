<?php
namespace haydee\enums;

enum TipoVinculo: string {
    case PROPIETARIO = 'Propietario';
    case HABITANTE = 'Habitante';
    case INQUILINO = 'Inquilino';
}