<!DOCTYPE html>
<html>

<head>
    <title>Propietarios | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar"
        value="<?php echo Propietario::tiene_permiso(GESTIONAR_PROPIETARIOS, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar"
        value="<?php echo Propietario::tiene_permiso(GESTIONAR_PROPIETARIOS, MODIFICAR) ?>">
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
                        <h2>PROPIETARIOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Propietario::tiene_permiso(GESTIONAR_PROPIETARIOS, REGISTRAR)): ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modal_propietario">Registrar</a>
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

                                <table id="tabla_propietario" class="table" style="width:97%">
                                    <thead>
                                        <tr>
                                            <th>NOMBRE</th>
                                            <th>APELLIDO</th>
                                            <th>CEDULA</th>
                                            <th>TELEFONO</th>
                                            <th>CORREO</th>
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
                                <div class="modal fade" id="modal_propietario" tabindex="-1"
                                    aria-labelledby="titulo_modal" aria_hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar Propietario
                                                </h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                require_once "vista/propietarios/propietario_modal.php";
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
    <script type="text/javascript" src="recursos/js/validaciones/propietario_validar.js"></script>
    <script type="text/javascript" src="recursos/js/consultas_ajax/propietario_ajax.js"></script>

</html>