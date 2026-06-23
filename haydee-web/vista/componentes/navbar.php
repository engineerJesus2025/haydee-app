<?php 
use haydee\ayuda\Sesiones; 

$sub_reporte_pdf = ($modulo === "reportes" && $accion === "reportes_pdf");
$sub_reporte_est = ($modulo === "reportes" && in_array($accion, ["reportes_estadisticos", "habitantes", "ingreso_egreso"]));
$es_reporte      = ($sub_reporte_pdf || $sub_reporte_est);

$es_configuracion = in_array($modulo, ["proveedores", "bancos", "tipo_gasto"]);
$es_seguridad     = in_array($modulo, ["rol", "bitacora", "permisos", "modulos"]); 

//  MATRIZ DE ELEMENTOS (Sacada de config_menu)
$elementos_menu = obtenerElementosMenu($modulo, $accion);
?>

<div class="l-navbar show" id="nav-bar">
    <nav class="nav">
        <div class="nav_logo_container w-sm-100">
            <a title="Inicio" href="?pagina=inicio&accion=inicio" class="nav_logo text-decoration-none d-flex align-items-center ps-2">
                <div class="d-flex align-items-center justify-content-center rounded bg-primary logo_box mx-0" style="min-width: 40px; height: 40px;">
                    <i class="bi bi-buildings text-white fs-4"></i>
                </div>
                <div class="d-flex flex-column ms-2 logo_text">
                    <span class="fw-bold text-white lh-1" style="font-size: 1.1rem;">CondoHaydee</span>
                    <span class="text-secondary fw-bold mt-1" style="font-size: 0.70rem; letter-spacing: 0.5px; white-space: nowrap;">SISTEMA DE CONDOMINIO</span>
                </div>
            </a>
        </div>
        <div id="nav-accordion" class="w-100 nav_scrollable_content">
            <div class="nav_list mt-1 ps-2 pe-2">
                
                <?php foreach ($elementos_menu as $item): ?>
                    
                    <?php if (!$item['mostrar']) continue; ?>

                    <?php if ($item['tipo'] === 'enlace'): ?>
                        <a href="<?php echo $item['url']; ?>" class="nav_link <?php echo $item['activo'] ? 'active' : ''; ?>" title="<?php echo $item['titulo']; ?>">
                            <i class="bi <?php echo $item['icono']; ?> nav_logo-icon"></i>
                            <span class="nav_name"><?php echo $item['titulo']; ?></span>
                        </a>

                    <?php elseif ($item['tipo'] === 'desplegable'): ?>
                        <a title="<?php echo $item['titulo']; ?>" class="nav_link <?php echo $item['abierto'] ? '' : 'collapsed'; ?>" data-bs-toggle="collapse" href="#<?php echo $item['id_collapse']; ?>" role="button" aria-expanded="<?php echo $item['abierto'] ? 'true' : 'false'; ?>">
                            <i class="bi <?php echo $item['icono']; ?> nav_logo-icon"></i>
                            <span class="nav_name"><?php echo $item['titulo']; ?></span>
                            <i class="bi bi-chevron-right arrow-icon"></i>
                        </a>
                        
                        <div class="collapse <?php echo $item['abierto'] ? 'show' : ''; ?>" id="<?php echo $item['id_collapse']; ?>" data-bs-parent="#nav-accordion">
                            <?php foreach ($item['submenus'] as $sub): ?>
                                <?php if (!$sub['mostrar']) continue; ?>
                                
                                <a title="<?php echo $sub['titulo']; ?>" href="<?php echo $sub['url']; ?>" class="nav_link <?php echo $sub['activo'] ? 'active' : ''; ?>">
                                    <i class="bi <?php echo $sub['icono']; ?> nav_logo-icon"></i>
                                    <span class="nav_name"><?php echo $sub['titulo']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php endif; ?>

                <?php endforeach; ?>

            </div>
        </div>
    </nav>
</div>