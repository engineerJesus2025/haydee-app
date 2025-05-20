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
                                            <th>AUTOR</th>
                                            <th>PRIORIDAD</th>
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
                                                <h5 class="modal-title" id="titulo_modal">Registrar Publicación</h5>
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
                <!-- Modal Vista Previa (fuera de todo el layout visual) -->
                <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-labelledby="modal_vista_previa_label"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Vista Previa de la Publicación</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Título:</strong> <span id="vista_titulo"></span></p>
                                <p><strong>Descripción:</strong></p>
                                <p id="vista_descripcion"></p>
                                <p><strong>Fecha:</strong> <span id="vista_fecha"></span></p>
                                <p><strong>Prioridad:</strong> <span id="vista_prioridad"></span></p>
                                <p><strong>Autor:</strong> <span id="vista_autor"></span></p>
                                <div class="text-center mt-3">
                                    <img id="vista_imagen" src="" class="img-fluid border rounded"
                                        style="max-height: 300px;" alt="Vista previa de la imagen"
                                        onerror="this.style.display='none'; document.getElementById('mensaje_error_imagen').classList.remove('d-none');">
                                    <p id="mensaje_error_imagen" class="text-danger d-none mt-2">⚠ No se pudo cargar la
                                        imagen.</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
<script type="text/javascript" src="recursos/js/validaciones/cartelera_virtual_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/cartelera_virtual_ajax.js"></script>

</html>