<?php
use haydee\servicios\Sesiones;
use haydee\enums\Modulo;
use haydee\enums\Accion;
// Tremenda fumada lance cuando pense esto
/**
 * Función que genera y devuelve la estructura del menú de navegación.
 */
function obtenerElementosMenu($modulo, $accion) {
    //  Evaluamos condiciones de submódulos para saber qué menú debe estar abierto
    $sub_reporte_pdf = ($modulo === "reportes" && $accion === "reportes_pdf");
    $sub_reporte_est = ($modulo === "reportes" && in_array($accion, ["reportes_estadisticos", "habitantes", "ingreso_egreso"]));
    $es_reporte      = ($sub_reporte_pdf || $sub_reporte_est);

    $es_configuracion = in_array($modulo, ["proveedores", "bancos", "cuentas", "tipo_gasto"]);
    $es_seguridad     = in_array($modulo, ["rol", "bitacora", "permisos", "modulos"]); 

    //  Retornamos la matriz de elementos
    return [
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Pagos',
	        'icono'   => 'bi-cash-coin',
	        'url'     => '?pagina=pagos&accion=inicio',
	        'activo'  => ($modulo === 'pagos'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_PAGOS, Accion::CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Gastos',
	        'icono'   => 'bi-cart-plus',
	        'url'     => '?pagina=gastos&accion=inicio',
	        'activo'  => ($modulo === 'gastos'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_GASTOS, Accion::CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Caja Chica',
	        'icono'   => 'bi-bank2',
	        'url'     => '?pagina=caja_chica&accion=inicio',
	        'activo'  => ($modulo === 'caja_chica'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_CAJA_CHICA, Accion::CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Mensualidad',
	        'icono'   => 'bi-piggy-bank-fill',
	        'url'     => '?pagina=mensualidad&accion=inicio',
	        'activo'  => ($modulo === 'mensualidad'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_MENSUALIDAD, Accion::CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Cartelera Virtual',
	        'icono'   => 'bi-tv',
	        'url'     => '?pagina=cartelera_virtual&accion=inicio',
	        'activo'  => ($modulo === 'cartelera_virtual'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_CARTELERA_VIRTUAL, Accion::CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Apartamentos',
	        'icono'   => 'bi-door-open',
	        'url'     => '?pagina=apartamentos&accion=inicio',
	        'activo'  => ($modulo === 'apartamentos'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_APARTAMENTOS, Accion::CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Solicitud Gasto',
	        'icono'   => 'bi-clipboard-check',
	        'url'     => '?pagina=solicitud_gasto&accion=inicio',
	        'activo'  => ($modulo === 'solicitud_gasto'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_SOLICITUD_GASTO, Accion::CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Presupuesto',
	        'icono'   => 'bi-calculator',
	        'url'     => '?pagina=presupuesto&accion=inicio',
	        'activo'  => ($modulo === 'presupuesto'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_PRESUPUESTO, Accion::CONSULTAR)
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Año Fiscal',
	        'icono'   => 'bi-calendar-range',
	        'url'     => '?pagina=anio_fiscal&accion=inicio',
	        'activo'  => ($modulo === 'anio_fiscal'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_ANIO_FISCAL, Accion::CONSULTAR)
	    ],
	    // --- GRUPO REPORTES ---
	    [
	        'tipo'        => 'desplegable',
	        'titulo'      => 'Reportes',
	        'icono'       => 'bi-card-checklist',
	        'id_collapse' => 'collapse_reporte',
	        'abierto'     => $es_reporte,
	        'mostrar'     => Sesiones::tienePermiso(Modulo::GESTIONAR_REPORTES, Accion::CONSULTAR),
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
	        'mostrar'     => Sesiones::tienePermiso(Modulo::GESTIONAR_CONFIGURACION, Accion::CONSULTAR),
	        'submenus'    => [
	            [
	                'titulo'  => 'Proveedores',
	                'icono'   => 'bi-truck',
	                'url'     => '?pagina=proveedores&accion=inicio',
	                'activo'  => ($modulo === 'proveedores'),
	                'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_PROVEEDORES, Accion::CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Bancos',
	                'icono'   => 'bi-bank',
	                'url'     => '?pagina=bancos&accion=inicio',
	                'activo'  => ($modulo === 'bancos'),
	                'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_BANCOS, Accion::CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Cuentas',
	                'icono'   => 'bi-bank2',
	                'url'     => '?pagina=cuentas&accion=inicio',
	                'activo'  => ($modulo === 'cuentas'),
	                'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_CUENTAS, Accion::CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Tipo de Gastos',
	                'icono'   => 'bi-columns-gap',
	                'url'     => '?pagina=tipo_gasto&accion=inicio',
	                'activo'  => ($modulo === 'tipo_gasto'),
	                'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_TIPO_GASTO, Accion::CONSULTAR)
	            ]
	        ]
	    ],
	    [
	        'tipo'    => 'enlace',
	        'titulo'  => 'Usuarios',
	        'icono'   => 'bi-person-badge-fill',
	        'url'     => '?pagina=usuario&accion=inicio',
	        'activo'  => ($modulo === 'usuario'),
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_USUARIOS, Accion::CONSULTAR)
	    ],
	    // --- GRUPO SEGURIDAD ---
	    [
	        'tipo'        => 'desplegable',
	        'titulo'      => 'Seguridad',
	        'icono'       => 'bi-shield-fill-check',
	        'id_collapse' => 'collapse_seguridad',
	        'abierto'     => $es_seguridad,
	        'mostrar'     => Sesiones::tienePermiso(Modulo::GESTIONAR_SEGURIDAD, Accion::CONSULTAR),
	        'submenus'    => [
	            [
	                'titulo'  => 'Roles',
	                'icono'   => 'bi-person-gear',
	                'url'     => '?pagina=rol&accion=inicio',
	                'activo'  => ($modulo === 'rol'),
	                'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_ROLES, Accion::CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Bitácora',
	                'icono'   => 'bi-journal-text',
	                'url'     => '?pagina=bitacora&accion=inicio',
	                'activo'  => ($modulo === 'bitacora'),
	                'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_BITACORA, Accion::CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Permisos',
	                'icono'   => 'bi-key-fill',
	                'url'     => '?pagina=permisos&accion=inicio',
	                'activo'  => ($modulo === 'permisos'),
	                'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_PERMISOS, Accion::CONSULTAR)
	            ],
	            [
	                'titulo'  => 'Módulos',
	                'icono'   => 'bi-stack',
	                'url'     => '?pagina=modulos&accion=inicio',
	                'activo'  => ($modulo === 'modulos'),
	                'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_MODULOS, Accion::CONSULTAR)
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
	        'mostrar' => Sesiones::tienePermiso(Modulo::GESTIONAR_MANTENIMIENTO, Accion::CONSULTAR)
	    ]
	];
}
