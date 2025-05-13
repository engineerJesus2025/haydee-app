<!DOCTYPE html>
<html>

<head>
    <title>Roles | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>
<body>
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Rol::tiene_permiso(GESTIONAR_ROLES, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar" value="<?php echo Rol::tiene_permiso(GESTIONAR_ROLES, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">            
            <?php
            require_once "vista/componentes/sesion.php";
            require_once "vista/componentes/header.php";
            ?>

            <div class="col d-flex flex-column  min-vh-100 gris">

                <?php
                require_once "vista/componentes/navbar.php";
                ?>

                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2>ROLES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3 justify-content-center">
                        <div class="col-9">
                            <div class="card p-4">
                                <?php if (Rol::tiene_permiso(GESTIONAR_ROLES, REGISTRAR)) : ?>
                                    <div class="button">
                                        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modal_roles">Registrar</a>
                                    </div><br>
                                <?php endif; ?>
                                <?php if (isset($_SESSION["mensaje"])) : ?>
                                    <div class="row">
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

                                <table id="tabla_roles" class="table" style="width:97%">
                                    <thead>
                                        <tr>        
                                            <th>ROL</th>
                                            <th>ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7"><h4>Cargando...</h4></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="modal fade modal-lg" id="modal_roles" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
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
                require_once "vista/componentes/footer.php";
                require_once "vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="recursos/js/validaciones/roles_validar.js"></script>
    <script type="text/javascript" src="recursos/js/consultas_ajax/roles_ajax.js"></script>
</body>

</html>