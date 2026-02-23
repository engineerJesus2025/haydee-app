<header class="header d-flex justify-content-between align-items-center px-3 bg-white shadow-sm" id="header" style="height: 60px; transition: .5s;">
    
    <div class="header_toggle">
        <i class='bi bi-list fs-3 text-dark' id="header-toggle" style="cursor: pointer;"></i>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <button class="btn btn-link nav-link position-relative text-secondary" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificaciones-toggle">
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
                                    <a href="?pagina=<?php echo $notificacion['tabla_origen']; ?>_controlador.php&accion=inicio&buscar=<?php echo $notificacion['id_registro_origen'] ?>"
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
                        <a href="?pagina=notificaciones_controlador.php&accion=inicio" class="text-primary small text-decoration-none d-block py-2">Ver todas las notificaciones</a>
                    </li>
                </ul>
        </div>

        <div class="dropdown">
            <button class="btn bg-none text-dark fw-bold dropdown-toggle border-0 d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2" style="width: 35px; height: 35px;">
                    <?php echo strtoupper(substr($_SESSION["nombre_completo"], 0, 2)); ?>
                </div>
                <div class="d-flex flex-column text-start" style="line-height: 1.2;">
                        <span class="fs-6"><?php echo $_SESSION["nombre_completo"]; ?></span>
                        <span class="text-muted" style="font-size: 0.75rem;"><?php echo $_SESSION["rol"]; ?></span>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item" href="?pagina=perfil_controlador.php&accion=perfil"><i class="bi bi-person me-2"></i> Mi perfil</a></li>
                <li><a class="dropdown-item" href="?pagina=ayuda_controlador.php&accion=inicio"><i class="bi bi-question-circle me-2"></i> Ayuda</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="?pagina=login_controlador.php&accion=cerrar"><i class="bi bi-box-arrow-right me-2"></i> Salir</a></li>
            </ul>
        </div>
    </div>
</header>