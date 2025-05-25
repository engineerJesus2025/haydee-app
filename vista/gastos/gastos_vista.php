<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos | Inicio</title>
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar"
        value="<?php echo Gastos::tiene_permiso(GESTIONAR_GASTOS, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar"
        value="<?php echo Gastos::tiene_permiso(GESTIONAR_GASTOS, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap">

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
                        <h2>GASTOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Gastos::tiene_permiso(GESTIONAR_GASTOS, REGISTRAR)): ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modal_gastos">Registrar Gasto</a>
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

                                <div class="mb-3">
                                    <label for="selector_mes_anio" class="form-label">Filtrar por mes y año</label>
                                    <select id="selector_mes_anio" class="form-select"></select>
                                </div>

                                <table id="tabla_gastos" class="table" style="width:97%;">
                                    <thead>
                                        <tr>
                                            <th>FECHA</th>
                                            <th>MONTO</th>
                                            <th>TIPO GASTO</th>
                                            <th>TASA DOLAR</th>
                                            <th>METODO PAGO</th>
                                            <th>BANCO</th>
                                            <th>REFERENCIA</th>
                                            <th>IMAGEN</th>
                                            <th>DESCRIPCION</th>
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
                                <table id="tabla_totales" class="table table-bordered mt-4">
                                    <thead>
                                        <tr>
                                            <th>Método de Pago</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div class="modal fade" id="modal_gastos" tabindex="-1" aria-labelledby="titulo-modal"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="titulo_modal">Registrar Gasto</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                require_once "vista/gastos/gastos_modal.php";
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
                                <h5 class="modal-title">Imagen</h5>
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
</body>
<script type="text/javascript" src="recursos/js/validaciones/gastos_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/gastos_ajax.js"></script>

</html>