<!DOCTYPE html>
<html>
<head>
    <title>Roles | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
    <style>
        /* Animación de Feedback de Fila (Seleccionar Todo) */
        .fila-resaltada td {
            animation: highlight-fade 1.2s ease-out;
        }

        @keyframes highlight-fade {
            0% { background-color: rgba(13, 110, 253, 0.2); }
            100% { background-color: transparent; }
        }

        /* Animación para los Checkboxes (Efecto Pop) */
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
            background-color: var(--text-link-notif-color);
        }

        /* Ajuste para la tabla y alineación superior */
        #tabla_permisos td {
            vertical-align: top;
        }

        #tabla_permisos .collapse.show, #tabla_permisos .collapsing{
            border-top-left-radius:initial;
            border-top-right-radius:initial
        }

        /* =========================================================
   TABLA DE PERMISOS (MÓDULO DE ROLES)
   ========================================================= */

/* 1. Cabecera destacada (Mismo color que las tablas principales) */
.tabla-permisos thead th {
    background-color: var(--ch-table-header-bg) !important;
    color: var(--ch-table-header-text) !important;
    border-bottom: 2px solid var(--ch-table-border) !important;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

/* 2. Celdas adaptativas */
.tabla-permisos tbody td {
    border-bottom: 1px solid var(--ch-table-border) !important;
    color: var(--bs-body-color);
}

/* 3. Acordeones (Botón principal) */
.tabla-permisos .accordion-button {
    background-color: var(--ch-header-hover) !important; /* Un gris/azul sutil */
    color: var(--bs-body-color) !important;
    border-radius: 6px !important;
    border: 1px solid var(--ch-table-border);
}

.tabla-permisos .accordion-button:not(.collapsed) {
    border-bottom-left-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

/* 4. Cuerpo del acordeón (Donde están los checkboxes) */
.tabla-permisos .accordion-body {
    border-top: none;
    border-bottom-left-radius: 6px;
    border-bottom-right-radius: 6px;
    color: var(--bs-body-color);
}

/* Para que los íconos de la cabecera también hereden el color blanco/claro */
.tabla-permisos thead th i {
    color: inherit !important; 
}

#tabla_permisos .accordion-collapse.collapse.show, #tabla_permisos .accordion-collapse.collapsing {
    background-color: transparent  !important;
}

/* =========================================================
   TABLA DE PERMISOS (MÓDULO DE ROLES)
   ========================================================= */

/* 1. Cabecera distintiva (Gris claro de día, Pizarra de noche) */
.tabla-permisos thead th {
    background-color: var(--ch-header-hover) !important;
    color: var(--ch-color-titulos) !important;
    border-bottom: 2px solid var(--ch-table-border) !important;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

/* 2. Celdas adaptativas (Para dar profundidad respecto al modal) */
.tabla-permisos tbody td {
    border-bottom: 1px solid var(--ch-table-border) !important;
    color: var(--bs-body-color);
}

/* 3. Acordeones (Botón principal) */
.tabla-permisos .accordion-button {
    background-color: var(--ch-card-bg) !important; /* Resalta como un elemento elevado sobre el td */
    color: var(--bs-body-color) !important;
    border-radius: 6px !important;
    border: 1px solid var(--ch-table-border);
}

.tabla-permisos .accordion-button:not(.collapsed) {
    border-bottom-left-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

/* 4. Cuerpo del acordeón (Donde están los checkboxes) */
.tabla-permisos .accordion-body {
    background-color: var(--ch-input-bg) !important; /* Efecto hundido para los checkboxes */
    border-top: none;
    border-bottom-left-radius: 6px;
    border-bottom-right-radius: 6px;
    color: var(--bs-body-color);
}

/* 5. Nombres de Módulos más legibles */
.tabla-permisos .nombre-modulo {
    /* Usamos nuestra variable pastel que brilla en oscuro y es visible en claro */
    color: var(--ch-badge-primary-text) !important; 
}

/* Para que los íconos de la cabecera también hereden el color */
.tabla-permisos thead th i {
    color: inherit !important; 
}

#tabla_permisos .accordion-collapse.collapse.show, 
#tabla_permisos .accordion-collapse.collapsing {
    background-color: var(--ch-input-bg) !important;
    margin: 0;
}

    </style>
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

                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2 id="titulo_pagina">GESTIONAR ROLES</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3 justify-content-center">
                        <div class="col-12">
                            <div class="card p-4 shadow-lg">
                                <div class="row">
                                    <div class="col-12 col-sm-6 mb-4">
                                        <?php require ROOT_PATH . "/vista/componentes/boton_nuevo.php"; ?>
                                    </div>
                                    <div class="col-12 col-sm-6 mb-4">
                                        <?php require_once ROOT_PATH . "/vista/componentes/buscador_global.php"; ?>
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
        // Modales
        require_once ROOT_PATH . "/vista/roles/roles_modal.php";
        require_once ROOT_PATH . "/vista/roles/roles_detalles.php";
    ?>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/roles_validar.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/roles_ajax.js"></script>
</body>

</html>