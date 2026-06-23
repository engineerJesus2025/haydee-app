<?php
namespace haydee\enums;

enum TablaOrigen: string {
    case PAGOS = 'pagos';
    case GASTOS = 'gastos';
    case MENSUALIDAD = 'mensualidad';
    case CARTELERA = 'cartelera_virtual';
    case SISTEMA = 'sistema';
}