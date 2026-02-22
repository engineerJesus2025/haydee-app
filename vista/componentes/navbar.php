<?php use haydee\ayuda\Sesiones; ?>
<div class="header body-pd" id="header">
    <div class="l-navbar show" id="nav-bar">
        <nav class="nav ps-3">
            <div>
                <?php echo ($_GET["pagina"] == "") ? "active" : ''; ?>
                <a href="?pagina=inicio_controlador.php&accion=inicio"
                    class="nav_logo <?php echo ($_GET["pagina"] == "inicio_controlador.php") ? "active" : ''; ?>"
                    title="Inicio">
                    <i class="bi bi-buildings nav_logo-icon"></i>
                    <span class="nav_logo-name">Inicio</span>
                </a>
                <div class="nav_list">
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
                    <a href="?pagina=mensualidad_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "mensualidad_controlador.php")?"active":''; ?>" title="Mensualidad"> 
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
                            <i class="bi bi-building nav_logo-icon"></i>
                            <span class="nav_name">Apartamentos</span>
                        </a>
                    <?php endif; ?>                    
                    <?php if (Sesiones::tienePermiso(GESTIONAR_SOLICITUD_GASTO, CONSULTAR)) : ?>
                    <a href="?pagina=solicitud_gasto_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "solicitud_gasto_controlador.php")?"active":''; ?>" title="Solicitud Gasto"> 
                        <i class="bi-clipboard-check nav_logo-icon"></i>
                        <span class="nav_name">Solicitud Gasto</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Sesiones::tienePermiso(GESTIONAR_PRESUPUESTO, CONSULTAR)) : ?>
                    <a href="?pagina=presupuesto_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "presupuesto_controlador.php")?"active":''; ?>" title="Presupuesto Mensual"> 
                        <i class="bi bi-calculator nav_logo-icon"></i>
                        <span class="nav_name">Presupuesto<br> Mensual</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Sesiones::tienePermiso(GESTIONAR_ANIO_FISCAL, CONSULTAR)) : ?>
                    <a href="?pagina=anio_fiscal_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "anio_fiscal_controlador.php")?"active":''; ?>" title="Año Fiscal"> 
                        <i class="bi-calendar-range nav_logo-icon"></i>
                        <span class="nav_name">Año Fiscal</span> 
                    </a>
                    <?php endif; ?>

                    <?php if (Sesiones::tienePermiso(GESTIONAR_REPORTES, CONSULTAR)) : 
                        $reporte = ($_GET["accion"] === "reportes_pdf")?1:0;
                        $reporte_estadistico = (in_array($_GET["accion"], ['reportes_estadisticos','habitantes','ingreso_egreso'],true))?1:0;
                        ?>
                    <a title="Reportes" class="nav_link" data-bs-toggle="collapse" href="#collapse_reporte" role="button" aria-expanded="false" aria-controls="collapse_reporte"> 
                        <i class="bi bi-card-checklist nav_logo-icon"></i>
                        <span class="nav_name">Reportes</span> 
                    </a>
                    <div class="collapse my-2 ms-4 py-2 bg-light rounded <?php echo ($reporte || $reporte_estadistico)?'show':'';?>" id="collapse_reporte" style="width: auto;"> 
                            <a href="?pagina=reportes_controlador.php&accion=reportes_pdf" class="nav_link <?php echo ($reporte)?'active':''; ?>" title="Reportes PDF"> 
                                <i class="bi bi-filetype-pdf nav_logo-icon text-dark"></i>
                                <span class="nav_name">Reportes PDF</span> 
                            </a>
                            <a href="?pagina=reportes_controlador.php&accion=reportes_estadisticos" class="nav_link <?php echo ($reporte_estadistico)?'active':''; ?>" title="Reportes Estadísticos"> 
                                <i class="bi bi-clipboard-data nav_logo-icon text-dark"></i>
                                <span class="nav_name">Reportes<br>Estadísticos</span> 
                            </a>
                        </div>
                    <?php endif; ?>



                    <?php if (Sesiones::tienePermiso(GESTIONAR_CONFIGURACION, CONSULTAR)): 
                        $configuracion = ($_GET["pagina"] == "proveedores_controlador.php" || $_GET["pagina"] == "bancos_controlador.php" || $_GET["pagina"] == "tipo_gasto_controlador.php")?true:false;
                        ?>
                        <a class="nav_link" title="Configuración" data-bs-toggle="collapse" href="#collapse_configuracion" role="button" aria-expanded="false" aria-controls="collapse_configuracion">
                            <i class="bi bi-gear-wide-connected nav_logo-icon"></i>
                            <span class="nav_name">Configuración</span>
                        </a>
                        <div class="collapse my-2 ms-4 py-2 bg-light rounded <?php echo ($configuracion)?'show':'';?>" id="collapse_configuracion" style="width: auto;">
                            <?php if (Sesiones::tienePermiso(GESTIONAR_PROVEEDORES, CONSULTAR)): ?>
                            <a href="?pagina=proveedores_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "proveedores_controlador.php")?"active":''; ?>" title="Gestionar Proveedores"> 
                                <i class="bi bi-truck nav_logo-icon text-dark"></i>
                                <span class="nav_name">Proveedores</span> 
                            </a>
                            <?php endif; ?>
                            <?php if (Sesiones::tienePermiso(GESTIONAR_BANCOS, CONSULTAR)): ?>
                            <a href="?pagina=bancos_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "bancos_controlador.php")?"active":''; ?>" title="Gestionar Bancos"> 
                                <i class="bi bi-piggy-bank nav_logo-icon text-dark"></i>
                                <span class="nav_name">Bancos</span> 
                            </a>
                            <?php endif; ?>
                            <?php if (Sesiones::tienePermiso(GESTIONAR_TIPO_GASTO, CONSULTAR)): ?>
                            <a href="?pagina=tipo_gasto_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "tipo_gasto_controlador.php")?"active":''; ?>" title="Gestionar Tipo de Gasto"> 
                                <i class="bi bi-columns-gap nav_logo-icon text-dark"></i>
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
                        <a class="nav_link" title="Seguridad" data-bs-toggle="collapse" href="#collapse_seguridad" role="button" aria-expanded="false" aria-controls="collapse_seguridad">
                            <i class="bi bi-shield-fill-check nav_logo-icon"></i>
                            <span class="nav_name">Seguridad</span>
                        </a>
                        <div class="collapse my-2 ms-4 py-2 bg-light rounded <?php echo ($seguridad)?'show':'';?>" id="collapse_seguridad" style="width: auto;">
                            <?php if (Sesiones::tienePermiso(GESTIONAR_ROLES, CONSULTAR)): ?>
                            <a href="?pagina=rol_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "rol_controlador.php")?"active":''; ?>" title="Gestionar Roles"> 
                                <i class="bi bi-person-gear nav_logo-icon text-dark"></i>
                                <span class="nav_name">Roles</span> 
                            </a>
                            <?php endif; ?>
                            <?php if (Sesiones::tienePermiso(GESTIONAR_BITACORA, CONSULTAR)): ?>
                            <a href="?pagina=bitacora_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "bitacora_controlador.php")?"active":''; ?>" title="Gestionar Bitacora"> 
                                <i class="bi bi-arrows-move nav_logo-icon text-dark"></i>
                                <span class="nav_name">Bitácora</span> 
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
        <div class="barra_inferior"></div>
    </div>
</div>