<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Bancos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_BANCOS, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_modificar"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_BANCOS, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">

            <?php
            require_once "vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once "vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>GESTIONAR BANCOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Sesiones::tienePermiso(GESTIONAR_BANCOS, REGISTRAR)): ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modal_banco">Nuevo banco</a>
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
                                <table id="tabla_banco" class="table table-striped table-hover" style="width:97%">
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
                                            <td colspan="6">
                                                <h4>Cargando...</h4>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Componentes -->
    <?php
        require_once "vista/componentes/footer.php";
        require_once "vista/componentes/script.php";
        require_once 'vista/componentes/modal_carga.php';
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_banco" tabindex="-1" aria-labelledby="titulo_modal"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">Registrar banco</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <?php
                    require_once "vista/bancos/bancos_modal.php";
                    ?>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="recursos/js/validaciones/bancos_validar.js"></script>
    <script type="text/javascript" src="recursos/js/consultas_ajax/bancos_ajax.js"></script>

</body>

</html>