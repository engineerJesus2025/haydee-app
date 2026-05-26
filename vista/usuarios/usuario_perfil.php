<!DOCTYPE html>
<html>
<head>
    <title>Mi Perfil | Usuarios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>

    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/contrasenias.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/esqueletos.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/estilos_switch_dark.css">
</head>

<body id="body-pd" class="body-pd">
    <input type="hidden" id="id_usuario" value="<?php echo $usuario["id_usuario"] ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">

            <?php
            require_once ROOT_PATH . "/vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column min-vh-100">

                <?php
                require_once ROOT_PATH . "/vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2 id="titulo_pagina" class="text-body">Información de Usuario</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row g-4 mb-5">
                        <div class="col-lg-4">
                            <div class="card shadow h-100 border-0 rounded-4">
                                <div class="card-body text-center pt-5 pb-4 px-4">
                                    <div id="contenedor_avatar" class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm skeleton" style="width: 110px; height: 110px; font-size: 2.8rem; font-weight: bold; letter-spacing: 2px;">
                                        </div>
                                    
                                    <h4 class="fw-bold mb-1" id="titulo_nombre">
                                        <div class="skeleton skeleton-text short mb-0" style="height: 24px; margin: 0 auto;"></div>
                                    </h4>
                                    
                                    <div class="mb-4" id="rol_container">
                                        <span>
                                            <div class="skeleton skeleton-text mt-2" style="width: 40%; margin: 0 auto;"></div>
                                        </span>
                                    </div>
                                    
                                    <div class="text-muted small mb-4 card-item p-3 rounded-3 text-start">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-calendar-check text-success me-2 fs-5" data-tooltip="true" title="Estado actual de la cuenta"></i>
                                            <div>
                                                <span class="fw-bold d-block">Estado de Cuenta</span>
                                                <span>Activo</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clock text-info me-2 fs-5" data-tooltip="true" title="Registro de tu última conexión"></i>
                                            <div class="w-100">
                                                <span class="fw-bold d-block">Último Acceso</span>
                                                <span id="ultimo_acceso">
                                                    <div class="skeleton skeleton-text mt-1" style="width: 80px; margin: 0;"></div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="text-muted opacity-25">

                                    <div class="d-grid gap-3 mt-4">
                                        <button class="btn btn-outline-warning d-flex justify-content-between align-items-center fw-medium px-4 py-2 rounded-3 shadow-sm" id="notificaciones" disabled type="button" data-bs-toggle="modal" data-bs-target="#modal_notificaciones">
                                            <span><i class="bi bi-bell me-2"></i> Notificaciones</span>
                                            <i class="bi bi-chevron-right small"></i>
                                        </button>
                                        <button class="btn btn-outline-primary d-flex justify-content-between align-items-center fw-medium px-4 py-2 rounded-3 shadow-sm" id="cambiar_contra" type="button" data-bs-toggle="modal" data-bs-target="#modal_contra">
                                            <span><i class="bi bi-key me-2"></i> Cambiar Contraseña</span>
                                            <i class="bi bi-chevron-right small"></i>
                                        </button>
                                        <a href="?pagina=login&accion=cerrar" class="btn btn-outline-danger d-flex justify-content-between align-items-center fw-medium px-4 py-2 rounded-3 shadow-sm">
                                            <span><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</span>
                                            <i class="bi bi-chevron-right small"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card shadow border-0 rounded-4">
                                <div class="card-header border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center rounded-4 bg-transparent">
                                    <h4 class="mb-0 fw-bold transicion_entrada">
                                        <i class="bi bi-person-lines-fill text-primary me-2"></i> Información Personal
                                    </h4>
                                    <button class="btn btn-outline-primary rounded-pill px-3 shadow-sm" title="Modificar Información" data-tooltip="true" id="boton_modificar" disabled>
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        <span>Cargando...</span>
                                    </button>
                                </div>
                                
                                <div class="card-body p-4">
                                    <div id="body_perfil" class="mt-2">
                                        <div class="list-group list-group-flush gap-2">
                                            
                                            <div class="list-group-item px-3 py-3 border-0 rounded-3 card-item d-flex align-items-center transition-all">
                                                <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 me-3 shadow-sm" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-person fs-4"></i>
                                                </div>
                                                <div class="w-100">
                                                    <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 0.5px; font-size: 0.75rem;">Nombre</small>
                                                    <p class="fs-5 mb-0 fw-semibold" id="p_nombre">
                                                        <span class="skeleton skeleton-text d-inline-block mt-2" style="width: 40%; margin: 0 auto;"></span>
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="list-group-item px-3 py-3 border-0 rounded-3 card-item d-flex align-items-center transition-all mt-2">
                                                <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 me-3 shadow-sm" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-person-badge fs-4"></i>
                                                </div>
                                                <div class="w-100">
                                                    <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 0.5px; font-size: 0.75rem;">Apellido</small>
                                                    <p class="fs-5 mb-0 fw-semibold" id="p_apellido">
                                                        <span class="skeleton skeleton-text d-inline-block mt-2" style="width: 40%; margin: 0 auto;"></span>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="list-group-item px-3 py-3 border-0 rounded-3 card-item d-flex align-items-center transition-all mt-2">
                                                <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 me-3 shadow-sm" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-envelope fs-4"></i>
                                                </div>
                                                <div class="w-100">
                                                    <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 0.5px; font-size: 0.75rem;">Correo Electrónico</small>
                                                    <p class="fs-5 mb-0 fw-semibold text-truncate" id="p_correo">
                                                        <span class="skeleton skeleton-text d-inline-block mt-2" style="width: 40%; margin: 0 auto;"></span>
                                                    </p>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <form hidden id="form_perfil" class="mt-2">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="nombre" class="form-label fw-bold"><i class="bi bi-person text-primary me-1"></i> Nombre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-lg card-item" id="nombre" name="nombre" required/>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="apellido" class="form-label fw-bold"><i class="bi bi-person text-primary me-1"></i> Apellido <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-lg card-item" id="apellido" name="apellido" required/>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                            <div class="col-12">
                                                <label for="correo" class="form-label fw-bold"><i class="bi bi-envelope text-primary me-1"></i> Correo Electrónico <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control form-control-lg card-item" id="correo" name="correo" required/>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                            <button type="button" class="btn btn-success px-4 rounded-pill shadow-sm" title="Guardar cambios" data-tooltip="true" id="boton_guardar">
                                                <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                                            </button>
                                            <button type="button" class="btn btn-secondary px-4 rounded-pill shadow-sm" title="Cancelar" data-tooltip="true" id="boton_cancelar">
                                                <i class="bi bi-x-lg me-1"></i> Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Sección de Ajustes de la Aplicación -->
                            <div class="card shadow border-0 rounded-4 mt-4">
                                <div class="card-header border-bottom-0 pt-4 pb-0 px-4 rounded-4 bg-transparent">
                                    <h4 class="mb-0 fw-bold transicion_entrada">
                                        <i class="bi bi-gear-fill text-primary me-2"></i> Ajustes del Sistema
                                    </h4>
                                </div>
                                <div class="card-body p-4">
                                    <div class="list-group list-group-flush gap-2">
                                        <div class="list-group-item px-3 py-3 border-0 rounded-3 card-item d-flex align-items-center justify-content-between transition-all">
                                            
                                            <div class="d-flex align-items-center">
                                                <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 me-3 shadow-sm" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-sun fs-4" id="icono_tema"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 0.5px; font-size: 0.75rem;">Tema de interfaz</small>
                                                    <p class="fs-5 mb-0 fw-semibold" id="texto_tema">Claro</p>
                                                </div>
                                            </div>

                                            <div class="theme-switch-wrapper">
                                                <label class="theme-switch" for="checkbox_tema_perfil">
                                                    <input type="checkbox" id="checkbox_tema_perfil" class="toggle-tema-global" />
                                                    <div class="slider round">
                                                        <div class="thumb">
                                                            <i class="bi bi-moon text-info thumb-icon-global" id="icono_tema"></i>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>               
                </main>
            </div>
        </div>
    </div>

    <!-- Componentes -->
    <?php
        require_once ROOT_PATH . "/vista/componentes/footer.php";
        require_once ROOT_PATH . "/vista/componentes/script.php";
        require_once 'vista/componentes/modal_carga.php';
        // Modales
        require_once ROOT_PATH . "/vista/usuarios/usuario_modal_contra.php";
    ?>
    
    <div class="modal fade" id="modal_notificaciones" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-bell-fill me-2"></i>Notificaciones
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-4">                                 
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro" data-tooltip="true" class="form-control" placeholder="Buscar notificacion...">
                        </div>
                    </div>
                    <div id="tabla_notificaciones" class="tabla-sistema-haydee"></div>
                </div>
                <div class="modal-footer border-top-0 justify-content-center">
                    <button type="button" class="btn btn-soft-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/usuario_perfil_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/usuario_perfiles.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/src/tema_global.js"></script>
</body>

</html>