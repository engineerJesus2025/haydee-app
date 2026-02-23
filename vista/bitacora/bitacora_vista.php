<!DOCTYPE html>
<html>
<head>
    <title>Bitácora | Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once "vista/componentes/estilos.php"; ?>
        <!-- Estilos personalizados para las acciones -->
    <style type="text/css">
        .badge-consultar { background-color: #2d3436; color: white; }
        .badge-eliminar { background-color: #b22222; color: white; }
        .badge-registrar { background-color: #0d6efd; color: white; }
        .badge-modificar { background-color: #198754; color: white; }
        .badge-iniciar-sesion { background-color: #6559BA; color: white; }
        .badge-cerrar-sesion { background-color: #9bb7d4; color: #372323; }
    </style>
</head>
<body id="body-pd" class="body-pd">
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php require_once "vista/componentes/navbar.php"; ?>
            <div class="col d-flex flex-column min-vh-100 gris">
                <?php require_once "vista/componentes/header.php"; ?>
                <main class="col ps-md-2 pt-2">
                    <div class="page-header pt-3">
                        <h2>BITÁCORA</h2>
                    </div>
                    <p class="lead"></p>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card p-4">
                                <div class="table-responsive">
                                    <table id="tabla_bitacora" class="table table-striped table-hover" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>USUARIO</th>
                                                <th>FECHA</th>
                                                <th>MÓDULO</th>
                                                <th>ACCIÓN</th>
                                                <th>DETALLES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <?php require_once 'vista/componentes/modal_carga.php'; ?>
                <?php require_once "vista/componentes/script.php"; ?>
            </div>
        </div>
    </div>
    <?php require_once "vista/componentes/footer.php"; ?>

    <!-- Modal para ver detalles -->
    <div class="modal fade" id="modalDetalleBitacora" tabindex="-1" aria-labelledby="modalDetalleBitacoraLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetalleBitacoraLabel">
                        <i class="bi bi-journal-text me-2"></i>Detalle de Bitácora
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <!-- Información general -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-person-circle me-2"></i>Usuario:</strong> <span id="detalle_usuario"></span></p>
                                <p><strong><i class="bi bi-shield me-2"></i>Rol:</strong> <span id="detalle_rol"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-calendar-event me-2"></i>Fecha:</strong> <span id="detalle_fecha"></span></p>
                                <p><strong><i class="bi bi-puzzle me-2"></i>Módulo:</strong> <span id="detalle_modulo"></span></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <p><strong><i class="bi bi-tag me-2"></i>Acción:</strong> 
                                    <span id="detalle_accion" class="badge bg-info text-dark"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Sección para acciones de consulta -->
                        <div id="detalle_consulta" class="alert alert-info d-none">
                            <i class="bi bi-info-circle me-2"></i>
                            <span id="mensaje_consulta"></span>
                        </div>

                        <!-- Sección para valores anteriores y nuevos (solo para modificar, eliminar, registrar) -->
                        <div id="detalle_cambios" class="d-none">
                            <ul class="nav nav-tabs" id="cambiosTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="anteriores-tab" data-bs-toggle="tab" data-bs-target="#anteriores" type="button" role="tab" aria-controls="anteriores" aria-selected="true">Valores Anteriores</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="nuevos-tab" data-bs-toggle="tab" data-bs-target="#nuevos" type="button" role="tab" aria-controls="nuevos" aria-selected="false">Valores Nuevos</button>
                                </li>
                            </ul>
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="cambiosTabsContent">
                                <div class="tab-pane fade show active" id="anteriores" role="tabpanel" aria-labelledby="anteriores-tab">
                                    <pre id="valores_anteriores" class="bg-light p-3 rounded" style="max-height: 300px; overflow: auto;"></pre>
                                </div>
                                <div class="tab-pane fade" id="nuevos" role="tabpanel" aria-labelledby="nuevos-tab">
                                    <pre id="valores_nuevos" class="bg-light p-3 rounded" style="max-height: 300px; overflow: auto;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="recursos/js/consultas_ajax/bitacora_ajax.js"></script>
</body>
</html>