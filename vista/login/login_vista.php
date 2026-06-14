<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="preload" as="image" href="<?php echo URL_BASE; ?>recursos/img/utils/apartament.webp">
    
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/estilos_generales.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap-icons/bootstrap-icons.min.css">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/css/src/estilos_login.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/estilos_modal_carga.css">
</head>
<body>
    <main>
        <div class="container-fluid">
            <div class="row p-3 p-md-5 justify-content-center justify-content-md-between align-items-start min-vh-100 pb-5">
                
                <div class="col-md-6 col-lg-6 d-none d-md-block text-white animation-fade-right">
                    <h1 class="display-4 fw-bold mb-3" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.7);">
                        Bienvenido a la gestión digital de Haydee
                    </h1>
                    
                    <p class="fs-5 mb-4" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.8); line-height: 1.6; max-width: 90%;">
                        Gestione sus pagos, revise los comunicados recientes y manténgase al día con la administración de su comunidad de forma rápida y segura.
                    </p>
                    
                    <div class="d-flex gap-3">
                        <span class="badge glass-badge p-2 px-3 rounded-pill text-white">
                            <i class="bi bi-shield-lock text-success me-1"></i> Acceso Seguro
                        </span>
                        <span class="badge glass-badge p-2 px-3 rounded-pill text-white">
                            <i class="bi bi-clock-history text-warning me-1"></i> Disponible 24/7
                        </span>
                    </div>
                </div>

                <div class="col-12 col-sm-10 col-md-6 col-lg-4">
                    
                    <div class="card shadow-lg rounded p-3 p-md-4 glass-card border-0">
                        <div class="card-body p-0"> 
                            <div class="text-center mt-2 mb-4">
                                <h1 class="text-logo">Edificio Haydee</h1>
                                <p class="subtitle">Junta de Condominio</p>
                            </div>

                            <form action="?pagina=login&accion=entrar" method="POST" id="form-login">
                                <h5 class="text-center mb-4 text-secondary">Iniciar sesión</h5>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mi-input-group mb-4 shadow-sm">
                                            <div class="d-flex align-items-center w-100">
                                                <div class="px-3">
                                                    <i class="bi bi-person text-primary mi-icono fs-5"></i>
                                                </div>
                                                <div class="form-floating flex-grow-1">
                                                    <input type="email" name="usuario" class="form-control mi-input" id="correo_login" placeholder="Correo" required autofocus>
                                                    <label for="correo_login" class="text-muted">Correo electrónico</label>
                                                </div>
                                            </div>
                                            <span class="invalid-feedback w-100"></span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mi-input-group mb-4 shadow-sm">
                                            <div class="d-flex align-items-center w-100 position-relative">
                                                <div class="px-3">
                                                    <i class="bi bi-lock text-primary mi-icono fs-5"></i>
                                                </div>
                                                <div class="form-floating flex-grow-1">
                                                    <input type="password" name="contra" class="form-control mi-input pe-5" id="contra" placeholder="Contraseña" required>
                                                    <label for="contra" class="text-muted">Contraseña</label>
                                                </div>
                                                <button type="button" class="btn btn-link text-decoration-none text-muted position-absolute end-0 top-50 translate-middle-y me-2 contra-btn" id="btn-ver-contra">
                                                    <i class="bi bi-eye-fill fs-5"></i>
                                                </button>
                                            </div>
                                            <span class="invalid-feedback w-100"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                        <div class="text-muted mb-0 d-flex align-items-center gap-2">
                                            <input type="checkbox" class="mi-checkbox m-0" id="checkbox_mantener_sesion" name="mantener_sesion">
                                            <label class="form-check-label small" for="checkbox_mantener_sesion" style="cursor: pointer; padding-top: 1px;">Mantener sesión</label>
                                        </div>
                                        <a data-bs-toggle="modal" data-bs-target="#modal_recuperar_contrasenia" type="button" class="text-decoration-none small text-primary" style="cursor: pointer;">Recuperar Contraseña</a>
                                    </div>

                                    <?php if (!$recaptchaDeshabilitado): ?>
                                    <div class="col-12 d-flex justify-content-center overflow-hidden">
                                        <div class="g-recaptcha my-2" style="transform: scale(0.95); transform-origin: center;"
                                             data-sitekey="<?php echo(CLAVE_SITIO_RECAPTCHA); ?>" 
                                             data-theme="light" 
                                             data-size="normal"
                                             data-tabindex="0"
                                             data-callback="onRecaptchaSuccess"
                                             data-expired-callback="onRecaptchaExpired"
                                             data-error-callback="onRecaptchaError">
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm py-2 text-uppercase fw-bold d-flex justify-content-center align-items-center gap-2" id="enviar" data-tooltip="true" title="Ingresar al sistema">
                                            <span id="texto-boton">Ingresar</span>
                                            <i class="bi bi-box-arrow-in-right" id="icono-boton" style="font-size: 1.2rem;"></i>
                                            <span id="spinner-boton" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once 'vista/componentes/modal_carga.php'; ?>

    <footer class="py-2 fixed-bottom" style="background-color: #0e121b; z-index: 1030;">
        <div class="text-center text-white"><h6 class="mb-0 py-1" style="font-size: 0.85rem;">Junta de Condominios Edificio Haydee C.A.</h6></div>
    </footer>

    <div class="modal fade" id="modal_recuperar_contrasenia" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0 py-4 pe-4">
                    <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-bottom px-4 px-md-5 pb-5 pt-2">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 65px; height: 65px;">
                            <i class="bi bi-key fs-1"></i>
                        </div>
                        <h4 class="fw-bold text-dark" id="titulo_modal">Recuperar Contraseña</h4>
                        <p class="text-muted small mb-0 px-2">Ingrese el correo asociado a su cuenta y le enviaremos las instrucciones para restablecer el acceso.</p>
                    </div>

                    <form method="POST" action="?pagina=login&accion=recuperar_contrasenia" id="form_recuperar_contra">
                        <div class="mb-4">
                            <div class="mi-input-group shadow-sm">
                                <div class="d-flex align-items-center w-100">
                                    <div class="px-3">
                                        <i class="bi bi-envelope-at text-primary mi-icono fs-5"></i>
                                    </div>
                                    <div class="form-floating flex-grow-1">
                                        <input type="email" class="form-control mi-input" name="correo_recuperar" id="correo_recuperar" placeholder="Correo electrónico" required minlength="3" maxlength="60">
                                        <label for="correo_recuperar" class="text-muted">Correo electrónico</label>
                                    </div>
                                </div>
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2 mt-4">
                            <button class="btn btn-primary w-100 rounded-pill shadow-sm py-2 text-uppercase fw-bold d-flex justify-content-center align-items-center gap-2" type="submit" id="boton_recuperar">
                                <span id="texto-boton-recuperar">Restablecer contraseña</span>
                                <i class="bi bi-send" id="icono-boton-recuperar" style="font-size: 1.1rem;"></i>
                                <span id="spinner-boton-recuperar" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                            <button type="button" class="btn btn-link text-decoration-none text-muted w-100 rounded-pill py-2 fw-semibold btn-volver" data-bs-dismiss="modal">
                                Volver al inicio de sesión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/dependencias/sweetalert2/sweetalert2.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Patrones.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Alertas.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Peticiones.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/EstadoInputs.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Validador.js"></script>
    <?php if (!$recaptchaDeshabilitado): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/login_validar.js"></script>
</body>
</html>