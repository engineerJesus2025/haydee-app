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

    .vp-status-card {
        background-color: var(--item-card-bg); 
        border: 1px solid var(--ch-header-border);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .vp-status-card:hover {
        border-color: rgba(13, 110, 253, 0.4);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); 
        transform: translateY(-2px);
    }

    .vp-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
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
                        <h2 id="titulo_pagina" class="fw-bold">MANTENIMIENTO</h2>
                    </div>
                    <hr class="vp-border-color mb-4">
                    <div class="row g-4">
                        <!-- TARJETA DE EXPORTACIÓN -->
                        <div class="col-12">
                            <!-- Agregamos tarjeta-modulo para heredar la fusión de íconos del CSS -->
                            <div class="card vp-card tarjeta-modulo shadow-sm border vp-border-color">
                                <div class="card-body p-4 p-md-5">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-cloud-arrow-up fs-3 me-3 text-primary"></i>
                                        <h4 class="mb-0 fw-bold">Exportar copia de seguridad</h4>
                                    </div>
                                    <p class="text-muted-custom mb-4">Genere un respaldo de la información actual del sistema. Puede guardarla en el servidor o descargarla a su equipo.</p>
                                    
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-md-8 col-lg-6 text-center">
                                            <label class="form-label fw-bold mb-3" for="select_db">1. Seleccione la Base de Datos</label>

                                            <!-- Input Group con ícono integrado y has-validation -->
                                            <div class="input-group input-group-lg mb-3 has-validation">
                                                <span class="input-group-text">
                                                    <i class="bi bi-database"></i>
                                                </span>
                                                <!-- Quitamos el mb-2 que estaba repetido dentro del select -->
                                                <select class="form-select" id="select_db">
                                                    <option selected="" hidden value="">-- Elegir --</option>
                                                    <option value="negocio">Edificio Haydee (Negocio)</option>
                                                    <option value="seguridad">Módulo de Seguridad</option>
                                                </select>
                                                <span class="invalid-feedback text-start"></span>
                                            </div>

                                        </div>
                                    </div>

                                    <div id="acciones_exportar" class="row g-3 justify-content-center mt-3" style="display: none;">
                                        <div class="col-sm-auto">
                                            <button class="btn btn-outline-primary w-100 py-2 rounded-pill fw-semibold" id="boton_exportar">
                                                <i class="bi bi-hdd-fill me-2"></i>Guardar en Sistema
                                            </button>
                                        </div>
                                        <div class="col-sm-auto d-flex align-items-center justify-content-center py-2 px-3 text-muted-custom fw-bold">O</div>
                                        <div class="col-sm-auto">
                                            <form action="?pagina=mantenimiento&accion=inicio" method="POST">
                                                <input type="hidden" name="db" id="db_input">
                                                <input type="hidden" name="operacion" value="descargar_copia_seguridad">
                                                <button class="btn btn-primary w-100 py-2 rounded-pill fw-semibold shadow-sm" id="boton_descargar">
                                                    <i class="bi bi-download me-2"></i>Descargar Archivo .SQL
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TARJETA DE IMPORTACIÓN -->
                        <div class="col-12">
                            <div class="card vp-card tarjeta-modulo shadow-sm border vp-border-color">
                                <div class="card-body p-4 p-md-5">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-cloud-arrow-down fs-3 me-3 text-primary"></i>
                                        <h4 class="mb-0 fw-bold">Importar copia de seguridad</h4>
                                    </div>
                                    <p class="text-muted-custom mb-4">Restaure una copia previa. Tenga en cuenta que esta acción <strong class="text-danger">sobrescribirá los datos actuales</strong>.</p>
                                    
                                    <!-- Pestañas actualizadas a "nav-pills" para un look moderno -->
                                    <ul class="nav nav-pills mb-4 p-2 bg-light bg-opacity-10 border vp-border-color rounded-pill d-inline-flex" id="importarTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active fw-bold rounded-pill px-4" id="servidor-tab" data-bs-toggle="tab" data-bs-target="#servidor" type="button" role="tab" aria-controls="servidor" aria-selected="true">
                                                <i class="bi bi-hdd-network me-2"></i>Desde el Servidor
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link fw-bold rounded-pill px-4" id="pc-tab" data-bs-toggle="tab" data-bs-target="#pc" type="button" role="tab" aria-controls="pc" aria-selected="false">
                                                <i class="bi bi-laptop me-2"></i>Desde su PC
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="importarTabsContent">
                                        
                                        <!-- Pestaña 1: Servidor -->
                                        <div class="tab-pane fade show active" id="servidor" role="tabpanel" aria-labelledby="servidor-tab">
                                            <div class="row">
                                                <div class="col-xl-10">
                                                    <label class="form-label fw-bold mb-3" for="select_copias">Seleccione un archivo de respaldo:</label>
                                                    <div class="input-group input-group-lg">
                                                        <span class="input-group-text"><i class="bi bi-file-earmark-code"></i></span>
                                                        <select class="form-select shadow-none" id="select_copias"></select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pestaña 2: PC Local -->
                                        <div class="tab-pane fade" id="pc" role="tabpanel" aria-labelledby="pc-tab">
                                            <div class="row">
                                                <div class="col-xl-10">
                                                    <label class="form-label fw-bold mb-3" for="input_file_importar">Suba un archivo local (.sql):</label>
                                                    <div class="input-group input-group-lg">
                                                        <span class="input-group-text"><i class="bi bi-folder2-open"></i></span>
                                                        <input id="input_file_importar" type="file" class="form-control shadow-none" accept=".sql">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Área de acción final -->
                                    <div class="mt-5 border-top vp-border-color pt-4">
                                        <div id="info_seleccion" class="mb-4"></div>
                                        <div class="text-center">
                                            <button class="btn btn-danger btn-lg px-5 shadow-sm text-white rounded-pill fw-bold" id="boton_importar" style="display: none;">
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