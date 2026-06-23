<?php
// declare(strict_types=1); 
// lei por ahi que para clases delicadas como esta es mejor definir esto para evitar comportamientos impredecibles en el backend. Por si alguien inyecta codigo el tipado estricto lo rebote

namespace haydee\servicios;

use haydee\enums\Modulo;
use haydee\enums\MetodoHttp;

class Endpoints
{
    // Constantes de Infraestructura
    public const CONF_ARCHIVO      = 'archivo';
    public const CONF_REQUIERE_AUTH = 'requiere_auth';
    public const CONF_MODULO        = 'modulo';
    public const CONF_METODOS       = 'metodos';

    /**
     * Mapa maestro de Endpoints, Seguridad y Protocolos autorizados.
     */
    public const MAPA = [
        // --- AUTENTICACIÓN Y ENTRADA ---
        'login' => [
            self::CONF_ARCHIVO      => 'login_controlador.php',
            self::CONF_REQUIERE_AUTH => false,
            self::CONF_MODULO        => null,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'inicio' => [
            self::CONF_ARCHIVO      => 'inicio_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => null, // Vista transversal común
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'perfil' => [
            self::CONF_ARCHIVO      => 'perfil_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => null, // Gestión de cuenta propia
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'ayuda' => [
            self::CONF_ARCHIVO      => 'ayuda_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => null,
            self::CONF_METODOS       => [MetodoHttp::GET]
        ],

        // --- OPERATIVO Y NEGOCIO ---
        'apartamentos' => [
            self::CONF_ARCHIVO      => 'apartamentos_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_APARTAMENTOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'mensualidad' => [
            self::CONF_ARCHIVO      => 'mensualidad_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_MENSUALIDAD,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST, MetodoHttp::PUT, MetodoHttp::DELETE]
        ],
        'pagos' => [
            self::CONF_ARCHIVO      => 'pagos_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_PAGOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],

        // --- GESTIÓN FINANCIERA Y GASTOS ---
        'presupuesto' => [
            self::CONF_ARCHIVO      => 'presupuesto_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_PRESUPUESTO,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'caja_chica' => [
            self::CONF_ARCHIVO      => 'caja_chica_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_CAJA_CHICA,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'solicitud_gasto' => [
            self::CONF_ARCHIVO      => 'solicitud_gasto_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_SOLICITUD_GASTO,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'gastos' => [
            self::CONF_ARCHIVO      => 'gastos_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_GASTOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'tipo_gasto' => [
            self::CONF_ARCHIVO      => 'tipo_gasto_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_TIPO_GASTO,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],

        // --- ENTORNOS DE INFRAESTRUCTURA ---
        'bancos' => [
            self::CONF_ARCHIVO      => 'bancos_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_BANCOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'proveedores' => [
            self::CONF_ARCHIVO      => 'proveedores_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_PROVEEDORES,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'anio_fiscal' => [
            self::CONF_ARCHIVO      => 'anio_fiscal_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_ANIO_FISCAL,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],

        // --- COMUNICACIÓN Y ALERTAS ---
        'cartelera_virtual' => [
            self::CONF_ARCHIVO      => 'cartelera_virtual_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_CARTELERA_VIRTUAL,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'notificaciones' => [
            self::CONF_ARCHIVO      => 'notificaciones_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => null, // Alerta transversal de sesión
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'suscripcion_push' => [
            self::CONF_ARCHIVO      => 'suscripcion_push_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => null, // Servicio perimetral del navegador
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],

        // --- REPORTES Y AUDITORÍA ---
        'reportes' => [
            self::CONF_ARCHIVO      => 'reportes_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_REPORTES,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'bitacora' => [
            self::CONF_ARCHIVO      => 'bitacora_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_BITACORA,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],

        // --- ADMINISTRACIÓN DE ACCESOS Y MANTENIMIENTO ---
        'usuario' => [
            self::CONF_ARCHIVO      => 'usuario_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_USUARIOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'rol' => [
            self::CONF_ARCHIVO      => 'rol_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_ROLES,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'permisos' => [
            self::CONF_ARCHIVO      => 'permisos_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_PERMISOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'modulos' => [
            self::CONF_ARCHIVO      => 'modulos_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_MODULOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'mantenimiento' => [
            self::CONF_ARCHIVO      => 'mantenimiento_controlador.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_MANTENIMIENTO,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ]
    ];

    /**
     * Mapa maestro de la API Móvil (Expo)
     * Mantiene los contratos exactos de tu aplicación móvil
     */
    public const MAPA_API = [
        'login' => [
            self::CONF_ARCHIVO       => 'login_api.php',
            self::CONF_REQUIERE_AUTH => false,
            self::CONF_MODULO        => null,
            self::CONF_METODOS       => [MetodoHttp::POST]
        ],
        'cartelera' => [
            self::CONF_ARCHIVO       => 'cartelera_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_CARTELERA_VIRTUAL,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'mensualidades' => [
            self::CONF_ARCHIVO       => 'mensualidades_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_MENSUALIDAD,
            self::CONF_METODOS       => [MetodoHttp::GET]
        ],
        'pagos' => [
            self::CONF_ARCHIVO       => 'pagos_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_PAGOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST, MetodoHttp::PUT]
        ],
        'bancos' => [
            self::CONF_ARCHIVO       => 'bancos_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_BANCOS,
            self::CONF_METODOS       => [MetodoHttp::GET]
        ],
        'apartamentos' => [
            self::CONF_ARCHIVO       => 'apartamentos_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_APARTAMENTOS,
            self::CONF_METODOS       => [MetodoHttp::GET]
        ],
        'gastos' => [
            self::CONF_ARCHIVO       => 'gastos_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => Modulo::GESTIONAR_GASTOS,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'perfil' => [
            self::CONF_ARCHIVO       => 'perfil_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => null, 
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST, MetodoHttp::PUT]
        ],
        'recuperar' => [
            self::CONF_ARCHIVO       => 'recuperar_api.php',
            self::CONF_REQUIERE_AUTH => false,
            self::CONF_MODULO        => null,
            self::CONF_METODOS       => [MetodoHttp::POST]
        ],
        'logout' => [
            self::CONF_ARCHIVO       => 'cerrar_sesion_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => null,
            self::CONF_METODOS       => [MetodoHttp::POST]
        ],
        'handshake' => [
            self::CONF_ARCHIVO       => 'handshake.php',
            self::CONF_REQUIERE_AUTH => false,
            self::CONF_MODULO        => null,
            self::CONF_METODOS       => [MetodoHttp::GET, MetodoHttp::POST]
        ],
        'refrescar' => [
            self::CONF_ARCHIVO       => 'refrescar_api.php',
            self::CONF_REQUIERE_AUTH => false, // Se valida internamente el token de refresco largo
            self::CONF_MODULO        => null,
            self::CONF_METODOS       => [MetodoHttp::POST]
        ],
        'suscripcion_push' => [
            self::CONF_ARCHIVO       => 'suscripcion_push_movil_api.php',
            self::CONF_REQUIERE_AUTH => true,
            self::CONF_MODULO        => null,
            self::CONF_METODOS       => [MetodoHttp::POST]
        ]
    ];
}