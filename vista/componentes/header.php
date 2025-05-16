<div class="header" id="header">
	<div class="l-navbar" id="nav-bar">
	    <nav class="nav">
	        <div>
	        	<a href="?pagina=inicio_controlador.php&accion=inicio" class="nav_logo" title="Inicio"> 
		        	<i class="bi bi-buildings nav_logo-icon"></i>
		        	<span class="nav_logo-name">Inicio</span> 
	        	</a>
		        <div class="nav_list">
		        	<?php if (Conexion::tiene_permiso(GESTIONAR_PAGOS, CONSULTAR)) : ?>
		        	<a href="#" class="nav_link active" title="Pagos"> 
		        		<i class="bi bi-cash-coin nav_logo-icon"></i>
		        		<span class="nav_name">Pagos</span> 
		        	</a>
		        	<?php endif; ?>
		        	<?php if (Conexion::tiene_permiso(GESTIONAR_GASTOS, CONSULTAR)) : ?>
		        	<a href="#" class="nav_link" title="Gastos"> 
		        		<i class="bi bi-cart-plus nav_logo-icon"></i>
		        		<span class="nav_name">Gastos</span> 
		        	</a>
		        	<?php endif; ?>
		        	<?php if (Conexion::tiene_permiso(GESTIONAR_CONCILIACION_BANCARIA, CONSULTAR)) : ?>
		        	<a href="?pagina=conciliacion_bancaria_controlador.php&accion=inicio" class="nav_link" title="Conciliacion Bancaria"> 
		        		<i class="bi bi-bank2 nav_logo-icon"></i>
		        		<span class="nav_name">Conciliacion Bancaria</span> 
		        	</a>
		        	<?php endif; ?>
		        	<?php if (Conexion::tiene_permiso(GESTIONAR_EFECTIVO, CONSULTAR)) : ?> 
		        	<a href="#" class="nav_link" title="Control de Caja"> 
		        		<i class="bi bi-piggy-bank-fill nav_logo-icon"></i>
		        		<span class="nav_name">Control de Caja</span> 
		        	</a>
		        	<?php endif; ?>
		        	<?php if (Conexion::tiene_permiso(GESTIONAR_CARTELERA_VIRTUAL, CONSULTAR)) : ?>
		        	<a href="?pagina=cartelera_virtual_controlador.php&accion=inicio" class="nav_link" title="Cartelera Virtual"> 
		        		<i class="bi bi-tv nav_logo-icon"></i>
		        		<span class="nav_name">Cartelera Virtual</span> 
		        	</a>
		        	<?php endif; ?>
		        	<?php if (Conexion::tiene_permiso(GESTIONAR_HABITANTES, CONSULTAR)) : ?>
		        	<a href="#" class="nav_link" title="Habitantes"> 
		        		<i class="bi bi-people-fill nav_logo-icon"></i>
		        		<span class="nav_name">Habitantes</span> 
		        	</a>
		        	<?php endif; ?>
		        	<?php if (Conexion::tiene_permiso(GESTIONAR_PROPIETARIOS, CONSULTAR)) : ?>
		        	<a href="?pagina=propietario_controlador.php&accion=inicio" class="nav_link" title="Propietarios"> 
		        		<i class="bi bi-person-vcard-fill nav_logo-icon"></i>
		        		<span class="nav_name">Propietarios</span> 
		        	</a>
		        	<?php endif; ?>
		        	<?php if (Conexion::tiene_permiso(GESTIONAR_CONFIGURACION, CONSULTAR)) : ?>
		        	<a href="?pagina=configuracion_controlador.php&accion=inicio" class="nav_link" title="Configuración"> 
		        		<i class="bi bi-gear-wide-connected nav_logo-icon"></i>
		        		<span class="nav_name">Configuración</span> 
		        	</a>
		        	<?php endif; ?>
		        <?php if (Conexion::tiene_permiso(GESTIONAR_USUARIOS, CONSULTAR)) : ?>
		        	<a href="?pagina=usuario_controlador.php&accion=inicio" class="nav_link" title="Usuarios"> 
		        		<i class="bi bi-person-badge-fill nav_logo-icon"></i>
		        		<span class="nav_name">Usuarios</span> 
		        	</a>
		        <?php endif; ?>
		        <?php if (Conexion::tiene_permiso(GESTIONAR_SEGURIDAD, CONSULTAR)) : ?>
		        	<a href="?pagina=seguridad_controlador.php&accion=inicio" class="nav_link" title="Seguridad"> 
		        		<i class="bi bi-shield-fill-check nav_logo-icon"></i>
		        		<span class="nav_name">Seguridad</span> 
		        	</a>
		        <?php endif; ?>
		        </div>
	        </div> 
	    </nav>
	</div>
</div>