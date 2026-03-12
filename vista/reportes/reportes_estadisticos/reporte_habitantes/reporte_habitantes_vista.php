<!DOCTYPE html>
<html>

<head>
    <title>Reportes de Habitantes | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
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

                        <div class="col-lg-8 col-11 card p-3">
                            <h4 class="text-center">Seleccione los filtros para el reporte:</h4>
                            <form class="p-3 row justify-content-center" id="form_reporte_habitantes">
                                <!-- Filtro por Fecha -->
                                <div class="col-sm-8 mb-3">
                                    <label for="filtro_tiempo">Buscar resultados a partir de:</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1"><i
                                                class="bi bi-calendar-week"></i></span>
                                        <select class="form-select" aria-label="Default select example"
                                            name="filtro_tiempo" id="filtro_tiempo" form="form_reporte_habitantes">
                                            <option selected hidden value="">Seleccione tiempo</option>
                                            <option value="mes">Este mes</option>
                                            <option value="trimestre">Últimos 3 meses</option>
                                            <option value="año">Este año</option>
                                            <option value="todo">Todos los registros</option>
                                            <option value="personalizado">Rango personalizado</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Fechas personalizadas (se muestra solo cuando se selecciona "personalizado") -->
                                <label class="text-center mb-2" id="label_fechas_habitantes" hidden>Seleccione el rango  de fechas:</label>
                                <div class="col-sm-6 col-lg-5 mb-3" id="div_fecha_inicio_habitantes" hidden>
                                    <label for="fecha_inicio_habitantes">Fecha de inicio</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar2-check"></i></span>
                                        <input type="date" name="fecha_inicio_habitantes" id="fecha_inicio_habitantes"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-5 mb-3" id="div_fecha_cierre_habitantes" hidden>
                                    <label for="fecha_fin_habitantes">Fecha de fin</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar2-check"></i></span>
                                        <input type="date" name="fecha_fin_habitantes" id="fecha_fin_habitantes"
                                            class="form-control">
                                    </div>
                                </div>

                                <!-- Filtro por Rango de Edades -->
                                <div class="col-sm-8 mb-3">
                                    <label for="rango_edades">Rango de edades:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                                        <select class="form-select" id="rango_edades" name="rango_edades">
                                            <option value="todos" selected>Todas las edades</option>
                                            <option value="jovenes">Jóvenes (18-35 años)</option>
                                            <option value="adultos">Adultos (36-59 años)</option>
                                            <option value="mayores">Adultos mayores (60+ años)</option>
                                            <option value="personalizado">Rango personalizado</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Edades personalizadas -->
                                <div class="col-6 col-lg-5 mb-3" id="div_edad_minima" hidden>
                                    <label for="edad_minima">Edad mínima</label>
                                    <input type="number" class="form-control" id="edad_minima" name="edad_minima"
                                        min="0" max="120">
                                </div>
                                <div class="col-6 col-lg-5 mb-3" id="div_edad_maxima" hidden>
                                    <label for="edad_maxima">Edad máxima</label>
                                    <input type="number" class="form-control" id="edad_maxima" name="edad_maxima"
                                        min="0" max="120">
                                </div>

                                <!-- Filtro por Tipo de Residentes -->
                                <div class="col-sm-5 my-3">
                                    <label>Tipo de habitantes:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_residente"
                                            id="todos_residentes" value="todos" checked>
                                        <label class="form-check-label" for="todos_residentes">Todos los
                                            habitantes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_residente"
                                            id="propietarios" value="propietarios">
                                        <label class="form-check-label" for="propietarios">Solo propietarios</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_residente"
                                            id="arrendatarios" value="arrendatarios">
                                        <label class="form-check-label" for="arrendatarios">Solo alquilados</label>
                                    </div>
                                </div>

                                <!-- Filtro por Servicios -->
                                <div class="col-sm-5 my-3">
                                    <label>Servicios básicos:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="con_agua" name="servicios[]"
                                            value="agua">
                                        <label class="form-check-label" for="con_agua">Con servicio de agua</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="con_gas" name="servicios[]"
                                            value="gas">
                                        <label class="form-check-label" for="con_gas">Con servicio de gas</label>
                                    </div>
                                </div>

                                <!-- Filtro de grafico o texto -->
                                <div class="col-sm-9 mb-3">
                                    <label for="select_mostrar_datos">¿Como deseas mostrar los datos?:</label>
                                    <div class="input-group">
                                        <select class="form-select" id="select_mostrar_datos" name="select_mostrar_datos">
                                            <option value="grafico_texto">Gráfico y texto</option>
                                            <option value="solo_grafico">Solo el Gráfico</option>
                                            <option value="solo_texto">Solo el texto</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Botón de generación -->
                                <div class="col-6 mx-auto mt-4 d-flex justify-content-center">
                                    <button id="boton_generar_reporte" class="btn btn-primary" type="button">
                                        <i class="bi bi-graph-up"></i> Generar Reporte
                                    </button>
                                </div>
                            </form>
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
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal_habitantes">Reporte Estadístico de
                        Habitantes</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h5 class="text-center">Distribución por Sexo</h5>
                            <div class="chart-container" style="position: relative; height:30vh; width:100%">
                                <canvas id="grafico_sexo"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5 class="text-center">Tipos de Habitantes</h5>
                            <div class="chart-container" style="position: relative; height:30vh; width:100%">
                                <canvas id="grafico_vivienda"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5 class="text-center">Rangos de Edad</h5>
                            <div class="chart-container" style="position: relative; height:30vh; width:100%">
                                <canvas id="grafico_edades"></canvas>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4 class="text-center mb-4">Resumen Estadístico</h4>
                    
                    <div class="row g-3 justify-content-center" id="contenedor_estadistica_habitantes">
                        
                        <div class="col-md-4">
                            <div class="card border-primary h-100 shadow-sm">
                                <div class="card-header bg-primary text-white text-center fw-bold">
                                    <i class="bi bi-people-fill"></i> Población General
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Registrados:</span>
                                        <strong class="fs-5 text-primary" id="total_personas">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Habitantes:</span>
                                        <strong id="total_habitantes">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Propietarios:</span>
                                        <strong id="total_propietarios">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-info h-100 shadow-sm">
                                <div class="card-header bg-info text-white text-center fw-bold">
                                    <i class="bi bi-gender-ambiguous"></i> Género
                                </div>
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-gender-female text-danger fs-4"></i> Mujeres: 
                                        <strong class="fs-5" id="total_mujeres">0</strong>
                                    </div>
                                    <div>
                                        <i class="bi bi-gender-male text-primary fs-4"></i> Hombres: 
                                        <strong class="fs-5" id="total_hombres">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-success h-100 shadow-sm">
                                <div class="card-header bg-success text-white text-center fw-bold">
                                    <i class="bi bi-bar-chart-fill"></i> Grupos de Edad
                                </div>
                                <div class="card-body small">
                                    <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                        <span>Menores (0-17):</span>
                                        <strong id="menores_edad">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                        <span>Jóvenes (18-35):</span>
                                        <strong id="adultos_jovenes">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                        <span>Adultos (36-59):</span>
                                        <strong id="adultos">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Mayores (+60):</span>
                                        <strong id="adultos_mayores">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="boton_exportar_pdf">
                        <i class="bi bi-file-earmark-pdf"></i> Exportar a PDF
                    </button>
                </div>
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