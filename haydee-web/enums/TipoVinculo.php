<?php
namespace haydee\enums;

enum TipoVinculo: string {
    case PROPIETARIO = 'PROPIETARIO';
    case FAMILIAR  = 'FAMILIAR';
    case INQUILINO = 'INQUILINO';
}