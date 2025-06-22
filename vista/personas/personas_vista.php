<!DOCTYPE html>
<html>
 
<head>
    <title>Personas | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Personas::tiene_permiso(GESTIONAR_PERSONAS, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar" value="<?php echo Personas::tiene_permiso(GESTIONAR_PERSONAS, MODIFICAR) ?>">
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
                        <h2>PERSONAS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Personas::tiene_permiso(GESTIONAR_PERSONAS, REGISTRAR)) : ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_personas">Registrar</a>
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

                                <table id="tabla_personas" class="table" style="width:97%">
                                    <thead>
                                        <tr>
                                            <th>NOMBRE</th>
                                            <th>APELLIDO</th>
                                            <th>CEDULA</th>
                                            <th>APARTAMENTO</th>
                                            <th class="text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7"><h4>Cargando...</h4></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="modal fade" id="modal_personas" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar Persona</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/personas/personas_modal.php";
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
                                <h5 class="modal-title">Mas detalles de la Persona</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Nombre:</strong> <span id="vista_nombre"></span></p>
                                <p><strong>Apellido:</strong> <span id="vista_apellido"></span></p>
                                <p><strong>Cedula:</strong> <span id="vista_cedula"></span></p>
                                <p><strong>Telefono:</strong> <span id="vista_telefono"></span></p>
                                <p><strong>Correo:</strong> <span id="vista_correo"></span></p>
                                <p><strong>Fecha de Nacimiento:</strong> <span id="vista_fecha_nacimiento"></span></p>
                                <p><strong>Sexo:</strong> <span id="vista_sexo"></span></p>
                                <p><strong>Apartamento:</strong> <span id="vista_apartamento"></span></p>
                                <p><strong>Tipo Vinculo:</strong> <span id="vista_vinculo"></span></p>
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

<script type="text/javascript" src="recursos/js/validaciones/personas_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/personas_ajax.js"></script>

</body>

</html>