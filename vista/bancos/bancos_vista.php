<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Bancos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
    <input type="text" hidden id="permiso_eliminar"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_BANCOS, ELIMINAR) ?>">
    <input type="text" hidden id="permiso_modificar"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_BANCOS, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">

            <?php
            require_once ROOT_PATH . "/vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once ROOT_PATH . "/vista/componentes/header.php";
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
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <?php if (Sesiones::tienePermiso(GESTIONAR_BANCOS, REGISTRAR)) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-tooltip="true" title="Registrar Nuevo Banco" data-bs-target="#modal_banco">Nuevo Banco</button>
                                    <?php else: ?>
                                        <div></div> <?php endif; ?>
                                    
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro" class="form-control" placeholder="Buscar banco...">
                                    </div>
                                </div>
                                <div id="tabla_banco" class="tabla-sistema-haydee"></div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Componentes -->
    <?php
        require_once ROOT_PATH . "/vista/componentes/footer.php";
        require_once ROOT_PATH . "/vista/componentes/script.php";
        require_once ROOT_PATH . "/vista/componentes/modal_carga.php";
        require_once ROOT_PATH . "/vista/componentes/boton_ayuda.php";
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
                    require_once ROOT_PATH . "/vista/bancos/bancos_modal.php";
                    ?>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/bancos_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/bancos_ajax.js"></script>

</body>

</html>