<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="shortcut icon" href="<?php echo URL_BASE; ?>recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/css/estilos_login.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>recursos/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/contrasenias.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container-fluid justify-content-around">
                <span class="navbar-text text-white fs-5 text-center"></span>
            </div>
        </nav>
    </header>
    <main>
        <div class="container-fluid">
            <div class="row p-5 justify-content-center">
                <div class="col-md-6 col-sm-12">
                    <div class="card mt-5 shadow-lg rounded">
                        <div class="card-body">
                            <form id="form-cambiar-contrasenia" method="POST" action="?pagina=login&accion=guardar_contrasenia">
                                <h5 class="card-title text-center p-3">Establecer nueva contraseña</h5>
                                <div class="row m-3 justify-content-center">
                                    <div class="col-md-12 mb-3">
                                        <label class="mb-2" for="contra">Nueva Contraseña <spam class="text-danger">*</spam></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control contra-input" name="contra" id="contra" placeholder="Ingrese su nueva contraseña" minlength="5" maxlength="50">
                                            <button class="btn contra-btn" type="button" title="Mostrar Contraseña" tabindex="-1">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <span class="w-100 invalid-feedback"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="mb-2" for="confir_contra">Confirmar nueva contraseña <spam class="text-danger">*</spam></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control contra-input" name="confir_contra" id="confir_contra" placeholder="Vuelva a escribir la contraseña" minlength="5" maxlength="50">
                                            <button class="btn contra-btn" type="button" title="Mostrar Contraseña" tabindex="-1">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <span class="w-100 invalid-feedback"></span>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center mt-4">
                                        <button class="btn btn-primary" id="btn-cambiar" type="submit">Cambiar contraseña</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="py-3 fixed-bottom" style="background-color: #3939a9;">
        <div class="text-center text-white"><h5>Junta de Condominios Edificio Haydee C.A.</h5></div>
    </footer>

    <!-- Scripts -->
    <script src="<?php echo URL_BASE; ?>recursos/bootstrap/js/jquery.min.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/bootstrap/js/sweetalert2.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/utilidades.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/validaciones.js"></script>
    <script src="<?php echo URL_BASE; ?>recursos/js/validaciones/cambio_contrasenia_validar.js"></script>
</body>
</html>