<!DOCTYPE html>
<html>

<head>
    <title>Usuarios | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
    <!-- Una personalizacion que me invente para las contraseñas -->
    <style type="text/css">

        .contra{
            border: var(--bs-border-width) solid var(--bs-border-color);
            border-left: none;
        }
        .contra:hover{
            cursor: pointer;
            border: var(--bs-border-width) solid var(--bs-border-color);
            border-left: none;
        }

        .contra:active{
            border-color: var(--bs-border-color) !important;
        }

        #contra , #confir_contra{
            border-right: none;
        }

        #contra:focus , #confir_contra:focus{
            box-shadow: -.2rem 0 0 .18rem rgba(13,110,253,.25);
        }

        #contra:focus + .contra, #confir_contra:focus + .contra{
            box-shadow: .22rem 0 0 .18rem rgba(13,110,253,.25);
            border-color: #86b7fe;
        }

        #contra.is-invalid:focus , #confir_contra.is-invalid:focus, .was-validated .form-control:invalid:focus {
          border-color: var(--bs-form-invalid-border-color);
          box-shadow: -.2rem 0 0 .18rem rgba(var(--bs-danger-rgb),.25);
        }

        #confir_contra.is-invalid + .contra{
            border-color: var(--bs-form-invalid-border-color);
        }

        #contra.is-invalid:focus + .contra , #confir_contra.is-invalid:focus + .contra, .was-validated .form-control:invalid:focus {
          border-color: var(--bs-form-invalid-border-color);
          box-shadow: .22rem 0 0 .18rem rgba(var(--bs-danger-rgb),.25);
        }

        #contra.is-valid:focus , #confir_contra.is-valid:focus, .was-validated .form-control:valid:focus {
          border-color: var(--bs-form-valid-border-color);
          box-shadow: -.2rem 0 0 .18rem rgba(var(--bs-success-rgb),.25);
        }

        #confir_contra.is-valid + .contra{
            border-color: var(--bs-form-valid-border-color);
        }

        #contra.is-valid:focus + .contra, #confir_contra.is-valid:focus + .contra, .was-validated .form-control:valid:focus {
          border-color: var(--bs-form-valid-border-color);
          box-shadow: .22rem 0 0 .18rem rgba(var(--bs-success-rgb),.25);
        }
    </style>
</head>

<body id="body-pd" class="body-pd">
    <input type="text" hidden="" id="permiso_eliminar" value="<?php echo Usuario::tiene_permiso(GESTIONAR_USUARIOS, ELIMINAR) ?>">
    <input type="text" hidden="" id="permiso_editar" value="<?php echo Usuario::tiene_permiso(GESTIONAR_USUARIOS, MODIFICAR) ?>">
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
                        <h2>GESTIONAR USUARIOS</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <?php if (Usuario::tiene_permiso(GESTIONAR_USUARIOS, REGISTRAR)) : ?>
                                    <div class="button mb-4">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_usuario">Nuevo Usuario</a>
                                    </div><br>
                                <?php endif; ?>
                                <!-- <div class="table-responsive"> -->
                                    <table id="tabla_usuario" class="table table-striped table-hover" style="width:97%">
                                        <thead>
                                            <tr>
                                                <th>NOMBRE</th>
                                                <th>APELLIDO</th>
                                                <th>CORREO</th>
                                                <th>ROL</th>
                                                <th class="text-center">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody>                                            
                                        </tbody>
                                    </table>
                                <!-- </div> -->
                                <div class="modal fade" id="modal_usuario" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h1 class="modal-title fs-5" id="titulo_modal">Registrar usuario</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <?php
                                                require_once "vista/usuarios/usuario_modal.php";
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
                require_once "vista/componentes/footer.php";
                require_once "vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>

<script type="text/javascript" src="recursos/js/validaciones/usuario_validar.js"></script>
<script type="text/javascript" src="recursos/js/consultas_ajax/usuario_ajax.js"></script>

</body>

</html>