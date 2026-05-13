<?php
namespace haydee\enums;

// define("REGISTRAR", "REGISTRAR");
// define("CONSULTAR", "CONSULTAR");
// define("MODIFICAR", "MODIFICAR");
// define("ELIMINAR", "ELIMINAR");
// define("DESCARGAR", "DESCARGAR"); // Reportes
// define("RESPALDAR", "RESPALDAR"); // Copias de seguridad generadas
// define("RESTAURAR", "RESTAURAR"); // Respaldos cargados
// define("INICIAR_SESION", "INICIAR SESION");
// define("CERRAR_SESION", "CERRAR SESION");

enum Accion: string 
{
    case REGISTRAR = 'REGISTRAR';
    case CONSULTAR = 'CONSULTAR';
    case MODIFICAR = 'MODIFICAR';
    case ELIMINAR = 'ELIMINAR';
    case DESCARGAR = 'DESCARGAR'; // Reportes
    case RESPALDAR = 'RESPALDAR'; // Copias de seguridad
    case RESTAURAR = 'RESTAURAR'; // Respaldos cargados
    case INICIAR_SESION = 'INICIAR SESION';
    case CERRAR_SESION = 'CERRAR SESION';
}

?>