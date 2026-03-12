<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap-icons/bootstrap-icons.min.css">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/css/estilos_login.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/estilos_modal_carga.css">
</head>
<body>
    <main>
        <div class="container-fluid">
            <div class="row p-5 justify-content-end">
                <div class="col-md-6 col-lg-4 col-sm-12">
                    <div class="card mt-5 shadow-lg rounded p-2 px-3">
                        <div class="card-body">
                            <form action="?pagina=login&accion=entrar" method="POST" id="form-login">
                                <h5 class="card-title text-center p-3">Iniciar sesión</h5>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                                            <input type="email" name="usuario" class="form-control" placeholder="Correo" name="correo_login" id="correo_login" aria-label="Username" aria-describedby="basic-addon1">
                                            <span class="w-100 invalid-feedback"></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-lock"></i></span>
                                            <input type="password" name="contra" class="form-control" placeholder="Contraseña" id="contra" aria-label="Username" aria-describedby="basic-addon1">
                                            <span class="w-100 invalid-feedback"></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check mb-2 text-muted">
                                            <input type="checkbox" class="form-check-input" id="checkbox_mantener_sesion" name="mantener_sesion">
                                            <label class="form-check-label" for="checkbox_mantener_sesion">Mantener sesión</label>
                                        </div>
                                    </div>                                    
                                    <div class="col-12">
                                        <a data-bs-toggle="modal" data-bs-target="#modal_recuperar_contrasenia" type="button" class="link">Recuperar Contraseña</a>
                                    </div>
                                    <?php if (!$recaptchaDeshabilitado): ?>
                                    <div class="g-recaptcha my-2 mt-4" 
                                         data-sitekey="<?php echo(CLAVE_SITIO_RECAPTCHA); ?>" 
                                         data-theme="light" 
                                         data-size="normal"
                                         data-tabindex="0"
                                         data-callback="onRecaptchaSuccess"
                                         data-expired-callback="onRecaptchaExpired"
                                         data-error-callback="onRecaptchaError">
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-12 text-center p-3 pb-0">
                                        <button type="submit" class="btn btn-primary rounded shadow" id="enviar" data-tooltip="true" title="Ingresar al sistema">Ingresar <i class="bi bi-send-fill"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
    </main>

    <?php 
    require_once 'vista/componentes/modal_carga.php';
     ?>

    <footer class="py-2 fixed-bottom" style="background-color: #0e121b;">
        <div class="text-center text-white"><h5>Junta de Condominios Edificio Haydee C.A.</h5></div>
    </footer>

    <div class="modal fade" id="modal_recuperar_contrasenia" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">Recuperar Contraseña</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="?pagina=login&accion=recuperar_contrasenia" class="row" id="form_recuperar_contra">
                        <div class="col mb-4">
                            <h6>Ingrese aquí su correo para recuperar contraseña.</h6>
                            <p>Se usará este correo para crear una nueva contraseña.</p>
                        </div>
                        <div class="col-md-11 mb-4">
                            <label class="mb-2" for="correo">Correo electrónico:</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                <input type="text" class="form-control" name="correo_recuperar" id="correo_recuperar" placeholder="Ingrese aquí su Correo electrónico" aria-label="correo" aria-describedby="basic-addon1" minlength="3" maxlength="60">
                                <span class="w-100 invalid-feedback"></span>
                            </div>
                        </div>
                        <div class="col-2 mx-auto">
                            <button class="btn btn-primary" type="submit" id="boton_recuperar">Enviar</button>
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