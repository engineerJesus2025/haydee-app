<!DOCTYPE html>
<html>

<head>
    <title>Bancos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<<<<<<< HEAD
<body>
=======
<body class="body-pd">
>>>>>>> francisco
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Banco::tiene_permiso(GESTIONAR_BANCOS, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar" value="<?php echo Banco::tiene_permiso(GESTIONAR_BANCOS, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">

            <?php
            require_once "vista/componentes/sesion.php";
<<<<<<< HEAD
            require_once "vista/componentes/header.php";
=======
            require_once "vista/componentes/navbar.php";        
>>>>>>> francisco
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
<<<<<<< HEAD
                require_once "vista/componentes/navbar.php";
=======
                require_once "vista/componentes/header.php";
>>>>>>> francisco
                ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>BANCOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Banco::tiene_permiso(GESTIONAR_BANCOS, REGISTRAR)) : ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_banco">Registrar</a>
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

                                <table id="tabla_banco" class="table" style="width:97%">
                                    <thead>
                                        <tr>
                                            <th>NOMBRE</th>
                                            <th>CODIGO</th>
                                            <th>NUMERO DE CUENTA</th>
                                            <th>TELEFONO AFILIADO</th>
                                            <th>CEDULA AFILIADA</th>
                                            <th class="text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
<<<<<<< HEAD
                                            <td colspan="7"><h4>No hay bancos registrados</h4></td>
=======
                                            <td colspan="7"><h4>Cargando...</h4></td>
>>>>>>> francisco
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="modal fade" id="modal_banco" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar banco</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/bancos/bancos_modal.php";
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

<script type="text/javascript" src="recursos/js/validaciones/bancos_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/bancos_ajax.js"></script>

</body>

</html>