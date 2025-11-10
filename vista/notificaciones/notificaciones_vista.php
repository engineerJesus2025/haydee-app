<!DOCTYPE html>
<html>

<head>
    <title>Notificaciones | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once "vista/componentes/navbar.php";
            ?>
            <div class="col d-flex flex-column  min-vh-100 gris">
                <?php
                require_once "vista/componentes/header.php";
                ?>
                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2>NOTIFICACIONES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <table id="tabla_notificaciones" class="table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>USUARIO</th>
                                            <th>ACCIÓN</th>
                                            <th>DESCRIPCIÓN</th>
                                            <th>FECHA</th>
                                            <th>LEIDA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                <?php
                require_once 'vista/componentes/modal_carga.php';
                require_once "vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>
    <?php require_once "vista/componentes/footer.php"; ?>
    <script type="text/javascript" src="recursos/js/consultas_ajax/notificaciones_ajax.js"></script>
</body>

</html>