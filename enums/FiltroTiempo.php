<?php
namespace haydee\enums;

enum FiltroTiempo: string {
    case MES = 'mes';
    case TRIMESTRE = 'trimestre';
    case ANIO = 'anio';
    case PERSONALIZADO = 'personalizado';
}