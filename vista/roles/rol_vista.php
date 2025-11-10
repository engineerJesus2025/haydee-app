<?php use haydee\modelo\Rol; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Roles | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>
<body id="body-pd" class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Rol::tiene_permiso(GESTIONAR_ROLES, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar" value="<?php echo Rol::tiene_permiso(GESTIONAR_ROLES, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once "vista/componentes/navbar.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">
                <?php
                require_once "vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2>GESTIONAR ROLES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3 justify-content-center">
                        <div class="col-9">
                            <div class="card p-4">
                                <?php if (Rol::tiene_permiso(GESTIONAR_ROLES, REGISTRAR)) : ?>
                                    <div class="button">
                                        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modal_roles">Nuevo Rol</a>
                                    </div><br>
                                <?php endif; ?>
                                <div class="table-responsive">
                                    <table id="tabla_roles" class="table table-striped table-hover" style="width:97%">
                                        <thead>
                                            <tr>        
                                                <th>ROL</th>
                                                <th class="text-center">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7"><h4>Cargando...</h4></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal fade modal-lg" id="modal_roles" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content"> 
                                            <div class="modal-header bg-primary text-white">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar rol</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/roles/rol_modal.php";
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
                require_once 'vista/componentes/modal_carga.php';
                require_once "vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>
    <?php require_once "vista/componentes/footer.php"; ?>
    <script type="text/javascript" src="recursos/js/validaciones/roles_validar.js"></script>
    <script type="text/javascript" src="recursos/js/consultas_ajax/roles_ajax.js"></script>
</body>

</html>