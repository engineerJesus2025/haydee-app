<?php
/**
 * Función que devuelve el título y subtítulo dinámico según la página.
 */
function obtenerInfoEncabezado($pagina) {
    // Diccionario de títulos y subtítulos
    $directorio_titulos = [
        'inicio'            => ['titulo' => 'Dashboard', 'subtitulo' => 'Resumen general del condominio'],
        'pagos'             => ['titulo' => 'Pagos', 'subtitulo' => 'Gestión de pagos y transacciones'],
        'gastos'            => ['titulo' => 'Gastos', 'subtitulo' => 'Registro y seguimiento de gastos'],
        'mensualidad'       => ['titulo' => 'Mensualidad', 'subtitulo' => 'Gestión de mensualidades y cuotas'],
        'cartelera_virtual' => ['titulo' => 'Cartelera Virtual', 'subtitulo' => 'Anuncios y comunicados para los habitantes'],
        'anio_fiscal'       => ['titulo' => 'Año Fiscal', 'subtitulo' => 'Configuración del año fiscal y periodos contables'],
        'reportes'          => ['titulo' => 'Reportes', 'subtitulo' => 'Generación de reportes financieros y administrativos'],
        'perfil'            => ['titulo' => 'Mi Perfil', 'subtitulo' => 'Información y configuración de mi cuenta'],
        'bancos'            => ['titulo' => 'Bancos', 'subtitulo' => 'Control de cuentas bancarias'],
        'apartamentos'      => ['titulo' => 'Apartamentos y Habitantes', 'subtitulo' => 'Gestión de apartamentos, habitantes y propietarios'],
        'caja_chica'        => ['titulo' => 'Caja Chica', 'subtitulo' => 'Control de caja chica y gastos menores'],
        'solicitud_gasto'   => ['titulo' => 'Solicitudes de Gasto', 'subtitulo' => 'Gestión de solicitudes de gasto y aprobaciones'],
        'presupuesto'       => ['titulo' => 'Presupuestos Mensuales', 'subtitulo' => 'Planificación y seguimiento del presupuesto mensual'],
        'proveedores'       => ['titulo' => 'Proveedores', 'subtitulo' => 'Gestión de proveedores y servicios'],
        'tipo_gasto'        => ['titulo' => 'Tipos de Gasto', 'subtitulo' => 'Categorías y tipos de gastos para clasificación'],
        'usuario'           => ['titulo' => 'Usuarios', 'subtitulo' => 'Gestión de usuarios y cuentas del sistema'],
        'rol'               => ['titulo' => 'Roles', 'subtitulo' => 'Definición y asignación de roles y permisos'],
        'bitacora'          => ['titulo' => 'Bitácora de Actividad', 'subtitulo' => 'Registro de acciones y eventos del sistema'],
        'permisos'          => ['titulo' => 'Permisos', 'subtitulo' => 'Gestión de permisos y accesos para usuarios'],
        'modulos'           => ['titulo' => 'Módulos', 'subtitulo' => 'Gestión de módulos y funcionalidades del sistema'],
        'notificaciones'    => ['titulo' => 'Notificaciones', 'subtitulo' => 'Gestión de notificaciones y alertas para los usuarios'],
        'mantenimiento'     => ['titulo' => 'Mantenimiento', 'subtitulo' => 'Tareas de mantenimiento y optimización del sistema']
    ];

    // Si la página existe en el diccionario, la devuelve. Si no, devuelve "inicio" por defecto.
    return $directorio_titulos[$pagina] ?? $directorio_titulos['inicio'];
}
