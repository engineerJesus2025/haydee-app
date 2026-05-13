<?php
namespace haydee\enums;

enum EstadoMovimientoCaja: string {
    case PENDIENTE_REPOSICION = 'Pendiente por reposicion';
    case REPUESTO = 'Repuesto';
}