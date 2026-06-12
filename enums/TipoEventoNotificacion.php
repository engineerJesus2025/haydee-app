<?php
namespace haydee\enums;

enum TipoEventoNotificacion: string {
    case NUEVA_PUBLICACION = 'nueva_publicacion';
    case PAGO_RECIBIDO     = 'pago_recibido';
    case BAJO_SALDO        = 'bajo_saldo';
    case GASTO_CAJA_CHICA  = 'gasto_caja_chica';
    case NUEVA_MENSUALIDAD = 'nueva_mensualidad';
    case EMERGENCIA        = 'emergencia';
}