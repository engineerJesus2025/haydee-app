<!DOCTYPE html>
<html>

<head>
    <title>Mantenimiento | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once ROOT_PATH . "/vista/componentes/estilos.php";
    ?>
    <style type="text/css">
        [hidden] {
            display: none !important;
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

                <main class="col ps-md-2 pt-2 mb-5">
                    <div class="page-header pt-3">
                        <h2>MANTENIMIENTO</h2>
                    </div>
                    <hr>

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-cloud-arrow-up fs-4 me-2 text-primary"></i>
                                        <h4 class="mb-0">Exportar copia de seguridad</h4>
                                    </div>
                                    <p class="text-muted">Genere un respaldo de la información actual del sistema. Puede guardarla en el servidor o descargarla a su equipo.</p>
                                    
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-md-8 col-lg-6 text-center">
                                            <label class="form-label fw-bold" for="select_db">1. Seleccione la Base de Datos</label>
                                            <select class="form-select form-select-lg mb-2" id="select_db">
                                                <option selected="" hidden value="">-- Elegir --</option>
                                                <option value="negocio">Edificio Haydee (Negocio)</option>
                                                <option value="seguridad">Módulo de Seguridad</option>
                                            </select>
                                            <span class="invalid-feedback"></span>
                                        </div>
                                    </div>

                                    <div id="acciones_exportar" class="row g-3 justify-content-center mt-3" style="display: none;">
                                        <div class="col-sm-auto">
                                            <button class="btn btn-outline-primary w-100 py-2" id="boton_exportar">
                                                <i class="bi bi-hdd-fill me-2"></i>Guardar en Sistema
                                            </button>
                                        </div>
                                        <div class="col-sm-auto d-flex align-items-center justify-content-center py-2 px-3 text-muted fw-bold">O</div>
                                        <div class="col-sm-auto">
                                            <form action="?pagina=mantenimiento&accion=inicio" method="POST">
                                                <input type="hidden" name="db" id="db_input">
                                                <input type="hidden" name="operacion" value="descargar_copia_seguridad">
                                                <button class="btn btn-primary w-100 py-2" id="boton_descargar">
                                                    <i class="bi bi-download me-2"></i>Descargar Archivo .SQL
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4">
    <div class="d-flex align-items-center mb-3">
        <i class="bi bi-cloud-arrow-down fs-4 me-2 text-primary"></i>
        <h4 class="mb-0">Importar copia de seguridad</h4>
    </div>
    <p class="text-muted">Restaure una copia previa. Tenga en cuenta que esta acción <strong class="text-danger">sobrescribirá los datos actuales</strong>.</p>
    
    <ul class="nav nav-tabs mb-4" id="importarTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="servidor-tab" data-bs-toggle="tab" data-bs-target="#servidor" type="button" role="tab" aria-controls="servidor" aria-selected="true">
                <i class="bi bi-hdd-network me-2"></i>Desde el Servidor
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="pc-tab" data-bs-toggle="tab" data-bs-target="#pc" type="button" role="tab" aria-controls="pc" aria-selected="false">
                <i class="bi bi-laptop me-2"></i>Desde su PC
            </button>
        </li>
    </ul>

    <div class="tab-content" id="importarTabsContent">
        
        <div class="tab-pane fade show active" id="servidor" role="tabpanel" aria-labelledby="servidor-tab">
            <div class="row">
                <div class="col-xl-10">
                    <label class="form-label fw-bold text-secondary" for="select_copias">Seleccione un archivo de respaldo:</label>
                    <select class="form-select form-select-lg shadow-sm" id="select_copias"></select>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pc" role="tabpanel" aria-labelledby="pc-tab">
            <div class="row">
                <div class="col-xl-10">
                    <label class="form-label fw-bold text-secondary" for="input_file_importar">Suba un archivo local (.sql):</label>
                    <input id="input_file_importar" type="file" class="form-control form-control-lg shadow-sm" accept=".sql">
                </div>
            </div>
        </div>

    </div>

    <div class="mt-4 border-top pt-4">
        <div id="info_seleccion" class="mb-4"></div>
        <div class="text-center">
            <button class="btn btn-danger btn-lg px-5 shadow-sm text-white" id="boton_importar" style="display: none;">
                <i id="icono_boton_importar" class="bi bi-arrow-repeat me-2"></i>
                <span id="texto_boton_importar">Restaurar datos de </span>
            </button>
        </div>
    </div>
</div>
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
    
    <!-- Scripts personalizado -->
    <script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/mantenimiento_ajax.js"></script>
</body>

</html>