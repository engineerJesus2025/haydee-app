<?php
namespace haydee\enums;

enum MetodoPago: string {
    case TRANSFERENCIA = 'Transferencia';
    case PAGO_MOVIL = 'Pago Movil';
    case EFECTIVO = 'Efectivo';
    // case ZELLE = 'Zelle'; // Por si acaso XD
}