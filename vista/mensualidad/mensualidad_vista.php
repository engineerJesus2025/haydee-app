<!DOCTYPE html>
<html>

<head>
    <title>Mensualidad | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>
<body class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Mensualidad::tiene_permiso(GESTIONAR_MENSUALIDAD, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar" value="<?php echo Mensualidad::tiene_permiso(GESTIONAR_MENSUALIDAD, MODIFICAR) ?>">
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
                        <h2>MENSUALIDAD</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row my-4 justify-content-center">
                        <div class="col-11">
                            <div class="card p-4 row">
                                <div class="col-12 row justify-content-between">
                                    <div class="col-4">
                                        <label for="mes_select">Mostrando Mensualidad del mes:</label>
                                        <div class="input-group my-1 mb-3">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-date"></i></span>
                                            <select class="form-select " aria-label="Default select example" name="mes_seleccionado" id="mes_select">            
                                            </select>
                                        </div>
                                        <button id="boton_eliminar" class="btn btn-outline-danger my-2">Eliminar Mensualidad</button>
                                    </div>
                                    <div class="col-3">
                                        <button class="btn btn-primary" id="boton_registrar" title="Presione para Registrar Nueva Mensualidad" type="button" data-bs-toggle="modal" data-bs-target="#modal_mensualidad">Registrar Mensualidad</button>
                                    </div>
                                </div>                                

                                <table id="tabla_mensualidad" class="table  caption-top mx-auto" style="width:97%">
                                    <caption>Listado de Mensualidades</caption>
                                    <thead>
                                        <tr>
                                            <th class="">MES/AÑO</th>
                                            <th class="">APARTAMENTO</th>
                                            <th class="">PROPIETARIO</th>
                                            <th class="">MONTO MENSUALIDAD</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4"><h4>Cargando...</h4></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button class="btn btn-outline-primary col-4 mx-auto" id="boton_editar" title="Presione para Editar Mensualidad Actual" type="button" data-bs-toggle="modal" data-bs-target="#modal_mensualidad">Editar Mensualidad Seleccionada</button>
                                <div class="modal fade" id="modal_mensualidad" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar Mensualidad</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/mensualidad/mensualidad_modal.php";
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

<script type="text/javascript" src="recursos/js/consultas_ajax/mensualidades_ajax.js"></script>
<script type="text/javascript" src="recursos/js/validaciones/mensualidad_validar.js"></script>

</body>

</html>