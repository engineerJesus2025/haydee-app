<!DOCTYPE html>
<html>
<head>
    <title>Mensualidad | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
    <style>
         .fila-mensualidad-asignar {
            transition: all 0.2s ease-in-out;
            border-left: 3px solid transparent; 
        }
        .fila-mensualidad-asignar:hover {
            background-color: var(--ch-table-row-hover) !important; 
            border-left-color: var(--ch-badge-primary-border); 
            transform: translateX(2px); 
        }

        /* Sombra adaptativa */
        .shadow-xs {
            box-shadow: var(--ch-shadow-sm) !important;
        }

        /* Adaptación de la columna de Exoneración al Tema */
        .bg-exoneracion {
            background-color: var(--ch-badge-warning-bg) !important;
        }

        /* Botón del Asistente adaptado a tus variables warning */
        .btn-asistente {
            background-color: var(--ch-badge-warning-bg) !important;
            color: var(--ch-badge-warning-text) !important;
            border-color: var(--ch-badge-warning-border) !important;
            transition: all 0.2s ease;
        }
        .btn-asistente:hover {
            background-color: var(--ch-badge-warning-border) !important;
            color: var(--body-bg) !important;
        }

        /* Input de descuento adaptado al tema oscuro/claro */
        .input-descuento {
            background-color: var(--ch-input-bg) !important;
            color: var(--ch-table-text) !important;
            border-color: var(--ch-badge-warning-border) !important;
        }
        .input-descuento:focus {
            box-shadow: 0 0 0 0.2rem var(--ch-badge-warning-bg) !important;
            border-color: var(--ch-badge-warning-text) !important;
        }

        /* Cabeceras, footer y bordes fusionados con Tabulator */
        .tabla-asignacion-mensualidad thead th,
        .tabla-asignacion-mensualidad tfoot td {
            background-color: var(--ch-table-header-bg) !important;
            color: var(--ch-table-header-text) !important;
            border-color: var(--ch-table-border) !important;
        }
        .tabla-asignacion-mensualidad tbody td {
            border-color: var(--ch-table-border) !important;
        }

        /* Asegurar flechas en inputs numéricos */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            opacity: 1; 
        }


        /* Asegurar comportamiento flex nativo en la celda de la exoneración */
        .tabla-asignacion-mensualidad tbody tr {
            display: table-row; /* Mantiene la fila con comportamiento estándar */
        }


        /* Forzar al contenedor del input a anular cualquier margen de Bootstrap */
        .tabla-asignacion-mensualidad .input-group {
            margin: 0 auto !important;
            transform: translateY(1px); /* Ajuste milimétrico óptico si fuera necesario */
        }

        @media (min-width: 992px) {
            #modal_mensualidad .table-responsive {
                overflow-x: hidden !important;
            }
        }

        /* La cabecera mantiene su jerarquía fuerte (Oscuro en ambos modos) */
        .tabla-asignacion-mensualidad thead th {
            background-color: var(--ch-table-header-bg) !important;
            color: var(--ch-table-header-text) !important;
            border-color: var(--ch-table-border) !important;
        }

        .tabla-asignacion-mensualidad tfoot td {
            background-color: var(--ch-table-row-hover) !important;
            color: var(--ch-color-titulos) !important;
            border-color: var(--ch-table-border) !important;
        }

        [data-bs-theme="dark"] .tabla-asignacion-mensualidad tfoot td{
            background-color: var(--ch-table-header-bg) !important;
            color: var(--ch-table-header-text) !important;
            border-color: var(--ch-table-border) !important;
        }

        /* Corrección de contraste para los totales en el footer */
        .tabla-asignacion-mensualidad tfoot td .text-warning {
            color: var(--bs-orange) !important; /* Un tono más legible sobre gris claro que el amarillo puro */
        }

        .text-dark-adaptativo {
            color: var(--ch-table-text) !important;
        }

        /* Modal de exoneración */

        .reserva-card-exoneracion {
            background: var(--ch-badge-warning-bg, #fff8e1) !important;
            border: 2px solid var(--ch-badge-warning-border, #ffc107) !important;
            border-radius: 0.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            color: var(--ch-table-text) !important;
        }

        .contenedor-bloques-exoneracion {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 0.6rem;
            align-items: start;
        }

        .bloque-exoneracion {
            min-width: 0;
            margin: 0 !important;
        }

        .exoneracion-item {
            padding: 0.3rem 0.5rem !important;
            line-height: 1.2;
        }

        .exoneracion-item .form-check-input {
            margin-top: 0 !important;
            flex-shrink: 0;
        }

        .modal-dialog:has(.contenedor-bloques-exoneracion) {
    max-width: min(1200px, 92vw) !important;
    width: min(1200px, 92vw) !important;
}

/* Grid auto-ajustable: 1→4 columnas según ancho disponible.
   minmax(300px, 1fr) asegura que cada bloque tenga ancho para texto legible */
.contenedor-bloques-exoneracion {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 0.75rem;
    align-items: start;
}

/* Reserva: tarjeta aislada, primera, ancho completo, NO forma bloque */
.reserva-card-exoneracion {
    background: var(--ch-badge-warning-bg, #fff8e1) !important;
    border: 2px solid var(--ch-badge-warning-border, #ffc107) !important;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    color: var(--ch-table-text) !important;
    margin-bottom: 0.75rem;
}

/* Item: padding compacto (reduce altura) SIN tocar font-size (legibilidad) */
.exoneracion-item {
    padding: 0.4rem 0.65rem !important;
    line-height: 1.3;
}
.exoneracion-item .form-check-input {
    margin-top: 0 !important;
    flex-shrink: 0;
}

/* ===================== Modal de exoneración ===================== */

/* Solo padding. El ancho se controla vía opciones.width en JS.
   `!important` porque Swal aplica estilos inline. */
.swal-exoneracion {
    padding: 1.25rem !important;
}

.swal-exoneracion .swal2-html-container {
    margin: 0 !important;
    padding: 0 !important;
}

.reserva-card-exoneracion {
    background: var(--ch-badge-warning-bg, #fff8e1) !important;
    border: 2px solid var(--ch-badge-warning-border, #ffc107) !important;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    color: var(--ch-table-text) !important;
    margin-bottom: 0.75rem;
}

.contenedor-bloques-exoneracion {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 0.75rem;
    align-items: start;
}

.bloque-exoneracion {
    min-width: 0;
    margin: 0 !important;
}

.exoneracion-item {
    padding: 0.4rem 0.65rem !important;
    line-height: 1.3;
}
.exoneracion-item .form-check-input {
    margin-top: 0 !important;
    flex-shrink: 0;
}

.exoneracion-footer {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: var(--ch-card-bg, #fff);
    border-top: 2px solid var(--ch-badge-warning-border, #ffc107);
    padding: 0.6rem 0.5rem;
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.08);
}

.swal-exoneracion .swal2-html-container {
    max-height: 65vh;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 0.25rem; /* deja respirar al scrollbar */
}


/* ===================== Modal de exoneración ===================== */

/* Popup: flex column con tope de altura. Evita scroll en .swal2-container */
.swal-exoneracion {
    display: flex !important;
    flex-direction: column !important;
    max-height: 90vh !important;
    overflow: hidden !important;
    padding: 1rem 1.25rem !important;
}

/* Título y acciones no se encogen: reservan su espacio */
.swal-exoneracion .swal2-title,
.swal-exoneracion .swal2-actions {
    flex-shrink: 0 !important;
}

/* Compactar el título (viene como <div class="fs-4"> desde Alertas.js) */
.swal-exoneracion .swal2-title {
    padding: 0 0 0.25rem 0 !important;
    margin: 0 !important;
    font-size: 1rem !important;
}
.swal-exoneracion .swal2-title .fs-4 {
    font-size: 1.1rem !important;
    margin-bottom: 0 !important;
}

/* Única zona con scroll interno.
   min-height: 0 es indispensable: sin él flexbox no encoge el hijo
   y el popup termina excediendo max-height → reaparece el scroll externo. */
.swal-exoneracion .swal2-html-container {
    flex: 1 1 auto !important;
    min-height: 0 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    max-height: none !important;
    margin: 0 !important;
    padding: 0 0.25rem 0 0 !important;
}

/* Compactar subtítulo */
.swal-exoneracion .swal2-html-container > p.text-muted:first-child {
    font-size: 0.82rem !important;
    margin-bottom: 0.5rem !important;
    line-height: 1.3 !important;
}

/* Reserva: tarjeta aislada */
.reserva-card-exoneracion {
    background: var(--ch-badge-warning-bg, #fff8e1) !important;
    border: 2px solid var(--ch-badge-warning-border, #ffc107) !important;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    color: var(--ch-table-text) !important;
    margin-bottom: 0.75rem;
}

.contenedor-bloques-exoneracion {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 0.75rem;
    align-items: start;
}

.bloque-exoneracion {
    min-width: 0;
    margin: 0 !important;
}

.exoneracion-item {
    padding: 0.4rem 0.65rem !important;
    line-height: 1.3;
}
.exoneracion-item .form-check-input {
    margin-top: 0 !important;
    flex-shrink: 0;
}

/* Footer sticky con totales */
.exoneracion-footer {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: var(--ch-card-bg, #fff);
    border-top: 2px solid var(--ch-badge-warning-border, #ffc107);
    padding: 0.6rem 0.5rem;
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.08);
}

    </style>
</head>
<body id="body-pd" class="body-pd">
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
                        <h2 id="titulo_pagina">GESTIONAR MENSUALIDAD</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row my-4 justify-content-center">
                        <div class="col-11">
                            <div class="card p-4 shadow-lg">

                                <div class="row mb-4 align-items-center justify-content-between">
                                    <div id="columna_boton_nuevo" class="col-6 col-sm-6 d-flex flex-column align-items-start">
                                        <?php require ROOT_PATH . "/vista/componentes/boton_nuevo.php"; ?>
                                    </div>

                                    <div id="aviso_no_mensualidades" class="col-12 col-sm-6 border rounded-3 py-2 px-3 d-none align-items-center shadow-sm transicion_entrada" style="background-color: var(--ch-badge-info-bg); color: var(--ch-badge-info-text); border-color: var(--ch-badge-info-border);">
                                        <i class="bi bi-info-circle me-3 fs-4" style="color: var(--ch-badge-info-text);"></i>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold mb-0.5" style="font-size: 0.95rem;">Módulo al día</span>
                                            <span class="small opacity-90" style="font-size: 0.85rem;">No hay mensualidades pendientes por generar.</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <?php require_once ROOT_PATH . "/vista/componentes/buscador_global.php"; ?>
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
        // Modales
        require_once ROOT_PATH . "/vista/mensualidad/mensualidad_modal.php";
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_mensualidades_apartamentos" tabindex="-1" aria-labelledby="titulo_modal_mensualidad" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog modal-dialog-scrollable-scrollable">
            <div class="modal-content card shadow-lg">
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title fw-bold" id="titulo_modal_mensualidad">
                        <i class="bi bi-buildings me-2"></i>Estado de Mensualidades por Apartamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <div class="p-3 mb-4 rounded border shadow-sm card-item">
                        <div class="row text-center g-3 align-items-start">
                            <div class="col-md-2 border-end">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-calendar2-month text-primary me-1 fs-6"></i> Período
                                </div>
                                <h6 id="vp_periodo" class="mb-0 fw-bold">---</h6>
                            </div>
                            <div class="col-md-3 border-end">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-cash-stack text-success me-1 fs-6"></i> Monto Base
                                </div>
                                <h6 id="vp_monto_base_bs" class="mb-0 fw-bold">---</h6>
                                <span id="vp_monto_base_usd" class="text-muted" style="font-size: 0.75rem;">---</span>
                            </div>
                            <div class="col-md-2 border-end">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-currency-exchange text-info me-1 fs-6"></i> Tasa BCV
                                </div>
                                <h6 id="vp_tasa" class="mb-0 fw-bold">---</h6>
                            </div>
                            <div class="col-md-2 border-end">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-graph-up-arrow text-danger me-1 fs-6"></i> Recargo
                                </div>
                                <span id="vp_recargo" class="badge bg-danger badge-soft-danger px-2 py-1 shadow-sm">---</span>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-calendar-x text-warning me-1 fs-6"></i> Día Límite
                                </div>
                                <h6 id="vp_limite" class="mb-0 fw-bold">---</h6>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div id="mensualidades_apartamentos" class="tabla-sistema-haydee m-0 border-0 rounded-bottom"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top d-flex justify-content-between">
                    <button type="button" id="btn_generar_reporte_modal" class="btn btn-info text-white px-4 shadow-sm" style="background-color:#3939a9;" title="Descargar Reporte de Pagos" data-tooltip="true">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Cuadro de Pagos
                    </button>
                    <button type="button" class="btn btn-soft-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/mensualidades_ajax.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/validaciones/mensualidad_validar.js"></script>
</body>
</html>