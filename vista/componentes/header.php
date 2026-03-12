<?php
// Lógica para definir el título dinámico del Header
$pagina_actual = isset($_GET['pagina']) ? $_GET['pagina'] : 'inicio';
$titulo_header = "Dashboard";
$subtitulo_header = "Resumen general del condominio";

switch ($pagina_actual) {
    case 'pagos':
        $titulo_header = "Pagos";
        $subtitulo_header = "Gestión de pagos y transacciones";
        break;
    case 'gastos':
        $titulo_header = "Gastos";
        $subtitulo_header = "Registro y seguimiento de gastos";
        break;
    case 'mensualidad':
        $titulo_header = "Mensualidad";
        $subtitulo_header = "Gestión de mensualidades y cuotas";
        break;
    case 'cartelera_virtual':
        $titulo_header = "Cartelera Virtual";
        $subtitulo_header = "Anuncios y comunicados para los habitantes";
        break;
    case 'anio_fiscal':
        $titulo_header = "Año Fiscal";
        $subtitulo_header = "Configuración del año fiscal y periodos contables";
        break;
    case 'reportes':
        $titulo_header = "Reportes";
        $subtitulo_header = "Generación de reportes financieros y administrativos";
        break;
    case 'perfil':
        $titulo_header = "Mi Perfil";
        $subtitulo_header = "Información y configuración de mi cuenta";
        break;
    case 'bancos':
        $titulo_header = "Bancos";
        $subtitulo_header = "Control de cuentas bancarias";
        break;
    case 'apartamentos':
        $titulo_header = "Apartamentos y Habitantes";
        $subtitulo_header = "Gestión de apartamentos, habitantes y propietarios";
        break;
    case 'caja_chica':
        $titulo_header = "Caja Chica";
        $subtitulo_header = "Control de caja chica y gastos menores";
        break;
    case 'solicitud_gasto':
        $titulo_header = "Solicitudes de Gasto";
        $subtitulo_header = "Gestión de solicitudes de gasto y aprobaciones";
        break;
    case 'presupuesto':
        $titulo_header = "Presupuesto Mensuales";
        $subtitulo_header = "Planificación y seguimiento del presupuesto mensual";
        break;
    case 'proveedores':
        $titulo_header = "Proveedores";
        $subtitulo_header = "Gestión de proveedores y servicios";
        break;
    case 'tipo_gasto':
        $titulo_header = "Tipos de Gasto";
        $subtitulo_header = "Categorías y tipos de gastos para clasificación";
        break;
    case 'usuario':
        $titulo_header = "Usuarios";
        $subtitulo_header = "Gestión de usuarios y cuentas del sistema";
        break;
    case 'rol':
        $titulo_header = "Roles";
        $subtitulo_header = "Definición y asignación de roles y permisos";
        break;
    case 'bitacora':
        $titulo_header = "Bitácora de Actividad";
        $subtitulo_header = "Registro de acciones y eventos del sistema";
        break;
    case 'permisos':
        $titulo_header = "Permisos";
        $subtitulo_header = "Gestión de permisos y accesos para usuarios";
        break;
    case 'modulos':
        $titulo_header = "Módulos";
        $subtitulo_header = "Gestión de módulos y funcionalidades del sistema";
        break;
    case 'notificaciones':
        $titulo_header = "Notificaciones";
        $subtitulo_header = "Gestión de notificaciones y alertas para los usuarios";
        break;
    case 'mantenimiento':
        $titulo_header = "Mantenimiento";
        $subtitulo_header = "Tareas de mantenimiento y optimización del sistema";
        break;
}
?>

