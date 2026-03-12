<?php use haydee\ayuda\Sesiones; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Roles | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
    <style>
        /* 1. Animación de Feedback de Fila (Seleccionar Todo) */
        .fila-resaltada td {
            animation: highlight-fade 1.2s ease-out;
        }

        @keyframes highlight-fade {
            0% { background-color: rgba(13, 110, 253, 0.2); }
            100% { background-color: transparent; }
        }

        /* 2. Animación para los Checkboxes (Efecto Pop) */
        .form-check-input {
            transition: all 0.2s cubic-bezier(0.12, 0.4, 0.29, 1.46);
            cursor: pointer;
        }

        .form-check-input:checked {
            transform: scale(1.2);
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.4);
        }

        .form-check-input:active {
            transform: scale(0.9);
        }

        /* 3. Resaltado de Permiso Individual al pasar el mouse */
        .permiso-item {
            transition: all 0.2s ease;
            border-radius: 6px;
            padding: 4px 8px;
            margin: -4px -8px; /* Ajuste para no mover el layout */
        }

        .permiso-item:hover {
            background-color: rgba(13, 110, 253, 0.08);
            color: var(--bs-primary) !important;
        }

        .permiso-item:hover .form-check-input:not(:checked) {
            border-color: var(--bs-primary);
            background-color: white;
        }

        /* Ajuste para la tabla y alineación superior */
        #tabla_permisos td {
            vertical-align: top;
        }
    </style>
</head>
<body id="body-pd" class="body-pd">
    <input type="text" hidden id="permiso_eliminar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_ROLES, ELIMINAR) ?>">
    <input type="text" hidden id="permiso_modificar" value="<?php echo Sesiones::tienePermiso(GESTIONAR_ROLES, MODIFICAR) ?>">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once ROOT_PATH . "/vista/componentes/navbar.php";
            ?>
            <div class="col d-flex flex-column  min-vh-100 gris">
                <?php
                require_once ROOT_PATH . "/vista/componentes/header.php";
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
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <?php if (Sesiones::tienePermiso(GESTIONAR_ROLES, REGISTRAR)) : ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_roles">Nuevo Rol</button>
                                    <?php else: ?>
                                        <div></div> <?php endif; ?>
                                    
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="busqueda_global" class="form-control" placeholder="Buscar Rol...">
                                    </div>
                                </div>
                                <div id="tabla_roles" class="tabla-sistema-haydee"></div>
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
    <div class="modal fade modal-xl" id="modal_roles" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"> 
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">Registrar rol</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body">
                     <?php require_once ROOT_PATH . "/vista/roles/rol_modal.php";?>
                 </div>
             </div>
         </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/roles_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/roles_ajax.js"></script>
</body>

</html>