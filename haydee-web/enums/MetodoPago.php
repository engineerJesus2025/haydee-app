<?php
namespace haydee\enums;

enum MetodoPago: string {
    case TRANSFERENCIA = 'TRANSFERENCIA';
    case PAGO_MOVIL    = 'PAGO MOVIL';
    case EFECTIVO      = 'EFECTIVO';
    // case ZELLE = 'ZELLE'; // Por si acaso XD
}