<!DOCTYPE html>
<html>

<head>
    <title>Año Fiscal | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Anio_fiscal::tiene_permiso(GESTIONAR_ANIO_FISCAL, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar" value="<?php echo Anio_fiscal::tiene_permiso(GESTIONAR_ANIO_FISCAL, MODIFICAR) ?>">
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
                        <h2>AÑOS FISCALES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Anio_fiscal::tiene_permiso(GESTIONAR_ANIO_FISCAL, REGISTRAR)) : ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_anio_fiscal">Registrar</a>
                                    </div><br>
                                <?php endif; ?>

                                <table id="tabla_anio_fiscal" class="table" style="width:97%">
                                    <thead>
                                        <tr>
                                            <th>ESTADO</th>
                                            <th>FECHA INICIO</th>
                                            <th>FECHA CIERRE</th>
                                            <th>DESCRIPCION</th>
                                            <th class="text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7"><h4>Cargando...</h4></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="modal fade" id="modal_anio_fiscal" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar Año Fiscal</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/anio_fiscal/anio_fiscal_modal.php";
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
<script type="text/javascript" src="recursos/js/validaciones/anio_fiscal_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/anio_fiscal_ajax.js"></script>

</body>

</html>