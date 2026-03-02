<!DOCTYPE html>
<html>
<head>
    <title>Usuarios | Perfil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>

    <link rel="stylesheet" type="text/css" href="/haydee-app/recursos/css/contrasenias.css">
</head>

<body id="body-pd" class="body-pd">
    <input type="hidden" id="id_usuario" value="<?php echo $usuario["id_usuario"] ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">

            <?php
            require_once "vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column min-vh-100 gris">

                <?php
                require_once "vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>Información de Usuario</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3 justify-content-center">
                        <div class="col-md-9 col-lg-9">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h2 class="mb-1">
                                        <i class="bi bi-person-circle me-2"></i>
                                        Mi Perfil
                                    </h2>
                                <p class="text-muted mb-0">Información personal del usuario</p>
                              </div>              
                                <button class="btn btn-outline-primary" title="modificar Información" id="boton_modificar" disabled>
                                    <div class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; z-index: 1000">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    Cargando...
                                    <!-- <i class="bi bi-pencil me-1"></i>
                                    modificar -->
                                </button>              
                            </div>
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex align-items-center">
                                        <div class="mx-1" id="titulo_icono">
                                            <div class="card-text placeholder-glow my-3" style="width: 4rem;">
                                                <span class="placeholder w-100 rounded" style="height: 4rem"></span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h4 class="mb-1" id="titulo_nombre">    
                                                <div class="card-title placeholder-glow">
                                                  <span class="placeholder placeholder-lg w-100 rounded"></span>
                                                </div>
                                            </h4>
                                            <div class="text-white" id="titulo_rol">
                                                <div class="card-text placeholder-glow">
                                                    <span class="placeholder w-100 rounded"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body p-4">                
                                    <div class="row g-3" id="body_perfil">
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-person text-primary me-2"></i>
                                                    <small class="text-muted text-uppercase fw-bold">Nombre</small>
                                                </div>
                                                <div class="mb-0 fs-5" id="p_nombre">
                                                    <div class="card-title placeholder-glow">
                                                        <span class="placeholder placeholder-lg w-100 rounded bg-dark"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-person text-primary me-2"></i>
                                                    <small class="text-muted text-uppercase fw-bold">Apellido</small>
                                                </div>
                                                <div class="mb-0 fs-5" id="p_apellido">
                                                    <div class="card-title placeholder-glow">
                                                        <span class="placeholder placeholder-lg w-100 rounded"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="border rounded p-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-envelope text-primary me-2"></i>
                                                    <small class="text-muted text-uppercase fw-bold">Correo Electrónico</small>
                                                </div>
                                                <div class="mb-0 fs-5" id="p_correo">
                                                    <div class="card-title placeholder-glow">
                                                        <span class="placeholder placeholder-lg w-100 rounded"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="border rounded p-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-shield-check text-primary me-2"></i>
                                                    <small class="text-muted text-uppercase fw-bold">Rol en el Condominio</small>
                                                </div>
                                                <span id="spam_rol">
                                                    <div class="card-title placeholder-glow">
                                                        <span class="placeholder placeholder-lg w-100 rounded"></span>
                                                    </div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <form hidden id="form_perfil">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label htmlFor="nombre" class="form-label">
                                                <i class="bi bi-person me-1"></i>
                                                Nombre
                                                </label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" required/>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>

                                            <div class="col-md-6">
                                                <label htmlFor="apellido" class="form-label">
                                                    <i class="bi bi-person me-1"></i>
                                                    Apellido
                                                </label>
                                            <input type="text" class="form-control" id="apellido" name="apellido" required/>
                                            <span class="w-100 invalid-feedback"></span>
                                            </div>

                                            <div class="col-12">
                                                <label htmlFor="correo" class="form-label">
                                                    <i class="bi bi-envelope me-1"></i>
                                                    Correo Electrónico
                                                </label>
                                                <input type="email" class="form-control" id="correo" name="correo" required/>
                                                <span class="w-100 invalid-feedback"></span>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-4">
                                            <button type="button" class="btn btn-success" title="Presione aqui para guardar los cambios" id="boton_guardar">
                                                <i class="bi bi-check-lg me-1"></i>
                                                Guardar Cambios
                                            </button>
                                            <button type="button" class="btn btn-secondary" title="Cancelar cambios" id="boton_cancelar">
                                                <i class="bi bi-x-lg me-1"></i>
                                                Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card mt-4 shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Información Adicional
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-calendar-check text-success me-2"></i>
                                                <span class="fw-bold">Estado:</span>
                                            </div>
                                            <span class="badge bg-success">Activo</span>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-clock text-info me-2"></i>
                                                <span class="fw-bold">Último acceso:</span>
                                            </div>
                                            <span class="text-muted" id="ultimo_acceso">
                                                <div class="card-title placeholder-glow">
                                                    <span class="placeholder placeholder-lg w-100 rounded"></span>
                                                </div>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-4 shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-gear me-2"></i>
                                        Acciones de Cuenta
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-outline-warning" title="Presione aquí para ver sus notificaciones" id="notificaciones" disabled type="button" data-bs-toggle="modal" data-bs-target="#modal_notificaciones">
                                            <i class="bi bi-bell me-1"></i>
                                            Notificaciones
                                        </button>
                                        <button class="btn btn-outline-primary" title="Presione aquí para cambiar su contraseña" id="cambiar_contra" type="button" data-bs-toggle="modal" data-bs-target="#modal_contra">
                                            <i class="bi bi-key me-1"></i>
                                            Cambiar Contraseña
                                        </button>
                                        <a href="?pagina=login&accion=cerrar" class="btn btn-outline-danger">
                                            <i class="bi bi-box-arrow-right me-1"></i>
                                            Cerrar Sesión
                                        </a>
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
        require_once "vista/componentes/footer.php";
        require_once "vista/componentes/script.php";
        require_once 'vista/componentes/modal_carga.php';
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_contra" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">Cambiar Contraseña</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    require_once 'vista/usuarios/usuario_modal_contra.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_notificaciones" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">Notificaciones</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    require_once 'vista/usuarios/usuario_modal_notificaciones.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="recursos/js/validaciones/usuario_perfil_validar.js"></script>
    <script type="text/javascript" src="recursos/js/consultas_ajax/usuario_perfiles.js"></script>

</body>

</html>