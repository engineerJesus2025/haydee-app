<?php use haydee\ayuda\Sesiones; ?>
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
    <input type="text" hidden id="permiso_eliminar"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_APARTAMENTOS, ELIMINAR) ?>">
    <input type="text" hidden id="permiso_modificar"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_APARTAMENTOS, MODIFICAR) ?>">
    <input type="text" hidden id="permiso_eliminar_habitantes"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_HABITANTES, ELIMINAR) ?>">
    <input type="text" hidden id="permiso_modificar_habitantes"
        value="<?php echo Sesiones::tienePermiso(GESTIONAR_HABITANTES, MODIFICAR) ?>">
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
                                <?php if (Sesiones::tienePermiso(GESTIONAR_APARTAMENTOS, REGISTRAR)): ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modal_apartamentos">Nuevo Apartamento</a>
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
                                <!-- <table id="tabla_apartamentos" class="table table-striped table-hover"
                                    style="width:97%">
                                    <thead>
                                        <tr>
                                            <th>NRO APARTAMENTO</th>
                                            <th>PARTICIPACION</th>
                                            <th>GAS</th>
                                            <th>AGUA</th>
                                            <th>ALQUILADO</th>
                                            <th class="text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                     
                                    </tbody>
                                </table> -->
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
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_apartamentos" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">Registrar Apartamento
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <?php
                    require_once ROOT_PATH . "/vista/apartamentos/apartamentos_modal.php";
                    ?>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_vista_previa" tabindex="-1" aria-labelledby="modal_vista_previa_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Habitantes en el Apartamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- INFO DEL APARTAMENTO ACTUAL -->
                    <div id="info_apartamento_actual" class="card mb-4 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-building"></i> Información del Apartamento
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><i class="bi bi-hash"></i> <strong>Número:</strong> <span id="apt_nro"></span></p>
                                    <p><i class="bi bi-percent"></i> <strong>Participación:</strong> <span id="apt_porcentaje"></span>%</p>
                                </div>
                                <div class="col-md-6">
                                    <p><i class="bi bi-fuel-pump"></i> <strong>Gas:</strong> <span id="apt_gas" class="badge bg-success"></span></p>
                                    <p><i class="bi bi-droplet"></i> <strong>Agua:</strong> <span id="apt_agua" class="badge bg-success"></span></p>
                                    <p><i class="bi bi-house"></i> <strong>Alquilado:</strong> <span id="apt_alquilado" class="badge bg-success"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (Sesiones::tienePermiso(GESTIONAR_HABITANTES, REGISTRAR)): ?>
                        <div class="button mb-4">
                            <button type="button" id="boton_registrar" class="btn btn-primary"
                                data-bs-toggle="modal" data-bs-target="#modal_habitantes">Nuevo Habitante</a>
                        </div><br>
                    <?php endif; ?>
                    <!-- <table id="tabla_habitantes" class="table table-striped table-hove"
                        style="width:97%">
                        <thead>
                            <tr>
                                <th>NOMBRE</th>
                                <th>APELLIDO</th>
                                <th>CEDULA</th>
                                <th>APARTAMENTO</th>
                                <th>TIPO VINCULO</th>
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
                    </table> -->
                    <div id="tabla_habitantes" class="tabla-sistema-haydee"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_habitantes" tabindex="-1" aria-labelledby="titulo_modal_habitantes"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal_habitantes">Registrar Habitante</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <?php
                    require_once ROOT_PATH . "/vista/apartamentos/habitantes_modal.php";
                    ?>

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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-building"></i> Datos del Apartamento Actual
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong><i class="bi bi-hash"></i> Número:</strong> <span id="vista_apartamento_nro"></span></p>
                                    <p><strong><i class="bi bi-percent"></i> Participación:</strong> <span id="vista_apartamento_porcentaje"></span>%</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><i class="bi bi-fuel-pump"></i> Gas:</strong> <span id="vista_apartamento_gas"></span></p>
                                    <p><strong><i class="bi bi-droplet"></i> Agua:</strong> <span id="vista_apartamento_agua"></span></p>
                                    <p><strong><i class="bi bi-house-door"></i> Alquilado:</strong> <span id="vista_apartamento_alquilado"></span></p>
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