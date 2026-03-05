<!DOCTYPE html>
<html>

<head>
    <title>Reportes De Ingresos y Egresos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd">
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
                        <h2>Reportes Estadísticos De Ingresos y Egresos</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row justify-content-center">             
                        <div class="col-8 card p-3">
                            <h4 class="text-center">Seleccione los filtros para el reporte:</h4>
                            <form class="p-3 row justify-content-center" id="form_reporte">
                                <div class="col-sm-8 mb-3">
                                    <label for="filtro">Buscar resultados a partir de: <spam class="text-danger">*</spam></label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                                        <select class="form-select" aria-label="Default select example" name="filtro" id="filtro" form="form_reporte">                
                                            <option value="mes" selected="">Este mes</option>
                                            <option value="trimestre">Ultimos 3 meses</option>
                                            <option value="semestre">Ultimos 6 meses</option>
                                            <option value="año">Este año</option>
                                            <option value="Otro">Elegir fecha</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="contenedor_fechas_personalizadas" class="row mt-3 bg-light p-3 border rounded" hidden>
                                    <label class="form-label fw-bold mb-2">Seleccione el rango de fechas para el reporte:</label>
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_inicio" class="form-label">Desde: <spam class="text-danger">*</spam></label>
                                        <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_fin" class="form-label">Hasta: <spam class="text-danger">*</spam></label>
                                        <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                                    </div>
                                </div>
                                <label class="text-center mb-2">Filtros: <spam class="text-danger">*</spam></label>
                                <div class="mb-sm-5 mt-3 mt-sm-0 form-check col-sm-5">
                                    <label class="form-check-label" for="balance">Elegir Balance:</label>
                                    <input type="checkbox" class="form-check-input" id="balance">
                                </div>
                                <div class="col-lg-5 col-sm-7">
                                    <select class="form-select" id="select_balance" form="form_reporte" disabled >
                                        <option value="todos">Todos</option>
                                        <option value="Ingresos">Pagos de Mensualidad</option>
                                        <option value="Egresos">Gastos efectuados</option>                                    
                                    </select>
                                </div>
                                <div class="mb-sm-5 mt-4 mt-sm-0 form-check col-sm-5">
                                    <label class="form-check-label" for="metodo_pago">Filtrar por Metodo de pago:</label>
                                    <input type="checkbox" class="form-check-input" id="metodo_pago">
                                </div>
                                <div class="col-lg-5 col-sm-7">
                                    <select class="form-select" id="select_metodo_pago" form="form_reporte" disabled>
                                        <option value="todos">Todos</option>
                                        <option value="Transferencia">Transferencia</option>
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Pago Movil">Pago Móvil</option>
                                    </select>
                                </div>
                                <div class="mb-sm-5 mt-4 mt-sm-0 form-check col-sm-5">
                                    <label class="form-check-label" for="tipo_gasto">Fitrar por Tipo de gasto:</label>
                                    <input type="checkbox" class="form-check-input" id="tipo_gasto">
                                </div>
                                <div class="col-lg-5 col-sm-7">
                                    <select class="form-select" id="select_tipo_gasto" form="form_reporte" disabled>
                                        <option value="todos">Todos</option>
                                        <option value="Variable">Variable</option>
                                        <option value="Fijo">Fijo</option>
                                    </select>
                                </div>
                                <div class="mt-4 mt-sm-0 form-check col-12 text-center">
                                    <label>¿Como deseas mostrar los datos? <spam class="text-danger">*</spam></label>
                                </div>
                                <div class="mb-sm-5 col-sm-8 col-lg-5 mb-4 mb-sm-0">
                                    <select class="form-select" id="select_mostrar_datos" form="form_reporte">
                                        <option value="grafico_texto">Gráfico y texto</option>
                                        <option value="solo_grafico">Solo el Gráfico</option>
                                        <option value="solo_texto">Solo el texto</option>
                                    </select>
                                </div>
                                
                                <div class="col-8 mx-auto mb-3 d-flex justify-content-center">
                                    <button id="boton_vista_previa" class="btn btn-primary" type="button">Vista Previa</button>
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
        require_once "vista/componentes/footer.php";
        require_once "vista/componentes/script.php";
        require_once 'vista/componentes/modal_carga.php';
    ?>
    
    <!-- Modales -->
    <div class="modal fade" id="modal_reporte" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5" id="titulo_modal">Reporte de Ingresos y Egresos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <?php
                    require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingresos_egresos_modal.php";
                    ?>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="recursos/estadisticas/chart.js"></script>
    <script type="text/javascript" src="recursos/js/reportes/reporte_ingreso_egreso.js"></script>
</body>

</html>