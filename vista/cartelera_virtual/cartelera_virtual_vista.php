<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartelera Virtual | Inicio</title>
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body>
    <input type="text" hidden="" id="permiso_eliminar"
        value="<?php echo Cartelera_virtual::tiene_permiso(GESTIONAR_CARTELERA_VIRTUAL, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar"
        value="<?php echo Cartelera_virtual::tiene_permiso(GESTIONAR_CARTELERA_VIRTUAL, MODIFICAR) ?>">
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
                        <h2>CARTELERA VIRTUAL</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Cartelera_virtual::tiene_permiso(GESTIONAR_CARTELERA_VIRTUAL, REGISTRAR)): ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modal_cartelera">Registrar Publicación</a>
                                    </div><br>
                                <?php endif; ?>

                                <?php if (isset($_SESSION["mensaje"])): ?>
                                    <div class="row ">
                                        <div class="col-md-12">
                                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                                <span class="bi bi-exclamation-triangle"></span>
                                                <div class="mx-3">
                                                    <?php echo $_SESSION["mensaje"]; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <table id="tabla_cartelera_virtual" class="table" style="width:97%;">
                                    <thead>
                                        <tr>
                                            <th>FECHA</th>
                                            <th>TITULO</th>
                                            <th>PRIORIDAD</th>
                                            <th>AUTOR</th>
                                            <th class="text-center">ACCIONES</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5">
                                                <h4>Cargando...</h4>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="modal fade" id="modal_cartelera" tabindex="-1"
                                    aria-labelledby="titulo-modal" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="titulo-modal">Registrar Publicación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                require_once "vista/cartelera_virtual/cartelera_virtual_modal.php";
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
<script type="text/javascript" src="recursos/js/validaciones/cartelera_virtual_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/cartelera_virtual_ajax.js"></script>

</html>