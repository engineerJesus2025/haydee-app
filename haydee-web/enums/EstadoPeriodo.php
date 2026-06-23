<?php
namespace haydee\enums;

enum EstadoPeriodo: string {
    case ABIERTO = 'ABIERTO';
    case CERRADO = 'CERRADO';
    case PLANIFICADO = 'PLANIFICADO'; // yo, si lees esto, acuerdate de implementar la logica del nuevo estado en años fiscales...
}