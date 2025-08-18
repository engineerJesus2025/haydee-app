<!DOCTYPE html>
<html>

<head>
    <title>Mantenimiento | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "vista/componentes/estilos.php";
    ?>
    <style type="text/css">
        [hidden] {
            display: none !important;
        }
    </style>
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

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>MANTENIMIENTO</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-12 mb-4">
                            <div class="card p-4">                                
                                <h4>Exportar copia de seguridad</h4>
                                <p class="my-4">Desde aquí podrá realizar copias de seguridad de la base de datos y/o los archivos del sistema para asegurar la integridad de la información. Los archivos se guardan en formato .sql</p>
                                <div class="row justify-content-between">
                                    <div class="col-lg-4 col-sm-10 mb-3 mb-lg-0 pe-lg-5 text-center mx-auto d-flex flex-column align-items-center">
                                        <label class="mb-2" for="select_db">¿Qué Base de Datos desea Exportar?</label>
                                        <select class="form-select" id="select_db">
                                            <option selected="" hidden="" value="">Seleccione la Base de Datos</option>
                                            <option value="negocio">Base de datos Edificio Haydee</option>
                                            <option value="seguridad">Base de datos de Seguridad</option>
                                        </select>
                                    </div>
                                    <div hidden="" class="col-lg-3 col-sm-5 d-flex flex-column align-items-center text-center">
                                        <label>¿Guardar copia en el sistema?</label>
                                        <button class="btn btn-primary mt-3 mx-auto" id="boton_exportar">Generar Copia de Seguridad</button>
                                    </div>
                                    <div class="col-sm-2 col-lg-1 text-center mt-4 mb-4 mb-sm-0" id="o" hidden="">O</div>
                                    <form hidden="" action="?pagina=mantenimiento_controlador.php&accion=inicio" method="POST" class="col-lg-3 col-sm-5 d-flex flex-column align-items-center text-center">
                                        <input type="text" name="db" hidden="" id="db_input">
                                        <input type="text" name="operacion" value="descargar_copia_seguridad" hidden="">
                                        <label>¿Descargar copia de seguridad?</label>
                                        <button class="btn btn-primary mt-3 mx-auto" id="boton_descargar" >Descargar copia de seguridad</button>
                                    </form>
                                </div> 
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card p-4">
                                <h4>Importar copia de seguridad</h4>
                                <p class="my-4">Desde aquí podrá importar copias de seguridad previamente generadas.</p>
                                <div class="row">
                                    <div class="col-lg-4 col-sm-5">
                                        <label class="mb-2" for="select_copias">Copias de Seguridad Guardas:</label>
                                        <select class="form-select" id="select_copias">
                                            <option selected="" hidden="" value="">Seleccione la Copia de Seguridad</option>
                                        </select>
                                    </div>

                                    <div class="col-sm-1 text-center mt-4 my-4 my-sm-0">O</div>

                                    <div class="col-lg-4 col-sm-6">
                                        <label class="mb-2" for="input_file_importar">Importar desde un archivo sql descargado:</label>
                                        <input id="input_file_importar" type="file" class="form-control" name="">
                                    </div>

                                    <div class="col-lg-3 col-sm-6 d-flex justify-content-center mx-auto">
                                        <button class="btn btn-primary mt-3 mx-auto" id="boton_importar" hidden="">Importar Copia de Seguridad</button>
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
    <script type="text/javascript" src="recursos/js/consultas_ajax/mantenimiento_ajax.js"></script>
</body>

</html>