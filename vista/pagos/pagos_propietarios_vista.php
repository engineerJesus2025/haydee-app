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
                                <div class="table-responsive">
                                    <table id="tabla_pagos" class="table table-striped table-hover" style="width:97%">
                                        <thead>
                                            <tr>
                                                <th>FECHA</th>
                                                <th>MONTO MENSUALIDAD</th>
                                                <th>ESTADO</th>
                                                <th>OBSERVACION</th>
                                                <th class="text-center">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7"><h4>No hay pagos registrados</h4></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal fade" id="modal_pagos" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar Pago</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/pagos/pagos_propietarios_modal.php";
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
                require_once 'vista/componentes/modal_carga.php';
                require_once "vista/componentes/footer.php";
                require_once "vista/componentes/script.php";
                ?>
                <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-labelledby="modal_vista_previa_label"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Detalles del Pago</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <strong>Fecha:</strong>
                                    <span id="vista_fecha" style="font-family: monospace;"></span>
                                    <strong>Monto Mensualidad:</strong>
                                    <span id="vista_monto_mensualidad" style="font-family: monospace;"></span>
                                    <strong>Estado:</strong>
                                    <span id="vista_estado" style="font-family: monospace;"></span>
                                    <strong>Apartamento:</strong>
                                    <span id="vista_apartamento" style="font-family: monospace;"></span>
                                    <strong>Observacion:</strong>
                                    <span id="vista_observacion" style="font-family: monospace;"></span>
                                </p>
                                <?php if (Pagos::tiene_permiso(GESTIONAR_PAGOS, REGISTRAR)) : ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_detalles_pagos">Registrar</a>
                                    </div><br>
                                <?php endif; ?>

                                
                                <table id="tabla_detalles_pagos" style="width:97%">
                                    <thead>
                                        <tr>
                                            <th>FECHA</th>
                                            <th>MONTO</th>
                                            <th>MONTO DOLAR</th>
                                            <th>TIPO DE PAGO</th>
                                            <th class="text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5"><h4>No hay detalles de pagos registrados</h4></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!--<div class="text-center mt-3">
                                    <img id="vista_imagen" src="" class="img-fluid border rounded"
                                        style="max-height: 300px;" alt="Vista previa de la imagen"
                                        onerror="this.style.display='none'; document.getElementById('mensaje_error_imagen').classList.remove('d-none');">
                                    <p id="mensaje_error_imagen" class="text-danger d-none mt-2">⚠ No se pudo cargar la
                                        imagen.</p>
                                </div>-->
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modal_detalles_pagos" tabindex="-1" aria-labelledby="titulo_modal_detalles" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h1 class="modal-title fs-5" id="titulo_modal_detalles">Registrar Detalles de Pago</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                <?php
                                require_once "vista/pagos/detalles_pagos_propietarios_modal.php";
                                ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
                <div class="modal fade" id="modal_vista_previa_detalles" tabindex="-1" aria-labelledby="modal_vista_previa_label" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Información Pago</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Fecha:</strong> <span id="vista_fecha_detalles"></span></p>
                                <p><strong>Monto:</strong> <span id="vista_monto_detalles"></span></p>
                                <p><strong>Tasa Dolar:</strong> <span id="vista_monto_dolar_detalles"></span></p>
                                <p><strong>Tipo de Pago:</strong> <span id="vista_tipo_pago_detalles"></span></p>
                                <p><strong>Banco:</strong> <span id="vista_nombre_banco_detalles"></span></p>
                                <p><strong>Referencia:</strong> <span id="vista_referencia_detalles"></span></p>

                                <div class="text-center mt-3">
                                    <img id="vista_imagen_detalles" src="" class="img-fluid border rounded"
                                        style="max-height: 300px;" alt="Vista previa de la imagen"
                                        onerror="this.style.display='none'; document.getElementById('mensaje_error_imagen_detalles').classList.remove('d-none');">
                                    <p id="mensaje_error_imagen_detalles" class="text-danger d-none mt-2">⚠ No se pudo cargar la
                                        imagen.</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

<script type="text/javascript" src="recursos/js/validaciones/detalles_pagos_propietarios_validar.js"></script>
<script type="text/javascript" src="recursos/js/validaciones/pagos_propietarios_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/pagos_propietarios_ajax.js"></script>

</body>

</html>