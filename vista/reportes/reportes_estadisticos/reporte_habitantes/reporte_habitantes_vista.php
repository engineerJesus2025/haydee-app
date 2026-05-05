<!DOCTYPE html>
<html>

<head>
    <title>Reportes de Habitantes | Inicio</title>
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

            <div class="col d-flex flex-column min-vh-100 gris">

                <?php
                require_once ROOT_PATH . "/vista/componentes/header.php";
                ?>

                <main class="col ps-md-2 pt-2">

                    <div class="page-header pt-3">
                        <h2>Reportes Estadísticos de Habitantes</h2>
                    </div>
                    <hr>
                    <div class="row justify-content-center">

                        <div class="col-12 col-xl-10">
                            <div class="card tarjeta-modulo p-4 border-0 shadow-sm">
                                <div class="text-center mb-4">
                                    <h4 class="fw-bold mb-1">
                                        <i class="bi bi-funnel me-2 text-primary"></i>Filtros de Habitantes
                                    </h4>
                                    <p class="text-muted-custom">Configure los parámetros poblacionales para su reporte</p>
                                </div>

                                <form class="row g-4" id="form_reporte_habitantes">
                                
                                    <div class="col-12">
                                        <div class="card vp-card shadow-sm border-0 px-2">
                                            <div class="card-body">
                                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-clock-history me-2"></i>Período de Registro</h6>
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-calendar-week"></i></span>
                                                            <select class="form-select" name="filtro_tiempo" id="filtro_tiempo" form="form_reporte_habitantes">
                                                                <option selected hidden value="">Seleccione tiempo</option>
                                                                <option value="mes">Este mes</option>
                                                                <option value="trimestre">Últimos 3 meses</option>
                                                                <option value="año">Este año</option>
                                                                <option value="todo">Todos los registros</option>
                                                                <option value="personalizado">Rango personalizado</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Fechas Personalizadas -->
                                                <div id="contenedor_fechas_habitantes" class="row mt-3 bg-light bg-opacity-10 p-3 border rounded" hidden>
                                                    <label class="form-label fw-bold mb-2 text-muted-custom" id="label_fechas_habitantes">Seleccione el rango de fechas:</label>
                                                    <div class="col-md-6 mb-3 mb-md-0" id="div_fecha_inicio_habitantes">
                                                        <label for="fecha_inicio_habitantes" class="form-label text-muted-custom">Desde: <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-calendar-plus"></i></span>
                                                            <input type="date" class="form-control" name="fecha_inicio_habitantes" id="fecha_inicio_habitantes">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6" id="div_fecha_cierre_habitantes">
                                                        <label for="fecha_fin_habitantes" class="form-label text-muted-custom">Hasta: <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-calendar-minus"></i></span>
                                                            <input type="date" class="form-control" name="fecha_fin_habitantes" id="fecha_fin_habitantes">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="card vp-card shadow-sm border-0 px-2">
                                            <div class="card-body">
                                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-people me-2"></i>Filtro de Datos</h6>
                                                
                                                <div class="row g-4">
                                                    <!-- Rango de Edades -->
                                                    <div class="col-md-6">
                                                        <label for="rango_edades" class="form-label fw-semibold">Rango de edades:</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                                            <select class="form-select" id="rango_edades" name="rango_edades">
                                                                <option value="todos" selected>Todas las edades</option>
                                                                <option value="jovenes">Jóvenes (18-35 años)</option>
                                                                <option value="adultos">Adultos (36-59 años)</option>
                                                                <option value="mayores">Adultos mayores (60+ años)</option>
                                                                <option value="personalizado">Rango personalizado</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="row mt-2" id="contenedor_edades_personalizadas" hidden>
                                                            <div class="col-6" id="div_edad_minima">
                                                                <input type="number" class="form-control form-control-sm" id="edad_minima" name="edad_minima" min="0" max="120" placeholder="Min.">
                                                            </div>
                                                            <div class="col-6" id="div_edad_maxima">
                                                                <input type="number" class="form-control form-control-sm" id="edad_maxima" name="edad_maxima" min="0" max="120" placeholder="Máx.">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tipo de Residentes -->
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold">Tipo de habitantes:</label>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="radio" name="tipo_residente" id="todos_residentes" value="todos" checked>
                                                            <label class="form-check-label text-muted-custom" for="todos_residentes">Todos</label>
                                                        </div>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="radio" name="tipo_residente" id="propietarios" value="propietarios">
                                                            <label class="form-check-label text-muted-custom" for="propietarios">Propietarios</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="tipo_residente" id="arrendatarios" value="arrendatarios">
                                                            <label class="form-check-label text-muted-custom" for="arrendatarios">Arrendatarios</label>
                                                        </div>
                                                    </div>

                                                    <!-- Servicios -->
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold">Servicios básicos:</label>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="checkbox" id="con_agua" name="servicios[]" value="agua">
                                                            <label class="form-check-label text-muted-custom" for="con_agua">Agua</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="con_gas" name="servicios[]" value="gas">
                                                            <label class="form-check-label text-muted-custom" for="con_gas">Gas</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- FORMATO Y BOTÓN -->
                                    <div class="col-12 mt-4 text-center">
                                        <label class="form-label fw-bold mb-2">¿Cómo deseas estructurar el documento?</label>
                                        <div class="col-md-6 mx-auto">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-layout-text-window-reverse"></i></span>
                                                <select class="form-select" id="select_mostrar_datos" name="select_mostrar_datos">
                                                    <option value="grafico_texto">Gráficos y Estadísticas</option>
                                                    <option value="solo_grafico">Solo Gráficos Visuales</option>
                                                    <option value="solo_texto">Solo Resumen Numérico</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 text-center mt-5 mb-2">
                                        <button id="boton_generar_reporte" class="btn btn-primary btn-lg px-5 shadow-sm rounded-pill" type="button">
                                            <i class="bi bi-eye me-2"></i> Generar Vista Previa
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
    ?>

    
    <!-- Modales -->
    <div class="modal fade" id="modal_reporte_habitantes" tabindex="-1"
        aria-labelledby="titulo_modal_habitantes" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal_habitantes">Reporte Estadístico de
                        Habitantes</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body vp-body p-4 rounded-bottom">
                    
                    <!-- Fila de Gráficos -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <h6 class="text-center fw-bold text-muted-custom mb-3">Distribución por Sexo</h6>
                            <div class="chart-container" style="position: relative; height:250px; width:100%">
                                <canvas id="grafico_sexo"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-center fw-bold text-muted-custom mb-3">Tipos de Habitantes</h6>
                            <div class="chart-container" style="position: relative; height:250px; width:100%">
                                <canvas id="grafico_vivienda"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-center fw-bold text-muted-custom mb-3">Rangos de Edad</h6>
                            <div class="chart-container" style="position: relative; height:250px; width:100%">
                                <canvas id="grafico_edades"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Línea separadora elegante -->
                    <hr class="mx-auto border-secondary opacity-25 mb-5" style="width: 80%;">

                    <div class="text-center mb-4 pb-3">
                        <h5 class="fw-bold text-primary"><i class="bi bi-calculator me-2"></i>Resumen Estadístico</h5>
                    </div>
                    
                    <!-- Fila de Tarjetas (Usando card-estadistica) -->
                    <div class="row g-4 justify-content-center mb-4" id="contenedor_estadistica_habitantes">
                        
                        <!-- Tarjeta 1: Población -->
                        <div class="col-md-4">
                            <div class="card card-estadistica h-100 animacion-aparecer">
                                <div class="card-header py-3 text-center">
                                    <h6 class="fw-bold text-body mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Población General</h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush rounded-bottom">
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span class="text-muted-custom">Total Registrados:</span>
                                            <strong class="fs-5 text-primary" id="total_personas">0</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span class="text-muted-custom">Habitantes:</span>
                                            <strong class="text-body" id="total_habitantes">0</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                            <span class="text-muted-custom">Propietarios:</span>
                                            <strong class="text-body" id="total_propietarios">0</strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta 2: Género -->
                        <div class="col-md-4">
                            <div class="card card-estadistica h-100 animacion-aparecer" style="animation-delay: 0.1s;">
                                <div class="card-header py-3 text-center">
                                    <h6 class="fw-bold text-body mb-0"><i class="bi bi-gender-ambiguous text-info me-2"></i>Distribución de Género</h6>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                                    <div class="mb-4">
                                        <div class="badge-soft-danger mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px;">
                                            <i class="bi bi-gender-female fs-4"></i>
                                        </div>
                                        <span class="text-muted-custom">Mujeres:</span> 
                                        <strong class="fs-4 text-body ms-2" id="total_mujeres">0</strong>
                                    </div>
                                    <div>
                                        <div class="badge-soft-primary mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px;">
                                            <i class="bi bi-gender-male fs-4"></i>
                                        </div>
                                        <span class="text-muted-custom">Hombres:</span> 
                                        <strong class="fs-4 text-body ms-2" id="total_hombres">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta 3: Edades -->
                        <div class="col-md-4">
                            <div class="card card-estadistica h-100 animacion-aparecer" style="animation-delay: 0.2s;">
                                <div class="card-header py-3 text-center">
                                    <h6 class="fw-bold text-body mb-0"><i class="bi bi-bar-chart-fill text-success me-2"></i>Grupos de Edad</h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush rounded-bottom small">
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span class="text-muted-custom">Menores (0-17):</span>
                                            <strong class="text-body" id="menores_edad">0</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span class="text-muted-custom">Jóvenes (18-35):</span>
                                            <strong class="text-body" id="adultos_jovenes">0</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <span class="text-muted-custom">Adultos (36-59):</span>
                                            <strong class="text-body" id="adultos">0</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                            <span class="text-muted-custom">Mayores (+60):</span>
                                            <strong class="text-body" id="adultos_mayores">0</strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer adaptado -->
                <div class="border-top vp-border-color pt-4 pb-4 text-center d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-soft-secondary px-4 rounded-pill fw-semibold" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-danger px-4 rounded-pill fw-semibold shadow-sm" id="boton_exportar_pdf">
                        <i class="bi bi-file-earmark-pdf-fill me-2"></i> Exportar a PDF
                    </button>
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="boton_exportar_pdf">
                        <i class="bi bi-file-earmark-pdf"></i> Exportar a PDF
                    </button>
                </div> -->
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/dependencias/chartjs/chart.js"></script>
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/reportes/reporte_habitantes.js"></script>
</body>

</html>