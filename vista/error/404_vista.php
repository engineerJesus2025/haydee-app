<!DOCTYPE html>
<html>
<head>
    <title>Página no encontrada | Error 404</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/haydee-app/recursos/img/utils/logo-haydee.ico" type="image/x-icon">
    <link rel="stylesheet" href="/haydee-app/recursos/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/haydee-app/recursos/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" type="text/css" href="/haydee-app/recursos/css/estilos_generales.css">
</head>
<body id="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col d-flex flex-column min-vh-100 gris">
                <main class="col ps-md-2 pt-2 mb-5 d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
                    
                    <div class="text-center animate__animated animate__fadeIn">
                        <h1 class="display-1 fw-bold text-primary" style="font-size: 8rem;">404</h1>
                        
                        <h2 class="mb-3">¡Ups! Página no encontrada</h2>
                        
                        <p class="lead text-muted mb-4">
                            Parece que la página que estás buscando no existe,<br>
                            se ha movido o el enlace es incorrecto.
                        </p>

                        <div class="d-flex gap-3 justify-content-center">
                            <a href="#" class="btn btn-outline-secondary btn-lg px-4" onclick="window.history.back();">
                                <i class="bi bi-escape me-2"></i>
                                Volver atrás
                            </a>
                            <a href="/haydee-app/index.php" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-house-door-fill me-2"></i>
                                Ir al Inicio
                            </a>
                        </div>
                    </div>

                </main>
            </div>
        </div>
    </div>
    <?php require_once "vista/componentes/footer.php"; ?>
</body>
</html>