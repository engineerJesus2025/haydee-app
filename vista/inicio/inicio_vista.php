<!DOCTYPE html>
<html>

<head>
	<title>Inicio</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<?php
		require_once ROOT_PATH . "/vista/componentes/estilos.php";
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/publicaciones_inicio.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL_BASE; ?>recursos/css/src/esqueletos.css">
</head>
<body id="body-pd" class="body-pd d-flex flex-column">
	<div class="container-fluid">
		<div class="row flex-nowrap">
			<?php				
				require_once ROOT_PATH . "/vista/componentes/navbar.php";
			?>
			<div class="col d-flex flex-column gris">

				<?php require_once ROOT_PATH . "/vista/componentes/header.php"; ?>
				<main class="col ps-md-2 pt-2">
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
                                            <div class="badge-up" id="badge-aptos" hidden></div>
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
                                            <div class="badge-down" id="badge-pendientes" hidden></div>
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
                                                <svg class=" me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
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
                                                    <span><i class="bi bi-arrow-up text-success me-2"></i> Ingreso Mes Actual</span>
                                                    <span class="fw-bold " id="esqueleto_dato_1_2">
                                                        <div class="skeleton skeleton-text short mb-0" style="width: 8rem;"></div>
                                                    </span>
                                                </li>
                                                <li class="d-flex justify-content-between">
                                                    <span><i class="bi bi-arrow-down text-danger me-2"></i> Egreso Mes Actual</span>
                                                    <span class="fw-bold " id="esqueleto_dato_2_2">
                                                        <div class="skeleton skeleton-text short mb-0" style="width: 8rem;"></div>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card dashboard-card p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="d-flex align-items-center">
                                                <svg class=" me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
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
                                                    <span><i class="bi bi-patch-check-fill  text-success me-2"></i> Deuda Solvente</span>
                                                    <span class="fw-bold " id="esqueleto_dato_1_1">
                                                        <div class="skeleton skeleton-text short mb-0" style="width: 4rem;"></div>
                                                    </span>
                                                </li>
                                                <li class="d-flex justify-content-between">
                                                    <span><i class="bi bi-exclamation-circle-fill text-danger me-2"></i>Deuda Pendiente</span>
                                                    <span class="fw-bold " id="esqueleto_dato_2_1">
                                                        <div class="skeleton skeleton-text short mb-0" style="width: 4rem;"></div>
                                                    </span>
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
                                        <svg class=" me-2" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path></svg>
                                        <h5 class="fw-bold mb-0 ">Apartamentos</h5>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-3 text-muted-custom" style="font-size: 0.85rem;">
                                        <div class="d-flex align-items-center"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color: var(--ch-badge-primary-border);"></span> Ocupado</div>
                                        <div class="d-flex align-items-center"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color: var(--ch-badge-success-border);"></span> Disponible</div>
                                        <div class="d-flex align-items-center"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color: var(--ch-badge-warning-border);"></span> Mantenimiento</div>
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
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <svg class=" me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                <h5 class="fw-bold mb-0">Actividad Reciente</h5>
                                            </div>
                                            <a href="?pagina=bitacora&accion=inicio">
                                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" >
                                                    <i class="bi bi-box-arrow-in-right me-2"></i> Ver todo
                                                </button>
                                            </a>
                                        </div>

                                        <div id="contenedor-actividad" class="d-flex flex-column flex-grow-1 justify-content-around">
                                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom vp-border-color">
                                                <div class="skeleton skeleton-avatar me-3"></div>
                                                <div class="w-100 mt-1">
                                                    <div class="skeleton skeleton-text"></div>
                                                    <div class="skeleton skeleton-text short"></div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom vp-border-color">
                                                <div class="skeleton skeleton-avatar me-3"></div>
                                                <div class="w-100 mt-1">
                                                    <div class="skeleton skeleton-text"></div>
                                                    <div class="skeleton skeleton-text short"></div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom vp-border-color">
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
                                                <svg class=" me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                                <h5 class="fw-bold mb-0">Últimas Publicaciones</h5>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-3" id="contenedor-widget-publicaciones">
                                            <?php for($i=0; $i<3; $i++) { ?>
                                            <div class="card publi-item  border-0 p-3 rounded-4">
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
        <div class="col-12 col-md-6 col-xl-4 d-flex align-items-stretch mb-4 item-publicacion">
            <div class="card w-100 shadow-sm tarjeta-publicacion">
                <div class="position-relative">
                    <img class="card-img-top imagen-tarjeta post-image" alt="Imagen de la publicación" 
                        onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22400%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20400%20200%22%20preserveAspectRatio%3D%22none%22%3E%3Crect%20width%3D%22400%22%20height%3D%22200%22%20fill%3D%22%23f8f9fa%22%2F%3E%3Cpath%20d%3D%22M140%2080%20h35%20v90%20h-35%20z%22%20fill%3D%22%23dee2e6%22%2F%3E%3Cpath%20d%3D%22M147%2090%20h8%20v8%20h-8%20z%20M160%2090%20h8%20v8%20h-8%20z%20M147%20105%20h8%20v8%20h-8%20z%20M160%20105%20h8%20v8%20h-8%20z%20M147%20120%20h8%20v8%20h-8%20z%20M160%20120%20h8%20v8%20h-8%20z%20M147%20135%20h8%20v8%20h-8%20z%20M160%20135%20h8%20v8%20h-8%20z%20M147%20150%20h8%20v8%20h-8%20z%20M160%20150%20h8%20v8%20h-8%20z%22%20fill%3D%22%23f8f9fa%22%2F%3E%3Cpath%20d%3D%22M185%2050%20h45%20v120%20h-45%20z%22%20fill%3D%22%23ced4da%22%2F%3E%3Cpath%20d%3D%22M195%2065%20h10%20v10%20h-10%20z%20M210%2065%20h10%20v10%20h-10%20z%20M195%2085%20h10%20v10%20h-10%20z%20M210%2085%20h10%20v10%20h-10%20z%20M195%20105%20h10%20v10%20h-10%20z%20M210%20105%20h10%20v10%20h-10%20z%20M195%20125%20h10%20v10%20h-10%20z%20M210%20125%20h10%20v10%20h-10%20z%20M195%20145%20h10%20v10%20h-10%20z%20M210%20145%20h10%20v10%20h-10%20z%22%20fill%3D%22%23f8f9fa%22%2F%3E%3Cpath%20d%3D%22M220%2070%20h30%20v100%20h-30%20z%22%20fill%3D%22%23e9ecef%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%22190%22%20fill%3D%22%23adb5bd%22%20font-size%3D%2212%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-weight%3D%22bold%22%20letter-spacing%3D%222%22%20text-anchor%3D%22middle%22%3ECOMUNICADO%3C%2Ftext%3E%3C%2Fsvg%3E';">
                    <span class="badge etiqueta-prioridad shadow-sm priority-badge d-flex align-items-center gap-1">
                        <i class="icono-prioridad"></i>
                        <span class="texto-prioridad"></span>
                    </span>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold  post-title"></h5>
                    <p class="card-text text-muted mb-4 flex-grow-1 texto-limitado post-description"></p>
                    
                    <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                        <small class="d-flex align-items-center">
                            <i class="bi bi-person-fill  me-2"></i> 
                            <strong class="author-name"></strong>
                        </small>
                        <small class="d-flex align-items-center">
                            <i class="bi bi-clock text-muted me-2"></i> 
                            <span class="post-date"></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </template>

	<?php
        require_once ROOT_PATH . "/vista/componentes/footer.php";
        require_once ROOT_PATH . "/vista/componentes/script.php";
        require_once ROOT_PATH . "/vista/componentes/modal_carga.php";
        // Modales
        require_once ROOT_PATH . "/vista/bitacora/bitacora_modal.php";
    ?>

    <!-- Modales -->
    <div class="modal fade" id="modalVistaPreviaPublicacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-megaphone me-2"></i>Detalles de la Publicación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body rounded-bottom p-0 vp-body">
                    
                    <div class=" p-4 border-bottom shadow-sm vp-border-color">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Título</span>
                                <h4 id="vista_titulo" class=" mb-0 fw-bold text-wrap" style="word-break: break-word;">---</h4>
                            </div>
                            <span id="vista_prioridad" class="badge fs-6 px-3 py-2 shadow-sm text-nowrap">---</span>
                        </div>
                    </div>

                    <div class="px-4 py-3 border-bottom vp-border-color">
                        <div class="row text-center text-md-start">
                            <div class="col-md-6 mb-2 mb-md-0 border-md-end">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start text-muted">
                                    <i class="bi bi-person-fill me-2 "></i>
                                    <span class="fw-semibold me-1">Autor:</span>
                                    <span id="vista_autor" class="fw-bold ">---</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start text-muted ms-md-3">
                                    <i class="bi bi-calendar-event me-2 "></i>
                                    <span class="fw-semibold me-1">Fecha:</span>
                                    <span id="vista_fecha" class="fw-bold ">---</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="contenedor_imagen" class="text-center border-bottom p-4 vp-border-color" style="display: none;">
                        <img id="vista_imagen" src="" class="img-fluid border rounded shadow-sm" style="max-height: 400px; object-fit: contain;" alt="Vista previa de la imagen" onerror="this.style.display='none'; document.getElementById('mensaje_error_imagen').classList.remove('d-none');">
                        <p id="mensaje_error_imagen" class=" d-none mt-2 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> No se pudo cargar la imagen.
                        </p>
                    </div>

                    <div class="p-4 ">
                        <h6 class="fw-bold text-secondary mb-3">
                            <i class="bi bi-justify-left me-2"></i>Descripción
                        </h6>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4  rounded">
                                <div id="vista_descripcion" class=" card-content-text text-wrap" style="font-size: 1rem; line-height: 1.7; word-break: break-word; white-space: pre-wrap;">---</div>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-soft-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalInfoApartamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                
                <div id="info-apt-header" class="modal-header border-0 pb-4 pt-4 justify-content-center position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center text-white">
                        <i class="bi bi-door-closed fs-1 mb-2 opacity-75"></i>
                        <h4 class="modal-title fw-bold mb-0">Apt. <span id="info-apt-nro"></span></h4>
                    </div>
                </div>
                
                <div class="modal-body rounded-bottom text-center pt-4 pb-4 px-4  position-relative vp-body">
                    
                    <div class="position-absolute top-0 start-50 translate-middle">
                        <span id="info-apt-estado" class="badge rounded-pill shadow-sm px-4 py-2 fs-6 text-uppercase border border-2 border-white" style="letter-spacing: 1px;"></span>
                    </div>
                    
                    <div class="mt-3 mb-4">
                        <p class="text-muted mb-1 fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">RESIDENTE PRINCIPAL</p>
                        <div class="d-flex align-items-center justify-content-center ">
                            <i class="bi bi-user-circle fs-4 me-2 text-secondary"></i>
                            <h6 class="fw-bold mb-0 fs-5 text-wrap" id="info-apt-residente" style="text-transform: capitalize;">---</h6>
                        </div>
                    </div>
                    
                    <div id="info-apt-caja-deuda" class="p-3 rounded-4 mt-2 mb-1 transition-all">
                        <p class="mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px; opacity: 0.8;">Estado de Cuenta</p>
                        <h3 class="fw-bolder mb-0" id="info-apt-deuda" style="letter-spacing: -0.5px;">0.00 Bs.</h3>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <i id="info-apt-icono-deuda" class="bi bi-check-circle me-1"></i>
                            <small id="info-apt-mensaje-deuda" class="fw-bold">Al día</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-soft-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

	<script type="text/javascript" src="<?php echo URL_BASE; ?>recursos/dependencias/chartjs/chart.js"></script>
	<script src="<?php echo URL_BASE; ?>recursos/js/consultas_ajax/inicio_ajax.js"></script>
</body>
</html>