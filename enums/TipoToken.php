<?php
namespace haydee\enums;

enum TipoToken: string {
    case RECUPERACION = 'RECUPERAR_CONTRASENIA';
    case RECUERDAME = 'RECORDAR_CONTRASENIA';
    case REFRESH_MOVIL = 'REFRESH_TOKEN_MOVIL';
}