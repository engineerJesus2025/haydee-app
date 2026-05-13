<?php
namespace haydee\enums;

enum EstadoSolicitud: string {
    case PENDIENTE = 'Pendiente';
    case APROBADA = 'Aprobada';
    case RECHAZADA = 'Rechazada';
}