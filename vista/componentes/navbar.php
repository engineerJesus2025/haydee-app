<div class="header body-pd" id="header">
    <div class="l-navbar show" id="nav-bar">
        <nav class="nav">
            <div>
                <?php echo ($_GET["pagina"] == "")?"active":''; ?>
                <a href="?pagina=inicio_controlador.php&accion=inicio" class="nav_logo <?php echo ($_GET["pagina"] == "inicio_controlador.php")?"active":''; ?>" title="Inicio"> 
                    <i class="bi bi-buildings nav_logo-icon"></i>
                    <span class="nav_logo-name">Inicio</span> 
                </a>
                <div class="nav_list">
                    <?php if (Conexion::tiene_permiso(GESTIONAR_PAGOS, CONSULTAR)) : ?>
                    <a href="?pagina=pagos_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "pagos_controlador.php")?"active":''; ?>" title="Pagos"> 
                        <i class="bi bi-cash-coin nav_logo-icon"></i>
                        <span class="nav_name">Pagos</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_GASTOS, CONSULTAR)) : ?>
                    <a href="?pagina=gastos_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "gastos_controlador.php")?"active":''; ?>" title="Gastos"> 
                        <i class="bi bi-cart-plus nav_logo-icon"></i>
                        <span class="nav_name">Gastos</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_CONCILIACION_BANCARIA, CONSULTAR)) : ?>
                    <a href="?pagina=conciliacion_bancaria_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "conciliacion_bancaria_controlador.php")?"active":''; ?>" title="Conciliacion Bancaria"> 
                        <i class="bi bi-bank2 nav_logo-icon"></i>
                        <span class="nav_name">Conciliación<br>Bancaria</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_MENSUALIDAD, CONSULTAR)) : ?> 
                    <a href="?pagina=mensualidad_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "mensualidad_controlador.php")?"active":''; ?>" title="Control de Caja"> 
                        <i class="bi bi-piggy-bank-fill nav_logo-icon"></i>
                        <span class="nav_name">Mensualidad</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_CARTELERA_VIRTUAL, CONSULTAR)) : ?>
                    <a href="?pagina=cartelera_virtual_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "cartelera_virtual_controlador.php")?"active":''; ?>" title="Cartelera Virtual"> 
                        <i class="bi bi-tv nav_logo-icon"></i>
                        <span class="nav_name">Cartelera Virtual</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_HABITANTES, CONSULTAR)) : ?>
                    <a href="?pagina=habitantes_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "habitantes_controlador.php")?"active":''; ?>" title="Habitantes"> 
                        <i class="bi bi-people-fill nav_logo-icon"></i>
                        <span class="nav_name">Habitantes</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_PROPIETARIOS, CONSULTAR)) : ?>
                    <a href="?pagina=propietarios_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "propietarios_controlador.php")?"active":''; ?>" title="Propietarios"> 
                        <i class="bi bi-person-vcard-fill nav_logo-icon"></i>
                        <span class="nav_name">Propietarios</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_REPORTES, CONSULTAR)) : ?>
                    <a href="?pagina=reportes_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "propietarios_controlador.php")?"active":''; ?>" title="Propietarios"> 
                        <i class="bi bi-card-checklist nav_logo-icon"></i>
                        <span class="nav_name">Reportes</span> 
                    </a>
                    <?php endif; ?>
                    <?php if (Conexion::tiene_permiso(GESTIONAR_CONFIGURACION, CONSULTAR)) : ?>
                    <a href="?pagina=configuracion_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "configuracion_controlador.php")?"active":''; ?>" title="Configuración"> 
                        <i class="bi bi-gear-wide-connected nav_logo-icon"></i>
                        <span class="nav_name">Configuración</span> 
                    </a>
                    <?php endif; ?>
                <?php if (Conexion::tiene_permiso(GESTIONAR_USUARIOS, CONSULTAR)) : ?>
                    <a href="?pagina=usuario_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "usuario_controlador.php")?"active":''; ?>" title="Usuarios"> 
                        <i class="bi bi-person-badge-fill nav_logo-icon"></i>
                        <span class="nav_name">Usuarios</span> 
                    </a>
                <?php endif; ?>
                <?php if (Conexion::tiene_permiso(GESTIONAR_SEGURIDAD, CONSULTAR)) : ?>
                    <a href="?pagina=seguridad_controlador.php&accion=inicio" class="nav_link <?php echo ($_GET["pagina"] == "seguridad_controlador.php")?"active":''; ?>" title="Seguridad"> 
                        <i class="bi bi-shield-fill-check nav_logo-icon"></i>
                        <span class="nav_name">Seguridad</span> 
                    </a>
                <?php endif; ?>
                </div>
            </div> 
        </nav>
    </div>
</div>