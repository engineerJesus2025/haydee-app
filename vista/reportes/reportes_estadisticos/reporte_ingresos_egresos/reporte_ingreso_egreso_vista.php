<!DOCTYPE html>
<html>

<head>
    <title>Reportes De Ingresos y Egresos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body id="body-pd" class="body-pd mb-5">
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
                                    <label for="filtro">Buscar resultados a partir de:</label>
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

                                <label class="text-center mb-2" id="label_fechas" hidden="">Seleccione la medida de tiempo:</label>
                                <div class="col-lg-5 mb-5" id="div_fecha_inicio" hidden="">
                                    <label for="fecha_inicio">Fecha de Inicio</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-check"></i></span>
                                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-5 mb-5" id="div_fecha_cierre" hidden="">
                                    <label for="fecha_fin">Fecha de Cierre</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-check"></i></span>
                                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control">
                                    </div>
                                </div>
                                <label class="text-center mb-2">Filtros:</label>
                                <div class="mb-sm-5 mt-3 mt-sm-0 form-check col-sm-5">
                                    <label class="form-check-label" for="balance">Elegir Balance:</label>
                                    <input type="checkbox" class="form-check-input" id="balance">
                                </div>
                                <div class="col-lg-5 col-sm-7">
                                    <select class="form-select" id="select_balance" form="form_reporte" disabled >
                                        <option value="Todos">Todos</option>
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
                                        <option value="Todos">Todos</option>
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
                                        <option value="Todos">Todos</option>
                                        <option value="Variable">Variable</option>
                                        <option value="Fijo">Fijo</option>
                                    </select>
                                </div>
                                <div class="mt-4 mt-sm-0 form-check col-12 text-center">
                                    <label>¿Como deseas mostrar los datos?</label>
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
                    </div>

                    
                </main>
                <?php
                require_once "vista/componentes/footer.php";
                require_once "vista/componentes/script.php";
                ?>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="recursos/estadisticas/chart.js"></script>
    <script type="text/javascript" src="recursos/js/reportes/reporte_ingreso_egreso.js"></script>
</body>

</html>