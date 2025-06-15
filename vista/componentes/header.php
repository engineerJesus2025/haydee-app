<header class="row justify-content-between align-items-center px-3" style="background-color: #3939a9;">
    <!-- Botón toggle -->
    <div class="header_toggle col-md-3">
        <i class='bi bi-list bi-x-lg' id="header-toggle" style="color: #fff"></i>
    </div>

    <!-- Sección de notificaciones y usuario -->
    <div class="col-md-6 d-flex justify-content-end align-items-center">
        <!-- Notificaciones -->
        <div class="col-md-1 text-right">
            <nav class="navbar navbar-expand-lg bg-none">
                <div class="container-fluid">
                    <a class="nav-link dropdown-toggle position-relative text-white" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill fs-5"></i>
                        <span id="count-label" class="position-absolute badge rounded-pill bg-danger"
                            style="top: 2px; right: 0px; font-size: 0.65rem;">
                            <?php echo count($_SESSION["notificaciones"]); ?>
                            <span class="visually-hidden">nuevas notificaciones</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="notificaciones-list">
                        <?php if (!empty($_SESSION["notificaciones"])): ?>
                            <?php foreach ($_SESSION["notificaciones"] as $notificacion): ?>
                                <li class="px-3 py-2 notification-item d-flex align-items-start gap-2"
                                    data-id="<?php echo ($notificacion['id_notificacion']); ?>"
                                    title="Click para marcar como leída" style="cursor: pointer;">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="bi bi-info-circle text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($notificacion["titulo"]); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($notificacion["descripcion"]); ?>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="px-3 py-2 text-center text-muted small">No hay notificaciones nuevas</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Usuario -->
        <div class="dropdown mt-1">
            <button class="btn bg-none text-white dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Hola, <?php echo $_SESSION["nombre_completo"] . " (" . $_SESSION["rol"] . ")"; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="?pagina=ayuda_controlador.php&accion=inicio">Ayuda</a></li>
                <li><a class="dropdown-item" href="?pagina=login_controlador.php&accion=cerrar">Salir</a></li>
            </ul>
        </div>
    </div>
</header>