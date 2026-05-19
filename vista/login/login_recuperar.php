<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="preload" as="image" href="<?php echo URL_BASE; ?>recursos/img/utils/apartament.webp">

    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap-icons/bootstrap-icons.min.css">
    
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/contrasenias.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/css/src/estilos_login.css">
</head>
<body>
    <main>
        <div class="container-fluid">
            <div class="row min-vh-100 justify-content-center align-items-center pb-5">
                <div class="col-md-6 col-lg-5 col-sm-12">
                    
                    <div class="card shadow-lg rounded p-4 glass-card border-0">
                        <div class="card-body p-0">
                            
                            <div class="text-center mt-2 mb-4">
                                <h1 class="text-logo">Edificio Haydee</h1>
                                <p class="subtitle">Junta de Condominio</p>
                            </div>

                            <form id="form-cambiar-contrasenia" method="POST" action="?pagina=login&accion=guardar_contrasenia">
                                <h4 class="text-center mb-4" style="color: #6c757d;">Establecer nueva contraseña</h4>
                                
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="mi-input-group shadow-sm">
                                            <div class="d-flex align-items-center w-100 position-relative">
                                                <div class="px-3">
                                                    <i class="bi bi-key text-primary mi-icono fs-5"></i>
                                                </div>
                                                <div class="form-floating flex-grow-1">
                                                    <input type="password" class="form-control mi-input pe-5" name="contra" id="contra" placeholder="Nueva Contraseña" minlength="5" maxlength="50" required autofocus>
                                                    <label for="contra" class="text-muted">Nueva Contraseña <span style="color: #e57373;">*</span></label>
                                                </div>
                                                <button type="button" class="btn btn-link text-decoration-none text-muted position-absolute end-0 top-50 translate-middle-y me-2 contra-btn" title="Mostrar Contraseña" tabindex="-1">
                                                    <i class="bi bi-eye-fill fs-5"></i>
                                                </button>
                                            </div>
                                            <span class="invalid-feedback w-100"></span>
                                        </div>
                                        
                                        <div class="mt-2 px-1 d-none" id="contenedor-fuerza">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-muted" style="font-size: 0.75rem;">Seguridad:</span>
                                                <span class="fw-bold" id="texto-fuerza" style="font-size: 0.75rem;">Débil</span>
                                            </div>
                                            <div class="progress" style="height: 5px; background-color: #e9ecef;">
                                                <div class="progress-bar transition-all" id="barra-fuerza" role="progressbar" style="width: 0%; transition: width 0.3s ease, background-color 0.3s ease;"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-4 mt-3">
                                        <div class="mi-input-group shadow-sm">
                                            <div class="d-flex align-items-center w-100 position-relative">
                                                <div class="px-3">
                                                    <i class="bi bi-check2-circle text-muted mi-icono fs-5" id="icono-confirmacion"></i>
                                                </div>
                                                <div class="form-floating flex-grow-1">
                                                    <input type="password" class="form-control mi-input pe-5" name="confir_contra" id="confir_contra" placeholder="Confirmar contraseña" minlength="5" maxlength="50" required>
                                                    <label for="confir_contra" class="text-muted">Confirmar contraseña <span style="color: #e57373;">*</span></label>
                                                </div>
                                                <button type="button" class="btn btn-link text-decoration-none text-muted position-absolute end-0 top-50 translate-middle-y me-2 contra-btn" title="Mostrar Contraseña" tabindex="-1">
                                                    <i class="bi bi-eye-fill fs-5"></i>
                                                </button>
                                            </div>
                                            <span class="invalid-feedback w-100"></span>
                                        </div>
                                    </div>

                                    <div class="col-12 text-center mt-4">
                                        <button class="btn btn-primary w-100 rounded-pill shadow-sm py-2 text-uppercase fw-bold" id="btn-cambiar" type="submit" data-tooltip="true" title="Cambiar Contraseña" disabled>
                                            Actualizar Contraseña <i class="bi bi-arrow-right-circle ms-1"></i>
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
    
    <footer class="py-2 fixed-bottom" style="background-color: #0e121b;">
        <div class="text-center text-white"><h6 class="mb-0 py-1">Junta de Condominios Edificio Haydee C.A.</h6></div>
    </footer>

    <script src="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/dependencias/sweetalert2/sweetalert2.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Patrones.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Alertas.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/EstadoInputs.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Validador.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/validaciones/cambio_contrasenia_validar.js"></script>
</body>
</html>