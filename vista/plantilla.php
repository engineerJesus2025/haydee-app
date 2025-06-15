<!DOCTYPE html>
<html>
<head>
    <title>Tu titulo</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        require_once "vista/componentes/estilos.php";
    ?>
</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
                require_once "vista/componentes/sesion.php";
                require_once "vista/componentes/header.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                    require_once "vista/componentes/navbar.php";
                ?>

                <main class="col ps-md-2 pt-2">

                    <div class="container-fluid justify-content-end">
						<a href="#" data-bs-target="#sidebar" data-bs-toggle="collapse" class="border rounded-3 p-1 text-decoration-none"><i class="bi bi-list bi-lg py-2 p-1"></i></a>
					</div>

                    <div class="page-header pt-3">
                        <h2>Tu titulo</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row">
						<!--CONTENIDO -->
                    </div>
                </main>
                <?php
				    require_once "vista/componentes/footer.php";
                    require_once "vista/componentes/script.php";
				?>
            </div>
        </div>
    </div>

</body>

</html>