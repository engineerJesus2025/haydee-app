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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
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
            <div class="row p-5">
                <div class="col-md-7">
                    <!-- <img src="recursos/img/mercal_logo.png" alt="mercal logo" class="img-fluid"> -->
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="card mt-5 shadow-lg rounded">
                        <div class="card-body">
                            <form action="?pagina=login_controlador.php&accion=entrar" method="POST" id="form-login">
                                <h5 class="card-title text-center p-3">Iniciar sesión</h5>
                                <?php if(isset($_SESSION["mensaje"])): ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                                <span class="bi bi-exclamation-triangle"></span>
                                                <div>
                                                    <?php echo $_SESSION["mensaje"]; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person"></i></span>
                                            <input type="text" name="usuario" class="form-control" placeholder="Correo" name="correo" id="correo" aria-label="Username" aria-describedby="basic-addon1">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-lock"></i></span>
                                            <input type="password" name="contra" class="form-control" placeholder="Contraseña" id="contra" aria-label="Username" aria-describedby="basic-addon1">
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-center p-3">
                                        <button type="submit" class="btn btn-primary rounded shadow" id="enviar">Ingresar <i class="bi bi-send-fill"></i></button>
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
    <script src="recursos/bootstrap/js/sweetalert2.js"></script>
    <script type="text/javascript" src="recursos/js/validaciones/login_validar.js"></script>
</body>

</html>