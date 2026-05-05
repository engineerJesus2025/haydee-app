<!DOCTYPE html>
<html>

<head>
    <title>Reportes De Ingresos y Egresos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/reportes.css">

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
                        <h2>Reportes Estadísticos De Ingresos y Egresos</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row justify-content-center">             
                        <div class="col-12 col-xl-10">
                            <div class="card tarjeta-modulo p-4 border-0 shadow-sm">
                                <div class="text-center mb-4">
                                    <h4 class="fw-bold mb-1">
                                        <i class="bi bi-funnel me-2 text-primary"></i>Filtros del Reporte
                                    </h4>
                                    <p class="text-muted-custom">Configure los parámetros para generar su estadística</p>
                                </div>

                                <form class="row g-4" id="form_reporte">
                                    <div class="col-12">
                                        <div class="card vp-card shadow-sm border-0 px-2">
                                            <div class="card-body">
                                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-clock-history me-2"></i>Período de Evaluación</h6>
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-calendar-week"></i></span>
                                                            <select class="form-select" name="filtro" id="filtro" form="form_reporte">                
                                                                <option value="mes" selected="">Este mes</option>
                                                                <option value="trimestre">Últimos 3 meses</option>
                                                                <option value="semestre">Últimos 6 meses</option>
                                                                <option value="año">Este año</option>
                                                                <option value="Otro">Personalizado...</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="contenedor_fechas_personalizadas" class="row mt-3 bg-light bg-opacity-10 p-3 border rounded" hidden>
                                                    <div class="col-md-6 mb-3 mb-md-0">
                                                        <label for="fecha_inicio" class="form-label text-muted-custom">Desde: <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-calendar-plus"></i></span>
                                                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="fecha_fin" class="form-label text-muted-custom">Hasta: <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-calendar-minus"></i></span>
                                                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="card vp-card shadow-sm border-0 px-2">
                                            <div class="card-body">
                                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-sliders me-2"></i>Filtro de Datos</h6>
                                                
                                                <div class="row g-4">
                                                    <!-- Balance -->
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" id="balance" style="cursor: pointer;">
                                                            <label class="form-check-label fw-semibold" for="balance" style="cursor: pointer;">Filtrar Balance</label>
                                                        </div>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-cash-stack"></i></span>
                                                            <select class="form-select" id="select_balance" form="form_reporte" disabled>
                                                                <option value="todos">Todos</option>
                                                                <option value="Ingresos">Pagos de Mensualidad</option>
                                                                <option value="Egresos">Gastos efectuados</option>                                    
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Método de Transacción -->
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" id="metodo_pago" style="cursor: pointer;">
                                                            <label class="form-check-label fw-semibold" for="metodo_pago" style="cursor: pointer;">Método de Transacción</label>
                                                        </div>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-wallet2"></i></span>
                                                            <select class="form-select" id="select_metodo_pago" form="form_reporte" disabled>
                                                                <option value="todos">Todos</option>
                                                                <option value="Transferencia">Transferencia</option>
                                                                <option value="Efectivo">Efectivo</option>
                                                                <option value="Pago Movil">Pago Móvil</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Tipo de Gasto -->
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" id="tipo_gasto" style="cursor: pointer;">
                                                            <label class="form-check-label fw-semibold" for="tipo_gasto" style="cursor: pointer;">Tipo de Gasto</label>
                                                        </div>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                                            <select class="form-select" id="select_tipo_gasto" form="form_reporte" disabled>
                                                                <option value="todos">Todos</option>
                                                                <option value="Variable">Variable</option>
                                                                <option value="Fijo">Fijo</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4 text-center">
                                        <label class="form-label fw-bold mb-2">¿Cómo deseas estructurar el documento?</label>
                                        <div class="col-md-6 mx-auto">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-layout-text-window-reverse"></i></span>
                                                <select class="form-select" id="select_mostrar_datos" form="form_reporte">
                                                    <option value="grafico_texto">Gráfico y Estadísticas</option>
                                                    <option value="solo_grafico">Solo el Gráfico Visual</option>
                                                    <option value="solo_texto">Solo Resumen Numérico</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- BOTÓN -->
                                    <div class="col-12 text-center mt-5 mb-2">
                                        <button id="boton_vista_previa" class="btn btn-primary btn-lg px-5 shadow-sm rounded-pill" type="button">
                                            <i class="bi bi-eye me-2"></i>Generar Vista Previa
                                        </button>
                                    </div>
                                </form>
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
        // Modales
        require_once ROOT_PATH . "/vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingresos_egresos_modal.php";
    ?>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/dependencias/chartjs/chart.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/reportes/reporte_ingreso_egreso.js"></script>
</body>

</html>