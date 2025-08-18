<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <!-- <link rel="icon" href="recursos/img/logo_ico.png"> -->
    <link rel="stylesheet" href="recursos/css/estilos_login.css">
    <link rel="stylesheet" href="recursos/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="recursos/bootstrap-icons/bootstrap-icons.min.css">    
</head>

<body>
    <?php if (isset($_GET['r'])) {
        if ($_GET['r'] == 1) {?>
            <p hidden="" id="resultado_cambio">true</p>
        <?php }
    } ?>
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
            <div class="row p-5">
                <div class="col">                    
                </div>
                <div class="col-md-6 col-lg-4 col-sm-12">
                    <div class="card mt-5 shadow-lg rounded">
                        <div class="card-body">
                            <form action="?pagina=login_controlador.php&accion=entrar" method="POST" id="form-login">
                                <h5 class="card-title text-center p-3">Iniciar sesión</h5>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                                            <input type="email" name="usuario" class="form-control" placeholder="Correo" name="correo_login" id="correo_login" aria-label="Username" aria-describedby="basic-addon1">
                                            <span class="w-100 invalid-feedback"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-lock"></i></span>
                                            <input type="password" name="contra" class="form-control" placeholder="Contraseña" id="contra" aria-label="Username" aria-describedby="basic-addon1">
                                            <span class="w-100 invalid-feedback"></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <a data-bs-toggle="modal" data-bs-target="#modal_recuperar_contrasenia" type="button" class="link">Recuperar Contraseña</a>
                                    </div>
                                    <div class="col-md-12 text-center p-3">
                                        <button type="submit" class="btn btn-primary rounded shadow" id="enviar">Ingresar <i class="bi bi-send-fill"></i></button>
                                    </div>
                                </div>
                                
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="modal_recuperar_contrasenia" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-md">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h1 class="modal-title fs-5" id="titulo_modal">Recuperar Contraseña</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="?pagina=login_controlador.php&accion=recuperar_contrasenia" class="row">
                                        <div class="col mb-4">
                                            <h6>Ingrese aquí su correo para recuperar contraseña.</h6>
                                            <p>Se usara este correo para crear una nueva contraseña.</p>
                                        </div>
                                        <div class="col-md-11 mb-4">
                                            <label class="mb-2" for="correo">Correo electrónico</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                                <input type="text" class="form-control" name="correo_recuperar" id="correo_recuperar" placeholder="Ingrese aqui su Correo electrónico" aria-label="correo" aria-describedby="basic-addon1" minlength="3" maxlength="60">
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
                </div>
            </div>
        </div>
    </main>
    <?php 
    require_once 'vista/componentes/modal_carga.php';
     ?>
    <footer class="py-3 fixed-bottom" style="background-color: #3939a9;">
        <div class="text-center text-white"><h5>Junta de Condominios Edificio Haydee C.A.</h5></div>
    </footer>
    <script src="recursos/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="recursos/bootstrap/js/sweetalert2.js"></script>
    <script type="text/javascript" src="recursos/js/validaciones/login_validar.js"></script>
</body>

</html>