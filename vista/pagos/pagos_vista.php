<!DOCTYPE html>
<html>

<head>
    <title>Pagos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Pagos::tiene_permiso(GESTIONAR_PAGOS, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar" value="<?php echo Pagos::tiene_permiso(GESTIONAR_PAGOS, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">

            <?php
            require_once "vista/componentes/sesion.php";
            require_once "vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once "vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>PAGOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Pagos::tiene_permiso(GESTIONAR_PAGOS, REGISTRAR)) : ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_pagos">Registrar</a>
                                    </div><br>
                                <?php endif; ?>

                                <?php if (isset($_SESSION["mensaje"])) : ?>
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

                                <table id="tabla_pagos" class="table table-dark" style="width:97%">
                                    <thead>
                                        <tr>
                                            <th>FECHA</th>
                                            <th>MONTO</th>
                                            <th>APARTAMENTO</th>
                                            <th>TASA DEL DOLAR</th>
                                            <th>ESTADO</th>
                                            <th>METODO DE PAGO</th>
                                            <th>BANCO</th>
                                            <th>REFERENCIA</th>
                                            <th>IMAGEN</th>
                                            <th>OBSERVACION</th>
                                            <th>MENSUALIDAD</th>
                                            <th class="text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7"><h4>No hay pagos registrados</h4></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="modal fade" id="modal_pagos" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar Pago</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/pagos/pagos_modal.php";
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

<script type="text/javascript" src="recursos/js/validaciones/pagos_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/pagos_ajax.js"></script>

</body>

</html>