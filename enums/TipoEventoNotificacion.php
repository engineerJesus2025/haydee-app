<?php
namespace haydee\enums;

enum TipoEventoNotificacion: string {
    case NUEVO_PAGO = 'NUEVO_PAGO';
    case GASTO_REGISTRADO = 'GASTO_REGISTRADO';
    case ALERTA_SISTEMA = 'ALERTA_SISTEMA';
    case RECORDATORIO = 'RECORDATORIO';
}