<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Mensualidad | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
</head>
<body id="body-pd" class="body-pd">
    <input type="text" hidden id="permiso_eliminar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_MENSUALIDAD, ELIMINAR) ?>">
    <input type="text" hidden id="permiso_modificar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_MENSUALIDAD, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap">

            <?php            
            require_once ROOT_PATH . "/vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once ROOT_PATH . "/vista/componentes/header.php";
                
                ?>

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>GESTIONAR MENSUALIDAD</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row my-4 justify-content-center">
                        <div class="col-11">
                            <div class="card p-4 row">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="col" hidden>
                                        <button class="btn btn-primary my-2" id="boton_registrar" type="button" data-bs-toggle="modal" data-bs-target="#modal_mensualidad" data-tooltip="true" title="Registrar Nueva mensualidad">Nueva Mensualidad</button>
                                        <p class="text-danger"></p>
                                    </div>
                                    
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro" class="form-control" placeholder="Buscar mensualidad...">
                                    </div>
                                </div>
                                <div id="tabla_mensualidad" class="tabla-sistema-haydee"></div>
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
    <div class="modal fade" id="modal_mensualidad" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">Registrar mensualidad</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">

                    <?php
                    require_once ROOT_PATH . "/vista/mensualidad/mensualidad_modal.php";
                    ?>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_mensualidades_apartamentos" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">mensualidades de los Apartamentos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="mensualidades_apartamentos" class="tabla-sistema-haydee"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/mensualidades_ajax.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/mensualidad_validar.js"></script>
</body>
</html>