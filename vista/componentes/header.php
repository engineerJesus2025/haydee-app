<header class="row justify-content-between align-items-center px-3" style="background-color: #3939a9;">
    <div class="header_toggle col-md-3">
        <i class='bi bi-list bi-x-lg' id="header-toggle" style="color: #fff"></i>
    </div>
    <div class="col-auto row d-flex justify-content-end align-items-center">
        <div class="col-1 me-4 text-end">
            <div class="dropdown">
                <button class="btn btn-link nav-link position-relative text-white mt-2" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false" id="notificaciones-toggle">
                    <i class="bi bi-bell-fill fs-5" id="boton_notificaciones"></i>
                    <span id="count-label"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?php echo (count($_SESSION["notificaciones"]) == 0) ? 'd-none' : ''; ?>">
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
                                    <a href="?pagina=<?php echo $notificacion['nombre_modulo']; ?>_controlador.php&accion=inicio&referencia=<?php echo $notificacion['referencia'] ?>"
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
                                                <div class="notif-date"><?php echo date('d/m/Y H:i', strtotime($notificacion['fecha_creacion'] ?? 'now')); ?></div>
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
        </div>

        <!-- Usuario (sin cambios) -->
        <div class="col-auto dropdown mt-1">
            <button class="btn bg-none text-white dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" id="boton_accion_usuario">
                Hola, <?php echo $_SESSION["nombre_completo"] . " (" . $_SESSION["rol"] . ")"; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="?pagina=usuario_controlador.php&accion=perfil">Mi perfil</a></li>
                <li><a class="dropdown-item" href="?pagina=ayuda_controlador.php&accion=inicio">Ayuda</a></li>
                <li><a class="dropdown-item" href="?pagina=login_controlador.php&accion=cerrar">Salir</a></li>
            </ul>
        </div>
    </div>
</header>