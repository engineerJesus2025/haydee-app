<!DOCTYPE html>
<html>
<head>
    <title>Apartamentos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
</head>
<body id="body-pd" class="body-pd">
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
                        <h2>GESTIONAR APARTAMENTOS Y HABITANTES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                    <?php if ($permisosVista['apartamentos']['registrar']) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-tooltip="true" title="Registrar Nuevo Apartamento" data-bs-target="#modal_apartamentos">Nuevo Apartamento</button>
                                    <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro" class="form-control" placeholder="Buscar apartamento...">
                                        </div>
                                    </div>
                                </div>
                                <div id="tabla_apartamentos" class="tabla-sistema-haydee"></div>
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
        // Modales
        require_once ROOT_PATH . "/vista/apartamentos/apartamentos_modal.php";
        require_once ROOT_PATH . "/vista/apartamentos/habitantes_modal.php";
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-labelledby="modal_vista_previa_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Habitantes en el Apartamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- INFO DEL APARTAMENTO ACTUAL -->
                    <div id="info_apartamento_actual" class="card mb-4 shadow border-0 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-5 text-white p-3 d-flex flex-column justify-content-center align-items-center" style="background-color: rgba(13, 110, 253, 0.83) !important">
                                <i class="bi bi-building fs-1 mb-2"></i>
                                <h3 class="mb-0 text-center">Apt. <span id="apt_nro"></span></h3>
                                <span class="mt-2 badge bg-light text-primary fs-6 shadow-sm">
                                    Participación: <span id="apt_porcentaje"></span>%
                                </span>
                            </div>
                            
                            <div class="col-md-7 p-4 bg-white d-flex justify-content-around align-items-center flex-wrap gap-3">
                                <div class="text-center">
                                    <i class="bi bi-fuel-pump fs-2 text-secondary mb-1"></i>
                                    <span class="d-block text-muted small fw-bold mb-2">GAS</span>
                                    <div id="apt_gas"></div>
                                </div>
                                
                                <div class="text-center">
                                    <i class="bi bi-droplet fs-2 text-secondary mb-1"></i>
                                    <span class="d-block text-muted small fw-bold mb-2">AGUA</span>
                                    <div id="apt_agua"></div>
                                </div>
                                
                                <div class="text-center">
                                    <i class="bi bi-house fs-2 text-secondary mb-1"></i>
                                    <span class="d-block text-muted small fw-bold mb-2">ESTADO</span>
                                    <div id="apt_alquilado"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($permisosVista['habitantes']['registrar']): ?>
                        <div class="button mb-4">
                            <button type="button" id="boton_registrar" class="btn btn-primary"
                                data-bs-toggle="modal" data-bs-target="#modal_habitantes" data-tooltip="true" title="Registrar Nuevo Habitante">Nuevo Habitante</a>
                        </div><br>
                    <?php endif; ?>
                    <div id="tabla_habitantes" class="tabla-sistema-haydee"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_vista_previa_habitantes" tabindex="-1" aria-labelledby="modal_vista_previa_label"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Detalles del Habitante y su Apartamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <h6>Datos del Habitante</h6>
                    <p><strong>Nombre:</strong> <span id="vista_nombre"></span></p>
                    <p><strong>Apellido:</strong> <span id="vista_apellido"></span></p>
                    <p><strong>Cédula:</strong> <span id="vista_cedula"></span></p>
                    <p><strong>Teléfono:</strong> <span id="vista_telefono"></span></p>
                    <p><strong>Correo:</strong> <span id="vista_correo"></span></p>
                    <p><strong>Fecha de Nacimiento:</strong> <span id="vista_fecha_nacimiento"></span></p>
                    <p><strong>Sexo:</strong> <span id="vista_sexo"></span></p>
                    <hr>
                    <div class="card mb-3 border-0">
                        <div class="row g-0">
                            <div class="col-md-5 text-white p-3 d-flex flex-column justify-content-center align-items-center rounded" style="background-color: rgba(13, 110, 253, 0.83) !important">
                                <i class="bi bi-building fs-1 mb-2"></i>
                                <h3 class="mb-0 text-center">Apt. <span id="vista_apartamento_nro"></span></h3>
                                <span class="mt-2 badge bg-light text-primary fs-6 shadow-sm">
                                    Participación: <span id="vista_apartamento_porcentaje"></span>%
                                </span>
                            </div>
                            
                            <div class="col-md-7 p-4 bg-white d-flex justify-content-around align-items-center flex-wrap gap-3">
                                <div class="text-center">
                                    <i class="bi bi-fuel-pump fs-2 text-secondary mb-1"></i>
                                    <span class="d-block text-muted small fw-bold mb-2">GAS</span>
                                    <div id="vista_apartamento_gas"></div>
                                </div>
                                
                                <div class="text-center">
                                    <i class="bi bi-droplet fs-2 text-secondary mb-1"></i>
                                    <span class="d-block text-muted small fw-bold mb-2">AGUA</span>
                                    <div id="vista_apartamento_agua"></div>
                                </div>
                                
                                <div class="text-center">
                                    <i class="bi bi-house fs-2 text-secondary mb-1"></i>
                                    <span class="d-block text-muted small fw-bold mb-2">ESTADO</span>
                                    <div id="vista_apartamento_alquilado"></div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/apartamentos_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/apartamentos_ajax.js"></script>
</body>
</html>