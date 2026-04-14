<?php
use haydee\servicios\Sesiones;

/**
 * Función que genera y devuelve la estructura del menú de navegación.
 */
function obtenerElementosMenu($modulo, $accion) {
    //  Evaluamos condiciones de submódulos para saber qué menú debe estar abierto
    $sub_reporte_pdf = ($modulo === "reportes" && $accion === "reportes_pdf");
    $sub_reporte_est = ($modulo === "reportes" && in_array($accion, ["reportes_estadisticos", "habitantes", "ingreso_egreso"]));
    $es_reporte      = ($sub_reporte_pdf || $sub_reporte_est);

    $es_configuracion = in_array($modulo, ["proveedores", "bancos", "tipo_gasto"]);
    $es_seguridad     = in_array($modulo, ["rol", "bitacora", "permisos", "modulos"]); 

    //  Retornamos la matriz de elementos
    return [
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Pagos',
	        'icono'   => 'bi-cash-coin',
	        'url'     => '?pagina=pagos&accion=inicio',
	        'activo'  => ($modulo === 'pagos'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_PAGOS, CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Gastos',
	        'icono'   => 'bi-cart-plus',
	        'url'     => '?pagina=gastos&accion=inicio',
	        'activo'  => ($modulo === 'gastos'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_GASTOS, CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Caja Chica',
	        'icono'   => 'bi-bank2',
	        'url'     => '?pagina=caja_chica&accion=inicio',
	        'activo'  => ($modulo === 'caja_chica'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_CAJA_CHICA, CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Mensualidad',
	        'icono'   => 'bi-piggy-bank-fill',
	        'url'     => '?pagina=mensualidad&accion=inicio',
	        'activo'  => ($modulo === 'mensualidad'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_MENSUALIDAD, CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Cartelera Virtual',
	        'icono'   => 'bi-tv',
	        'url'     => '?pagina=cartelera_virtual&accion=inicio',
	        'activo'  => ($modulo === 'cartelera_virtual'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_CARTELERA_VIRTUAL, CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Apartamentos',
	        'icono'   => 'bi-door-open',
	        'url'     => '?pagina=apartamentos&accion=inicio',
	        'activo'  => ($modulo === 'apartamentos'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_APARTAMENTOS, CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Solicitud Gasto',
	        'icono'   => 'bi-clipboard-check',
	        'url'     => '?pagina=solicitud_gasto&accion=inicio',
	        'activo'  => ($modulo === 'solicitud_gasto'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_SOLICITUD_GASTO, CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Presupuesto',
	        'icono'   => 'bi-calculator',
	        'url'     => '?pagina=presupuesto&accion=inicio',
	        'activo'  => ($modulo === 'presupuesto'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_PRESUPUESTO, CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Año Fiscal',
	        'icono'   => 'bi-calendar-range',
	        'url'     => '?pagina=anio_fiscal&accion=inicio',
	        'activo'  => ($modulo === 'anio_fiscal'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_ANIO_FISCAL, CONSULTAR)
	    ],
	    // --- GRUPO REPORTES ---
	    [
	        'tipo'        => 'desplegable',
	        'titulo'      => 'Reportes',
	        'icono'       => 'bi-card-checklist',
	        'id_collapse' => 'collapse_reporte',
	        'abierto'     => $es_reporte,
	        'mostrar'     => Sesiones::tienePermiso(GESTIONAR_REPORTES, CONSULTAR),
	        'submenus'    => [
	            [
	                'titulo'  => 'Reportes PDF',
	                'icono'   => 'bi-filetype-pdf',
	                'url'     => '?pagina=reportes&accion=reportes_pdf',
	                'activo'  => $sub_reporte_pdf,
	                'mostrar' => true // Hereda del padre
	            ],
	            [
	                'titulo'  => 'Estadísticos',
	                'icono'   => 'bi-clipboard-data',
	                'url'     => '?pagina=reportes&accion=reportes_estadisticos',
	                'activo'  => $sub_reporte_est,
	                'mostrar' => true
	            ]
	        ]
	    ],
	    // --- GRUPO CONFIGURACIÓN ---
	    [
	        'tipo'        => 'desplegable',
	        'titulo'      => 'Configuración',
	        'icono'       => 'bi-gear-wide-connected',
	        'id_collapse' => 'collapse_configuracion',
	        'abierto'     => $es_configuracion,
	        'mostrar'     => Sesiones::tienePermiso(GESTIONAR_CONFIGURACION, CONSULTAR),
	        'submenus'    => [
	            [
	                'titulo'  => 'Proveedores',
	                'icono'   => 'bi-truck',
	                'url'     => '?pagina=proveedores&accion=inicio',
	                'activo'  => ($modulo === 'proveedores'),
	                'mostrar' => Sesiones::tienePermiso(GESTIONAR_PROVEEDORES, CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Bancos',
	                'icono'   => 'bi-bank',
	                'url'     => '?pagina=bancos&accion=inicio',
	                'activo'  => ($modulo === 'bancos'),
	                'mostrar' => Sesiones::tienePermiso(GESTIONAR_BANCOS, CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Tipo de Gastos',
	                'icono'   => 'bi-columns-gap',
	                'url'     => '?pagina=tipo_gasto&accion=inicio',
	                'activo'  => ($modulo === 'tipo_gasto'),
	                'mostrar' => Sesiones::tienePermiso(GESTIONAR_TIPO_GASTO, CONSULTAR)
	            ]
	        ]
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Usuarios',
	        'icono'   => 'bi-person-badge-fill',
	        'url'     => '?pagina=usuario&accion=inicio',
	        'activo'  => ($modulo === 'usuario'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_USUARIOS, CONSULTAR)
	    ],
	    // --- GRUPO SEGURIDAD ---
	    [
	        'tipo'        => 'desplegable',
	        'titulo'      => 'Seguridad',
	        'icono'       => 'bi-shield-fill-check',
	        'id_collapse' => 'collapse_seguridad',
	        'abierto'     => $es_seguridad,
	        'mostrar'     => Sesiones::tienePermiso(GESTIONAR_SEGURIDAD, CONSULTAR),
	        'submenus'    => [
	            [
	                'titulo'  => 'Roles',
	                'icono'   => 'bi-person-gear',
	                'url'     => '?pagina=rol&accion=inicio',
	                'activo'  => ($modulo === 'rol'),
	                'mostrar' => Sesiones::tienePermiso(GESTIONAR_ROLES, CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Bitácora',
	                'icono'   => 'bi-journal-text',
	                'url'     => '?pagina=bitacora&accion=inicio',
	                'activo'  => ($modulo === 'bitacora'),
	                'mostrar' => Sesiones::tienePermiso(GESTIONAR_BITACORA, CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Permisos',
	                'icono'   => 'bi-key-fill',
	                'url'     => '?pagina=permisos&accion=inicio',
	                'activo'  => ($modulo === 'permisos'),
	                'mostrar' => Sesiones::tienePermiso(GESTIONAR_PERMISOS, CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Módulos',
	                'icono'   => 'bi-stack',
	                'url'     => '?pagina=modulos&accion=inicio',
	                'activo'  => ($modulo === 'modulos'),
	                'mostrar' => Sesiones::tienePermiso(GESTIONAR_MODULOS, CONSULTAR)
	            ]
	        ]
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Notificaciones',
	        'icono'   => 'bi-bell-fill',
	        'url'     => '?pagina=notificaciones&accion=inicio',
	        'activo'  => ($modulo === 'notificaciones'),
	        'mostrar' => true // General para todos los logueados
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Mantenimiento',
	        'icono'   => 'bi-tools',
	        'url'     => '?pagina=mantenimiento&accion=inicio',
	        'activo'  => ($modulo === 'mantenimiento'),
	        'mostrar' => Sesiones::tienePermiso(GESTIONAR_MANTENIMIENTO, CONSULTAR)
	    ]
	];
}
?>