<header class="header d-flex justify-content-between align-items-center px-3 bg-white shadow-sm" id="header" style="height: 60px; transition: .5s;">
    
    <div class="d-flex align-items-center">
        <div class="header_toggle me-3">
            <i class='bi bi-list fs-3 text-dark' id="header-toggle" style="cursor: pointer;"></i>
        </div>
        
        <div class="header_title">
            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.1rem; line-height: 1;"><?php echo $titulo_header; ?></h5>
            <small class="text-muted" style="font-size: 0.7rem;"><?php echo $subtitulo_header; ?></small>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <button class="btn btn-link nav-link position-relative text-secondary" role="button" data-tooltip="true" title="Ver Notificaciones" data-bs-toggle="dropdown" aria-expanded="false" id="notificaciones-toggle">
                <i class="bi bi-bell fs-5"></i>
                <span id="count-label" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?php echo (count($_SESSION['notificaciones']) == 0) ? 'd-none' : ''; ?>">
                    <?php echo (count($_SESSION["notificaciones"]) > 99) ? '+99' : count($_SESSION["notificaciones"]); ?>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 dropdown-menu-notifications" aria-labelledby="notificaciones-toggle" id="notificaciones-list">
                    
                    <li class="dropdown-header py-3 px-3 d-flex justify-content-between align-items-center border-bottom">
                        <span class="fw-bold fs-6">Notificaciones</span>
                        <a href="#" class="text-primary small text-decoration-underline" id="marcar-todas-leidas">Marcar todas como leídas</a>
                    </li>
                    <div id="lista-notificaciones-items">
                        <?php if (!empty($_SESSION["notificaciones"])): ?>
                            <?php foreach ($_SESSION["notificaciones"] as $notificacion): ?>                                
                                <li class="notif-item <?php echo (!empty($notificacion['leida']) && $notificacion['leida']) ? '' : 'notif-unread'; ?>">                            
                                    <a href="?pagina=<?php echo $notificacion['tabla_origen']; ?>&accion=inicio&buscar=<?php echo $notificacion['id_registro_origen'] ?>"
                                        class="notif-link"
                                        title="Ir a la notificación">
                                        <div class="notif-icon-circle">
                                            <i class="bi bi-info-circle"></i>
                                        </div>                                     
                                        <div class="d-flex flex-column justify-content-between align-items-start">
                                            <div>
                                                <div class="notif-title">
                                                    <?php echo htmlspecialchars($notificacion["titulo"]); ?>
                                                </div>
                                                <div class="notif-description">
                                                    <?php echo htmlspecialchars($notificacion["descripcion"]); ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="notif-date"><?php echo date('d/m/Y', strtotime($notificacion['fecha'] ?? 'now')); ?></div>
                                            </div>
                                        </div>
                                    </a>
                                    <button type="button" class="btn notif-remove-btn"
                                        data-id="<?php echo ($notificacion['id_notificacion']); ?>"
                                        title="Marcar como leída" aria-label="Marcar notificación como leída">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </li>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="notif-empty" id="no-hay-notificaciones">
                                <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-50"></i>
                                <span class="d-block">No hay notificaciones nuevas</span>
                            </li>
                        <?php endif; ?>
                    </div>
                    <li class="dropdown-footer text-center border-top">
                        <a href="?pagina=notificaciones&accion=inicio" class="text-primary small text-decoration-none d-block py-2">Ver todas las notificaciones</a>
                    </li>
                </ul>
        </div>

        <div class="dropdown">
            <button class="btn bg-none text-dark fw-bold dropdown-toggle border-0 d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2" style="width: 35px; height: 35px;">
                    <?php echo strtoupper(substr($_SESSION["nombre_completo"], 0, 2)); ?>
                </div>
                <div class="d-flex flex-column text-start" style="line-height: 1.2;">
                        <span class="fs-6" data-tooltip="true" title="<?php echo $_SESSION["nombre_completo"]; ?>" id="nombre_usuario_sesion"><?php echo $_SESSION["nombre_completo"]; ?></span>
                        <span class="text-muted" data-tooltip="true" title="<?php echo $_SESSION["rol"]; ?>" style="font-size: 0.75rem;"><?php echo $_SESSION["rol"]; ?></span>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item" href="?pagina=perfil&accion=perfil"  data-tooltip="true" title="Ir a mi Perfil"><i class="bi bi-person me-2"></i> Mi perfil</a></li>
                <li><a class="dropdown-item" href="?pagina=ayuda&accion=inicio" data-tooltip="true" title="Ir a Ayuda"><i class="bi bi-question-circle me-2"></i> Ayuda</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="?pagina=login&accion=cerrar" data-tooltip="true" title="Cerrar Sesión"><i class="bi bi-box-arrow-right me-2"></i> Salir</a></li>
            </ul>
        </div>
    </div>
</header>