<?php
namespace haydee\enums;

enum EstadoPago: string {
    case PENDIENTE = 'PENDIENTE';
    case PROCESADO = 'PROCESADO';
    case RECHAZADO = 'RECHAZADO';
    case ANULADO = 'ANULADO';
}