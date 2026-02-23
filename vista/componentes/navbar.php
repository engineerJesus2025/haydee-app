<?php use haydee\ayuda\Sesiones; ?>
<div class="l-navbar show" id="nav-bar">
    <nav class="nav">
        <div class="nav_logo_container">
            <a href="?pagina=inicio_controlador.php&accion=inicio"
                class="nav_logo text-decoration-none d-flex align-items-center ps-2" title="Inicio">
                <div class="d-flex align-items-center justify-content-center rounded bg-primary shadow-sm logo_box"
                    style="min-width: 40px; height: 40px;">
                    <i class="bi bi-buildings text-white fs-4"></i>
                </div>

                <div class="d-flex flex-column ms-2 logo_text">
                    <span class="fw-bold text-white lh-1" style="font-size: 1.1rem;">CondoHaydee</span>
                    <span class="text-secondary fw-bold mt-1"
                        style="font-size: 0.70rem; letter-spacing: 0.5px; white-space: nowrap;">SISTEMA DE
                        CONDOMINIO</span>
                </div>
            </a>
        </div>

        <div id="nav-accordion" class="w-100 nav_scrollable_content">
            <div class="nav_list mt-3 ps-2 pe-2">
                <?php if (Sesiones::tienePermiso(GESTIONAR_PAGOS, CONSULTAR)): ?>
                <a href="?pagina=pagos_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "pagos_controlador.php") ? "active" : ''; ?>"
                    title="Pagos">
                    <i class="bi bi-cash-coin nav_logo-icon"></i>
                    <span class="nav_name">Pagos</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_GASTOS, CONSULTAR)): ?>
                <a href="?pagina=gastos_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "gastos_controlador.php") ? "active" : ''; ?>"
                    title="Gastos">
                    <i class="bi bi-cart-plus nav_logo-icon"></i>
                    <span class="nav_name">Gastos</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_CAJA_CHICA, CONSULTAR)): ?>
                <a href="?pagina=caja_chica_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "caja_chica_controlador.php") ? "active" : ''; ?>"
                    title="Caja Chica">
                    <i class="bi bi-bank2 nav_logo-icon"></i>
                    <span class="nav_name">Caja Chica</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_MENSUALIDAD, CONSULTAR)) : ?>
                <a href="?pagina=mensualidad_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "mensualidad_controlador.php")?"active":''; ?>"
                    title="Mensualidad">
                    <i class="bi bi-piggy-bank-fill nav_logo-icon"></i>
                    <span class="nav_name">Mensualidad</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_CARTELERA_VIRTUAL, CONSULTAR)): ?>
                <a href="?pagina=cartelera_virtual_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "cartelera_virtual_controlador.php") ? "active" : ''; ?>"
                    title="Cartelera Virtual">
                    <i class="bi bi-tv nav_logo-icon"></i>
                    <span class="nav_name">Cartelera Virtual</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_APARTAMENTOS, CONSULTAR)): ?>
                <a href="?pagina=apartamentos_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "apartamentos_controlador.php") ? "active" : ''; ?>"
                    title="Apartamentos">
                    <i class="bi bi-door-open nav_logo-icon"></i>
                    <span class="nav_name">Apartamentos</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_SOLICITUD_GASTO, CONSULTAR)) : ?>
                <a href="?pagina=solicitud_gasto_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "solicitud_gasto_controlador.php")?"active":''; ?>"
                    title="Solicitud Gasto">
                    <i class="bi bi-clipboard-check nav_logo-icon"></i>
                    <span class="nav_name">Solicitud Gasto</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_PRESUPUESTO, CONSULTAR)) : ?>
                <a href="?pagina=presupuesto_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "presupuesto_controlador.php")?"active":''; ?>"
                    title="Presupuesto Mensual">
                    <i class="bi bi-calculator nav_logo-icon"></i>
                    <span class="nav_name">Presupuesto</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_ANIO_FISCAL, CONSULTAR)) : ?>
                <a href="?pagina=anio_fiscal_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "anio_fiscal_controlador.php")?"active":''; ?>"
                    title="Año Fiscal">
                    <i class="bi bi-calendar-range nav_logo-icon"></i>
                    <span class="nav_name">Año Fiscal</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_REPORTES, CONSULTAR)) : 
                    $reporte = ($_GET["accion"] === "reportes_pdf")?1:0;
                    $reporte_estadistico = (in_array($_GET["accion"], ['reportes_estadisticos','habitantes','ingreso_egreso'],true))?1:0;
                    $menu_abierto = ($reporte || $reporte_estadistico);
                    ?>
                <a title="Reportes" class="nav_link <?php echo $menu_abierto ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#collapse_reporte" role="button"
                    aria-expanded="<?php echo $menu_abierto ? 'true' : 'false'; ?>">
                    <i class="bi bi-card-checklist nav_logo-icon"></i>
                    <span class="nav_name">Reportes</span>
                    <i class="bi bi-chevron-right arrow-icon"></i>
                </a>
                <div class="collapse <?php echo ($menu_abierto)?'show':'';?>" id="collapse_reporte" data-bs-parent="#nav-accordion">
                    <a href="?pagina=reportes_controlador.php&accion=reportes_pdf"
                        class="nav_link <?php echo ($reporte)?'active':''; ?>">
                        <i class="bi bi-filetype-pdf nav_logo-icon"></i>
                        <span class="nav_name">Reportes PDF</span>
                    </a>
                    <a href="?pagina=reportes_controlador.php&accion=reportes_estadisticos"
                        class="nav_link <?php echo ($reporte_estadistico)?'active':''; ?>">
                        <i class="bi bi-clipboard-data nav_logo-icon"></i>
                        <span class="nav_name">Estadísticos</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_CONFIGURACION, CONSULTAR)): 
                    $configuracion = ($_GET["pagina"] == "proveedores_controlador.php" || $_GET["pagina"] == "bancos_controlador.php" || $_GET["pagina"] == "tipo_gasto_controlador.php")?true:false;
                    ?>
                <a class="nav_link <?php echo $configuracion ? '' : 'collapsed'; ?>" title="Configuración"
                    data-bs-toggle="collapse" href="#collapse_configuracion" role="button"
                    aria-expanded="<?php echo $configuracion ? 'true' : 'false'; ?>">
                    <i class="bi bi-gear-wide-connected nav_logo-icon"></i>
                    <span class="nav_name">Configuración</span>
                    <i class="bi bi-chevron-right arrow-icon"></i>
                </a>
                <div class="collapse <?php echo ($configuracion)?'show':'';?>" id="collapse_configuracion" data-bs-parent="#nav-accordion">
                    <?php if (Sesiones::tienePermiso(GESTIONAR_PROVEEDORES, CONSULTAR)): ?>
                    <a href="?pagina=proveedores_controlador.php&accion=inicio"
                        class="nav_link <?php echo ($_GET["pagina"] == "proveedores_controlador.php")?"active":''; ?>">
                        <i class="bi bi-truck nav_logo-icon"></i>
                        <span class="nav_name">Proveedores</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Sesiones::tienePermiso(GESTIONAR_BANCOS, CONSULTAR)): ?>
                    <a href="?pagina=bancos_controlador.php&accion=inicio"
                        class="nav_link <?php echo ($_GET["pagina"] == "bancos_controlador.php")?"active":''; ?>">
                        <i class="bi bi-bank nav_logo-icon"></i>
                        <span class="nav_name">Bancos</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Sesiones::tienePermiso(GESTIONAR_TIPO_GASTO, CONSULTAR)): ?>
                    <a href="?pagina=tipo_gasto_controlador.php&accion=inicio"
                        class="nav_link <?php echo ($_GET["pagina"] == "tipo_gasto_controlador.php")?"active":''; ?>">
                        <i class="bi bi-columns-gap nav_logo-icon"></i>
                        <span class="nav_name">Tipo de Gasto</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_USUARIOS, CONSULTAR)): ?>
                <a href="?pagina=usuario_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET['pagina'] == 'usuario_controlador.php' || $_GET['accion'] == 'perfil') ? 'active' : ''; ?>"
                    title="Usuarios">
                    <i class="bi bi-person-badge-fill nav_logo-icon"></i>
                    <span class="nav_name">Usuarios</span>
                </a>
                <?php endif; ?>

                <?php if (Sesiones::tienePermiso(GESTIONAR_SEGURIDAD, CONSULTAR)): 
                    $seguridad = ($_GET["pagina"] == "rol_controlador.php" || $_GET["pagina"] == "bitacora_controlador.php")?true:false;
                    ?>
                <a class="nav_link <?php echo $seguridad ? '' : 'collapsed'; ?>" title="Seguridad"
                    data-bs-toggle="collapse" href="#collapse_seguridad" role="button"
                    aria-expanded="<?php echo $seguridad ? 'true' : 'false'; ?>">
                    <i class="bi bi-shield-fill-check nav_logo-icon"></i>
                    <span class="nav_name">Seguridad</span>
                    <i class="bi bi-chevron-right arrow-icon"></i>
                </a>
                <div class="collapse <?php echo ($seguridad)?'show':'';?>" id="collapse_seguridad" data-bs-parent="#nav-accordion">
                    <?php if (Sesiones::tienePermiso(GESTIONAR_ROLES, CONSULTAR)): ?>
                    <a href="?pagina=rol_controlador.php&accion=inicio"
                        class="nav_link <?php echo ($_GET["pagina"] == "rol_controlador.php")?"active":''; ?>">
                        <i class="bi bi-person-gear nav_logo-icon"></i>
                        <span class="nav_name">Roles</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Sesiones::tienePermiso(GESTIONAR_BITACORA, CONSULTAR)): ?>
                    <a href="?pagina=bitacora_controlador.php&accion=inicio"
                        class="nav_link <?php echo ($_GET["pagina"] == "bitacora_controlador.php")?"active":''; ?>">
                        <i class="bi bi-journal-text nav_logo-icon"></i>
                        <span class="nav_name">Bitácora</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Sesiones::tienePermiso(GESTIONAR_PERMISOS, CONSULTAR)): ?>
                    <a href="?pagina=permisos_controlador.php&accion=inicio"
                        class="nav_link <?php echo ($_GET["pagina"] == "permisos_controlador.php")?"active":''; ?>">
                        <i class="bi bi-key-fill nav_logo-icon"></i>
                        <span class="nav_name">Permisos</span>
                    </a>
                    <?php endif; ?>
                    <?php if (Sesiones::tienePermiso(GESTIONAR_MODULOS, CONSULTAR)): ?>
                    <a href="?pagina=modulos_controlador.php&accion=inicio"
                        class="nav_link <?php echo ($_GET["pagina"] == "modulos_controlador.php")?"active":''; ?>">
                        <i class="bi bi-stack nav_logo-icon"></i>
                        <span class="nav_name">Módulos</span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <a href="?pagina=notificaciones_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "notificaciones_controlador.php") ? "active" : ''; ?>"
                    title="Notificaciones">
                    <i class="bi bi-bell-fill nav_logo-icon"></i>
                    <span class="nav_name">Notificaciones</span>
                </a>

                <?php if (Sesiones::tienePermiso(GESTIONAR_MANTENIMIENTO, CONSULTAR)): ?>
                <a href="?pagina=mantenimiento_controlador.php&accion=inicio"
                    class="nav_link <?php echo ($_GET["pagina"] == "mantenimiento_controlador.php") ? "active" : ''; ?>"
                    title="Mantenimiento">
                    <i class="bi bi-tools nav_logo-icon"></i>
                    <span class="nav_name">Mantenimiento</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</div>