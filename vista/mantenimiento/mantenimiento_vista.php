<!DOCTYPE html>
<html>

<head>
    <title>Mantenimiento | Inicio</title>
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
                                <p class="my-4">Desde aquí podrá realizar copias de seguridad de la base de datos y/o los archivos del sistema para asegurar la integridad de la información.</p>
                                <div class="row">
                                    <!-- <div class="col-4">
                                        <label class="mb-2" for="select_exportar">¿Qué desea Exportar?</label>
                                        <select class="form-select" id="select_exportar">
                                            <option selected="" hidden="">Seleccione lo que desee exportar</option>
                                            <option value="bd">Base de datos</option>
                                            <option value="documentos">Documentos Guardados</option>
                                        </select>
                                    </div>
                                    <div class="col-1"></div> -->
                                    <div class="col-4" >
                                        <label class="mb-2" for="select_db">¿Qué Base de Datos desea Exportar?</label>
                                        <select class="form-select" id="select_db">
                                            <option selected="" hidden="" value="">Seleccione la Base de Datos</option>
                                            <option value="negocio">Base de datos Edificio Haydee</option>
                                            <option value="seguridad">Base de datos Seguridad</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <button class="btn btn-primary mt-3 mx-auto" id="boton_exportar" hidden="">Generar Copia de Seguridad</button>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card p-4">
                                <h4>Importar copia de seguridad</h4>
                                <p class="my-4">Desde aquí podrá importar copias de seguridad previamente generadas.</p>
                                <div class="row">
                                    <div class="col-5">
                                        <label class="mb-2" for="select_copias">Copias de Seguridad Creadas:</label>
                                        <select class="form-select" id="select_copias">
                                            <option selected="" hidden="" value="">Seleccione la Copia de Seguridad</option>
                                        </select>
                                    </div>
                                    <div class="col-1"></div>
                                    <div class="col">
                                        <button class="btn btn-primary mt-3 mx-auto" id="boton_importar" hidden="">Importar Copia de Seguridad</button>
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
    <script type="text/javascript" src="recursos/js/consultas_ajax/mantenimiento_ajax.js"></script>
</body>

</html>