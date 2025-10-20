<header class="row justify-content-between align-items-center px-3" style="background-color: #3939a9;">
    <div class="header_toggle col-md-3">
        <i class='bi bi-list bi-x-lg' id="header-toggle" style="color: #fff"></i>
    </div>

    <div class="col-auto row d-flex justify-content-end align-items-center">
        <div class="col-1 me-4 text-end">
            <div class="dropdown">
                <button class="btn btn-link nav-link position-relative text-white mt-2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell-fill fs-5" id="boton_notificaciones"></i>
                    <span id="count-label" class="position-absolute badge rounded-pill bg-danger <?php echo (count($_SESSION["notificaciones"]) == 0) ? 'd-none' : ''; ?>">
                        <?php echo (count($_SESSION["notificaciones"]) > 99) ? '+99' : count($_SESSION["notificaciones"]); ?>
                    </span>                        
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-0 shadow" style="width: 350px; max-height: 70vh; overflow-y: auto;" id="notificaciones-list">
                    <li class="dropdown-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Notificaciones</span>
                        <a href="#" class="text-primary small" id="marcar-todas-leidas">Marcar todas como leídas</a>
                    </li>
                    
                    <div id="lista-notificaciones-items">
                        <?php if (!empty($_SESSION["notificaciones"])): ?>
                            <?php foreach ($_SESSION["notificaciones"] as $notificacion): ?>
                                <li class="px-2 py-2 notification-item d-flex align-items-center gap-3" data-id="<?php echo ($notificacion['id_notificacion']); ?>" title="Click para marcar como leída" style="cursor: pointer;">
                                    <div class="flex-shrink-0 bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="bi bi-info-circle text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($notificacion["titulo"]); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($notificacion["descripcion"]); ?></div>
                                    </div>
                                </li>
                                <li class="dropdown-divider my-1"><hr></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="px-3 py-4 text-center text-muted" id="no-hay-notificaciones">
                                <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                No hay notificaciones nuevas
                            </li>
                        <?php endif; ?>
                    </div>
                </ul>
            </div>
        </div>

        <!-- Usuario -->
        <div class="col-auto dropdown mt-1">
            <button class="btn bg-none text-white dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false">
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