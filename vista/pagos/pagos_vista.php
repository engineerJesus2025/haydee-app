<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Pagos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once "vista/componentes/estilos.php"; ?>
</head>

<body id="body-pd" class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_PAGOS, ELIMINAR) ? 1 : 0; ?>">
    <input type="text" hidden="" id="permiso_modificar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_PAGOS, MODIFICAR) ? 1 : 0; ?>">
    
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php require_once "vista/componentes/navbar.php"; ?>
            <div class="col d-flex flex-column min-vh-100 gris">
                <?php require_once "vista/componentes/header.php"; ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>GESTIONAR PAGOS</h2>
                    </div>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Sesiones::tienePermiso(GESTIONAR_PAGOS, REGISTRAR)): ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_pagos">
                                            <?php echo $esPropietario ? 'Reportar Nuevo Pago' : 'Nuevo Pago'; ?>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION["mensaje"])): ?>
                                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                                        <span class="bi bi-exclamation-triangle"></span>
                                        <div class="mx-3"><?php echo $_SESSION["mensaje"]; ?></div>
                                    </div>
                                <?php endif; ?>

                                <table id="tabla_pagos" class="table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>FECHA</th>
                                            <th>MONTO</th>
                                            <th>MENSUALIDAD</th>
                                            <th>ESTADO</th>
                                            <th>APARTAMENTO</th>
                                            <th class="text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="6" class="text-center">Cargando...</td></tr>
                                    </tbody>
                                </table>

                                <div class="modal fade" id="modal_pagos" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable modal-xl">
                                        <div class="modal-content card shadow-sm border-primary mt-4 mb-4">
                                            <div class="modal-header card-header bg-primary text-white fw-bold">
                                                <h1 class="modal-title fs-5" id="titulo_modal">
                                                    <?php echo $esPropietario ? 'Reportar Pago' : 'Registrar Pago'; ?>
                                                </h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body bg-light">
                                                <?php require_once "vista/pagos/pagos_modal.php"; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

                

            </div>
        </div>
    </div>

    <?php 
    require_once "vista/componentes/footer.php";
    require_once "vista/componentes/script.php";
    require_once 'vista/componentes/modal_carga.php';
    ?>

    <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-labelledby="modal_vista_previa_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Vista Previa del Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4"><strong>Fecha:</strong> <span id="vista_fecha"></span></div>
                        <div class="col-md-4"><strong>Monto Mensualidad:</strong> <span id="vista_monto_mensualidad"></span></div>
                        <div class="col-md-4"><strong>Estado:</strong> <span id="vista_estado"></span></div>
                        <div class="col-md-4 mt-2"><strong>Apartamento:</strong> <span id="vista_apartamento"></span></div>
                        <div class="col-md-8 mt-2"><strong>Observación:</strong> <span id="vista_observacion" class="text-muted"></span></div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3">Desglose de Detalles</h6>
                    <div class="table-responsive">
                        <table id="tabla_detalles_pagos" class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto (Bs)</th>
                                    <th>Monto ($)</th>
                                    <th>Método de Pago</th>
                                    <th>Banco</th>
                                    <th>Referencia</th>
                                    <th class="text-center">Comprobante</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="recursos/js/validaciones/pagos_validar.js"></script>
    <script type="text/javascript" src="recursos/js/consultas_ajax/pagos_ajax.js"></script>
</body>
</html>