<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Gastos | Inicio</title>
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar"
        value="<?php echo Solicitud_gasto::tiene_permiso(GESTIONAR_SOLICITUD_GASTO, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar"
        value="<?php echo Solicitud_gasto::tiene_permiso(GESTIONAR_SOLICITUD_GASTO, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap">

            <?php
            require_once "vista/componentes/sesion.php";
            require_once "vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column min-vh-100 gris">

                <?php
                require_once "vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2 mb-5">

                    <div class="page-header pt-3">
                        <h2>GESTIONAR SOLICITUD DE GASTOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Solicitud_gasto::tiene_permiso(GESTIONAR_SOLICITUD_GASTO, REGISTRAR)): ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modal_solicitud_gasto">Nueva Solicitud</a>
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
                                
                                <div class="table-responsive">
                                    <table id="tabla_solicitud_gasto" class="table table-striped table-hover" style="width: 97%;">
                                        <thead>
                                            <tr>
                                                <th>FECHA</th>
                                                <th>DESCRIPCION</th>
                                                <th>NOMBRE SOLICITANTE</th>
                                                <th>MONTO ESTIMADO</th>
                                                <th>ESTADO</th>
                                                <th>PRIORIDAD</th>
                                                <th class="text-center">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7">
                                                    <h4>Cargando...</h4>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal fade" id="modal_solicitud_gasto" tabindex="-1"
                                    aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title" id="titulo_modal">Registrar
                                                    Solicitud de
                                                    Gasto</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                require_once "vista/solicitud_gasto/solicitud_gasto_modal.php";
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
</body>
<script type="text/javascript" src="recursos/js/validaciones/solicitud_gasto_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/solicitud_gasto_ajax.js"></script>
</html>