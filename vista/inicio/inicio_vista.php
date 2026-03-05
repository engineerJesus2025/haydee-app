<!DOCTYPE html>
<html>

<head>
	<title>Inicio</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<?php
		require_once "vista/componentes/estilos.php";
	?>
	<link rel="stylesheet" type="text/css" href="recursos/css/publicaciones_inicio.css">
</head>
<body id="body-pd" class="body-pd d-flex flex-column">
	<div class="container-fluid">
		<div class="row flex-nowrap">
			<?php				
				require_once "vista/componentes/navbar.php";
			?>
			<div class="col d-flex flex-column gris">

				<?php require_once "vista/componentes/header.php"; ?>

				<main class="col ps-md-2 pt-2" style="background-color: #f4f7f9;">

					<div class="row justify-content-center px-2" id="contenido">
					<?php if ($_SESSION["rol"] != "Propietario") { ?>
                        
                        <div class="col-11 mb-4 mt-2">
                            <div class="row g-3">
                                <div class="col-xl-3 col-md-6">
                                    <div class="card dashboard-card h-100 p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="icon-box icon-box-blue">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path></svg>
                                            </div>
                                            <div class="badge-up" id="badge-aptos">80%</div>
                                        </div>
                                        <h3 class="fw-bold mb-1" id="kpi-aptos">
                                            <div class="skeleton skeleton-text short mb-0" style="height: 28px; width: 60px;"></div>
                                        </h3>
                                        <span class="text-muted-custom">Apartamentos Ocupados</span>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card dashboard-card h-100 p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="icon-box icon-box-green">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path></svg>
                                            </div>
                                        </div>
                                        <h3 class="fw-bold mb-1" id="kpi-residentes">
                                            <div class="skeleton skeleton-text short mb-0" style="height: 28px; width: 40px;"></div>
                                        </h3>
                                        <span class="text-muted-custom">Residentes Activos</span>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card dashboard-card h-100 p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="icon-box icon-box-yellow">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                                            </div>
                                        </div>
                                        <h3 class="fw-bold mb-1" id="kpi-recaudado">
                                            <div class="skeleton skeleton-text short mb-0" style="height: 28px; width: 80px;"></div>
                                        </h3>
                                        <span class="text-muted-custom">Recaudado Este Periodo</span>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card dashboard-card h-100 p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="icon-box icon-box-red">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                            </div>
                                            <div class="badge-down" id="badge-pendientes">↓ 7</div>
                                        </div>
                                        <h3 class="fw-bold mb-1" id="kpi-pendientes">
                                            <div class="skeleton skeleton-text short mb-0" style="height: 28px; width: 40px;"></div>
                                        </h3>
                                        <span class="text-muted-custom">Pagos Pendientes / Vencidos</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-11 mb-4" id="div_graficos">
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="card dashboard-card p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center">
                                                <svg class="text-primary me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#3b82f6;"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                                <h5 class="fw-bold mb-0">Ingresos vs Gastos</h5>
                                            </div>
                                            <span class="text-muted-custom">Últimos 6 meses</span>
                                        </div>
                                        
                                        <div class="card-text placeholder-glow d-none" id="esqueleto_titulo_2"><span class="placeholder w-100 rounded"></span></div>
                                        
                                        <div class="card-text skeleton my-3" id="esqueleto_canva_2">
                                            <span class="skeleton w-100 rounded" style="height: 250px; display:block;"></span>
                                        </div>
                                        <div class="chart-container mx-auto" style="height: 0">
                                            <canvas id="canva_2" hidden></canvas>
                                        </div>
                                        <div class="alert alert-warning text-center mx-auto mt-3" role="alert" id="div_alert_2" hidden style="width:fit-content;">No hay Datos para el gráfico</div>
                                        
                                        <div class="mt-4 pt-3 border-top">
                                            <ul class="list-unstyled text-muted-custom m-0 p-0" style="font-size: 0.9rem;">
                                                <li class="d-flex justify-content-between mb-2">
                                                    <span><i class="fas fa-arrow-up text-success me-2"></i> Ingreso Mes Actual</span>
                                                    <span class="fw-bold text-dark" id="esqueleto_dato_1_2">---</span>
                                                </li>
                                                <li class="d-flex justify-content-between">
                                                    <span><i class="fas fa-arrow-down text-danger me-2"></i> Egreso Mes Actual</span>
                                                    <span class="fw-bold text-dark" id="esqueleto_dato_2_2">---</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card dashboard-card p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center">
                                                <svg class="text-danger me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#ef4444;"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                                                <h5 class="fw-bold mb-0">Estado de Deudas</h5>
                                            </div>
                                        </div>
                                        
                                        <div class="card-text placeholder-glow d-none" id="esqueleto_titulo_1"><span class="placeholder w-100 rounded"></span></div>
                                        
                                        <div class="card-text placeholder-glow my-3" id="esqueleto_canva_1">
                                            <span class="skeleton w-100 rounded" style="height: 250px; display:block;"></span>
                                        </div>
                                        <div class="chart-container mx-auto" style="height: 0">
                                            <canvas id="canva_1" hidden></canvas>
                                        </div>
                                        <div class="alert alert-warning text-center mx-auto mt-3" role="alert" id="div_alert_1" hidden style="width:fit-content;">No hay Datos para el gráfico</div>
                                        
                                        <div class="mt-4 pt-3 border-top">
                                            <ul class="list-unstyled text-muted-custom m-0 p-0" style="font-size: 0.9rem;">
                                                <li class="d-flex justify-content-between mb-2">
                                                    <span><i class="fas fa-user-check text-success me-2"></i> Deuda Solvente</span>
                                                    <span class="fw-bold text-dark" id="esqueleto_dato_1_1">---</span>
                                                </li>
                                                <li class="d-flex justify-content-between">
                                                    <span><i class="fas fa-user-times text-danger me-2"></i> Deuda Pendiente</span>
                                                    <span class="fw-bold text-dark" id="esqueleto_dato_2_1">---</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
						</div>

                        <div class="col-11 mb-4">
                            <div class="card dashboard-card p-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                                    <div class="d-flex align-items-center mb-2 mb-md-0">
                                        <svg class="text-primary me-2" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#3b82f6;"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path></svg>
                                        <h5 class="fw-bold mb-0 text-dark">Apartamentos</h5>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-3 text-muted-custom" style="font-size: 0.85rem;">
                                        <div class="d-flex align-items-center"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color:#bfdbfe;"></span> Ocupado</div>
                                        <div class="d-flex align-items-center"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color:#bbf7d0;"></span> Disponible</div>
                                        <div class="d-flex align-items-center"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color:#fef08a;"></span> Mantenimiento</div>
                                    </div>
                                </div>

                                <div class="apartamentos-grid mt-2">
                                    <div class="skeleton skeleton-badge"></div>
                                    <div class="skeleton skeleton-badge"></div>
                                    <div class="skeleton skeleton-badge"></div>
                                    <div class="skeleton skeleton-badge"></div>
                                    <div class="skeleton skeleton-badge"></div>
                                    <div class="skeleton skeleton-badge"></div>
                                    <div class="skeleton skeleton-badge"></div>
                                    <div class="skeleton skeleton-badge"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-11 mb-4">
                            <div class="row g-4">
                                <div class="col-lg-8">
                                    <div class="card dashboard-card p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center">
                                                <svg class="text-primary me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#3b82f6;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                <h5 class="fw-bold mb-0">Actividad Reciente</h5>
                                            </div>
                                            <a href="?pagina=bitacora&accion=inicio">
                                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" >Ver todo</button>
                                            </a>
                                        </div>

                                        <div id="contenedor-actividad">
                                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                                <div class="skeleton skeleton-avatar me-3"></div>
                                                <div class="w-100 mt-1">
                                                    <div class="skeleton skeleton-text"></div>
                                                    <div class="skeleton skeleton-text short"></div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                                <div class="skeleton skeleton-avatar me-3"></div>
                                                <div class="w-100 mt-1">
                                                    <div class="skeleton skeleton-text"></div>
                                                    <div class="skeleton skeleton-text short"></div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                                <div class="skeleton skeleton-avatar me-3"></div>
                                                <div class="w-100 mt-1">
                                                    <div class="skeleton skeleton-text"></div>
                                                    <div class="skeleton skeleton-text short"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card dashboard-card p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center">
                                                <svg class="text-primary me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#3b82f6;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                                <h5 class="fw-bold mb-0">Últimas Publicaciones</h5>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-3" id="contenedor-widget-publicaciones">
                                            <?php for($i=0; $i<3; $i++) { ?>
                                            <div class="card publi-item bg-light border-0 p-3 rounded-4">
                                                <div class="d-flex align-items-start">
                                                    <div class="skeleton skeleton-avatar me-3" style="width: 32px; height: 32px;"></div>
                                                    <div class="w-100 mt-1">
                                                        <div class="skeleton skeleton-text"></div>
                                                        <div class="skeleton skeleton-text short"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

					<?php } ?>
					</div>
                    
                    <div class="d-flex justify-content-center mb-5"><span id="carga_publicaciones" hidden class="loader_publicaciones"></span></div>
				</main>
			</div>
		</div>
	</div>

	<template id="template-publicacion">
	    <div class="col-11 card post-card mx-auto shadow-lg my-4 px-0">
	        <div class="row g-0 h-100">
	            <div class="col-md-7 order-md-1">
	                <div class="content-area">
	                    <h2 class="post-title h3"></h2>
	                    <div class="post-meta my-2 mb-5">
	                        <small class="text-uppercase fw-bold"></small>
	                        <span class="author-badge ms-2"></span>
	                    </div>
	                    <p class="post-description flex-grow-1"></p>
	                </div>
	            </div>
	            <div class="col-md-5 order-md-2 d-flex align-items-center">
	                <div class="image-container w-100">
	                    <img class="post-image" alt="Imagen de la publicación">
	                </div>
	            </div>
	        </div>
	    </div>
	</template>

	<?php
        require_once "vista/componentes/footer.php";
        require_once "vista/componentes/script.php";
        require_once 'vista/componentes/modal_carga.php';
    ?>

	<script type="text/javascript" src="recursos/estadisticas/chart.js"></script>
	<script src="recursos/js/consultas_ajax/inicio_ajax.js"></script>
</body>
</html>