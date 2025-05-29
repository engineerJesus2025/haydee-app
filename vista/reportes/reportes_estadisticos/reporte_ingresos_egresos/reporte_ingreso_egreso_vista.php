<!DOCTYPE html>
<html>

<head>
    <title>Reportes De Ingresos y Egresos | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
</head>

<body class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap ">
            <?php
            require_once "vista/componentes/sesion.php";
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
                            <form class="p-3 row justify-content-center">
                                <label class="text-center mb-2">Seleccione la medida de tiempo:</label>
                                <div class="col-5 mb-5">
                                    <label for="fecha_inicio">Fecha de Inicio</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-check"></i></span>
                                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
                                    </div>
                                </div>
                                <div class="col-5 mb-5">
                                    <label for="fecha_fin">Fecha de Cierre</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-check"></i></span>
                                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control">
                                    </div>
                                </div>
                                <label class="text-center mb-2">Filtros:</label>
                                <div class="mb-5 form-check col-5">
                                    <label class="form-check-label" for="balance">Elegir Balance:</label>
                                    <input type="checkbox" class="form-check-input" id="balance">
                                </div>
                                <div class="col-5">
                                    <select class="form-select" id="select_balance">
                                        <option value="Todos">Todos</option>
                                        <option value="Ingresos">Ingresos</option>
                                        <option value="Egresos">Egresos</option>                                    
                                    </select>
                                </div>
                                <div class="mb-5 form-check col-5">
                                    <label class="form-check-label" for="metodo_pago">Filtrar por Metodo de pago:</label>
                                    <input type="checkbox" class="form-check-input" id="metodo_pago">
                                </div>
                                <div class="col-5">
                                    <select class="form-select" id="select_metodo_pago">
                                        <option value="Todos">Todos</option>
                                        <option value="Transferencia">Transferencia</option>
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Pago Movil">Pago Móvil</option>
                                    </select>
                                </div>
                                <div class="mb-5 form-check col-5">
                                    <label class="form-check-label" for="tipo_gasto">Fitrar por Tipo de gasto:</label>
                                    <input type="checkbox" class="form-check-input" id="tipo_gasto">
                                </div>
                                <div class="col-5">
                                    <select class="form-select" id="select_tipo_gasto">
                                        <option value="Todos">Todos</option>
                                        <option value="Variable">Variable</option>
                                        <option value="Fijo">Fijo</option>
                                    </select>
                                </div>
                                <div class="col-3 mx-auto">
                                    <button id="boton_vista_previa" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal_reporte">Vista Previa</button>
                                </div>
                            </form>
                            
                        </div>
                        <div class="modal fade" id="modal_reporte" tabindex="-1" aria-labelledby="titulo_modal" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
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