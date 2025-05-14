<!DOCTYPE html>
<html>

<head>
    <title>Mensualidad | Inicio</title>
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

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>MENSUALIDAD</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">

                                <table id="tabla_mensualidad" class="table" style="width:97%">
                                    <thead>
                                        <tr>
                                            <th>MES/AÑO</th>
                                            <th>APARTAMENTO</th>
                                            <th>PROPIETARIO</th>
                                            <th>MONTO</th>
                                            <th>TASA DOLAR</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7"><h4>Cargando...</h4></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!-- <div class="modal fade" id="modal_usuario" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar usuario</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/usuarios/usuario_modal.php";
                                                ?>

                                            </div>
                                        </div>
                                    </div>
                                </div> -->

                            </div>
                        </div>
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

<script type="text/javascript" src="recursos/js/consultas_ajax/mensualidades_ajax.js"></script>

</body>

</html>