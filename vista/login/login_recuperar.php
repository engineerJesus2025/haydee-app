<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="shortcut icon" href="recursos/img/utils/logo-haydee.ico" type="image/x-icon">    
    <link rel="stylesheet" href="recursos/css/estilos_login.css">
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="recursos/bootstrap-icons/bootstrap-icons.min.css">    
</head>

<body>
    <header>
        <nav class="navbar ">
            <div class="container-fluid justify-content-around">
                <span class="navbar-text text-white fs-5 text-center">
                </span>
            </div>
        </nav>
    </header>
    <main>
        <div class="container-fluid">
            <div class="row p-5 justify-content-center">
                <div class="col-md-6 col-sm-12">
                    <div class="card mt-5 shadow-lg rounded">
                        <div class="card-body">
                            <?php if ($resultado['estatus']) { ?>
                            <form id="form-login">
                                <h5 class="card-title text-center p-3">Procesando petición</h5>
                                <div class="row m-3 justify-content-center">
                                    <div class="col-md-12 mb-3">
                                        <label class="mb-2" for="contra">Nueva Contraseña</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control contra" name="contra" id="contra" placeholder="Introduzca la nueva Contraseña" aria-label="contra" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                                            <span class="w-100"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="mb-2" for="confir_contra">Confirmar nueva contraseña</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control confir_contra" name="confir_contra" id="confir_contra" placeholder="Vuelva a escribir la contraseña" aria-label="confir_contra" aria-describedby="basic-addon1" minlength="5" maxlength="50">
                                            <span class="w-100"></span>
                                        </div>
                                    </div>
                                    <div class="col-5 mx-auto mb-3">
                                        <button class="btn btn-primary" id="cambiar_contrasenia">Cambiar contraseña</button>
                                    </div>
                                </div> 
                            </form>
                            <?php } 
                            else {
                            ?>
                            <h4 class="my-4">Atención:</h4>
                            <div class="alert alert-danger d-flex align-items-center my-5" role="alert">
                                <i class="bi bi-exclamation-triangle me-3"></i>
                                <div>El token recibido no fue encontrado en el sistema.</div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="py-3 fixed-bottom" style="background-color: #3939a9;">
        <div class="text-center text-white"><h5>Junta de Condominios Edificio Haydee C.A.</h5></div>
    </footer>    
    <script src="recursos/bootstrap/js/sweetalert2.js"></script>
    <script type="text/javascript" src="recursos/js/validaciones/cambio_contrasenia_validar.js"></script>
</body>

</html>