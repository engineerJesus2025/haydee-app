<?php
// página actual (por defecto 'inicio')
$pagina_actual = $_GET['pagina'] ?? 'inicio';

$info_header = obtenerInfoEncabezado($pagina_actual);
$titulo_header = $info_header['titulo'];
$subtitulo_header = $info_header['subtitulo'];
?>

<header class="header d-flex justify-content-between align-items-center px-3 shadow-sm" id="header" style="height: 60px; transition: .5s;">
    
    <div class="d-flex align-items-center">
        <div class="header_toggle me-3">
            <i class='bi bi-list fs-3 transition-icon' id="header-toggle" style="cursor: pointer;"></i>
        </div>
        
        <div class="header_title">
            <h5 class="fw-bold mb-0" style="font-size: 1.1rem; line-height: 1;"><?php echo $titulo_header; ?></h5>
            <small class="text-muted" style="font-size: 0.75rem;"><?php echo $subtitulo_header; ?></small>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <button class="btn btn-link nav-link position-relative header-icon-btn" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificaciones-toggle">
                <span data-tooltip="true" title="Ver Notificaciones" class="d-inline-block">
                    <i class="bi bi-bell fs-5"></i>
                    <span id="count-label" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?php echo (empty($_SESSION['notificaciones'])) ? 'd-none' : ''; ?>">
                        <?php echo (count($_SESSION["notificaciones"] ?? []) > 99) ? '+99' : count($_SESSION["notificaciones"] ?? []); ?>
                    </span>
                </span>
            </button>
            
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 dropdown-menu-notifications p-0" aria-labelledby="notificaciones-toggle" id="notificaciones-list">
                
                <div class="dropdown-header py-3 px-3 d-flex justify-content-between align-items-center border-bottom rounded-top drop-header-bg">
                    <span class="fw-bold fs-6 drop-header-text">Notificaciones</span>
                    <a href="#" class="enlace-notf small text-decoration-none <?php echo (empty($_SESSION["notificaciones"])) ? 'd-none' : ''; ?>" id="marcar-todas-leidas" title="Marcar todas como leídas" data-tooltip="true">
                        Marcar todas como leídas
                    </a>
                </div>
                
                <div id="lista-notificaciones-items" class="drop-body-bg">
                    <?php if (!empty($_SESSION["notificaciones"])): ?>
                        <?php foreach ($_SESSION["notificaciones"] as $notificacion): ?>                                
                            <div class="notif-item d-flex align-items-start <?php echo (!empty($notificacion['leida']) && $notificacion['leida']) ? '' : 'notif-unread'; ?>">                            
                                <a href="?pagina=<?php echo $notificacion['tabla_origen']; ?>&accion=inicio&buscar=<?php echo $notificacion['id_registro_origen'] ?>"
                                    class="notif-link d-flex flex-grow-1 align-items-center text-decoration-none"
                                    title="Ir a la notificación" data-tooltip="true">
                                    
                                    <div class="notif-icon-circle bg-light text-primary me-3">
                                        <i class="bi bi-info-circle"></i>
                                    </div>                                     
                                    
                                    <div class="d-flex flex-column justify-content-center" style="width: calc(100% - 60px);">
                                        <div class="notif-title text-truncate mb-1" style="max-width: 95%;">
                                            <?php echo htmlspecialchars($notificacion["titulo"]); ?>
                                        </div>
                                        <div class="notif-description text-truncate text-muted mb-1" style="max-width: 95%;" title="<?php echo htmlspecialchars($notificacion["descripcion"]); ?>" data-tooltip="true">
                                            <?php echo htmlspecialchars($notificacion["descripcion"]); ?>
                                        </div>
                                        <div class="notif-date text-muted" data-fecha="<?php echo $notificacion['fecha']; ?>" style="font-size: 0.7rem;">
                                            <i class="bi bi-clock me-1"></i>
                                            <span class="fecha"><?php echo date('d/m/Y', strtotime($notificacion['fecha'] ?? 'now')); ?></span>
                                        </div>
                                    </div>
                                </a>
                                
                                <button type="button" class="btn notif-remove-btn shadow-none ms-2 mt-1"
                                    data-id="<?php echo ($notificacion['id_notificacion']); ?>"
                                    title="Marcar como leída" aria-label="Marcar notificación como leída" data-tooltip="true">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="notif-empty py-5 text-center" id="no-hay-notificaciones">
                            <div class="d-inline-flex justify-content-center align-items-center rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-bell-slash fs-3 text-secondary"></i>
                            </div>
                            <span class="d-block text-secondary fw-medium">No hay notificaciones nuevas</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dropdown-footer text-center border-top rounded-bottom drop-footer-bg">
                    <a href="?pagina=notificaciones&accion=inicio" class="enlace-notf fw-semibold small text-decoration-none d-block py-2" title="Ver el historial completo" data-tooltip="true">Ver el historial completo</a>
                </div>
            </div>
        </div>

        <div class="dropdown">
            <button class="btn bg-none fw-bold dropdown-toggle border-0 d-flex align-items-center px-2 py-1 rounded user-profile-btn header-user-text" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2 shadow-sm" style="width: 35px; height: 35px;">
                    <?php echo strtoupper(substr($_SESSION["nombre_completo"] ?? 'US', 0, 2)); ?>
                </div>
                <div class="d-none d-md-flex flex-column text-start" style="line-height: 1.2;">
                        <span class="fs-6" data-tooltip="true" title="<?php echo $_SESSION["nombre_completo"] ?? ''; ?>" id="nombre_usuario_sesion"><?php echo $_SESSION["nombre_completo"] ?? 'Usuario'; ?></span>
                        <span class="text-muted" data-tooltip="true" title="<?php echo $_SESSION["rol"] ?? ''; ?>" style="font-size: 0.75rem; font-weight: 600;"><?php echo $_SESSION["rol"] ?? 'Rol'; ?></span>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 rounded-3 drop-body-bg">
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center <?php echo (isset($modulo) && $modulo == 'perfil') ? 'active' : '' ?>" href="?pagina=perfil&accion=perfil" title="Ir a Mi Perfil" data-tooltip="true">
                        <i class="bi bi-person me-2 fs-5"></i> Mi perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center <?php echo (isset($modulo) && $modulo == 'ayuda') ? 'active' : '' ?>" href="?pagina=ayuda&accion=inicio" title="Ir a Ayuda" data-tooltip="true">
                        <i class="bi bi-question-circle me-2 fs-5"></i> Ayuda
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center text-danger hover-danger" href="?pagina=login&accion=cerrar" title="Cerrar Sesión" data-tooltip="true">
                        <i class="bi bi-box-arrow-right me-2 fs-5"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>