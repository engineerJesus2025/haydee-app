<!-- Dependencias -->
<script src="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/dependencias/sweetalert2/sweetalert2.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/dependencias/tabulator/tabulator.min.js"></script>

<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Patrones.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Alertas.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Peticiones.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/EstadoInputs.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Validador.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Tooltips.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Tablas.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/FormatoFechas.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Notificaciones.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/AyudaInteractiva.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/AtajosTeclado.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/ComponentesUI.js"></script>

<script src="<?php echo URL_BASE; ?>recursos/js/src/header.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/src/notificaciones.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/src/driver.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/src/tema_global.js"></script>

<!-- Scripts globales -->
<!-- <script src="<?php echo URL_BASE; ?>recursos/js/dist/app-core.min.js"></script> -->

<!-- Variables VAPID: -->
<script>
    // Pasamos la variable de PHP a JavaScript de forma segura
    const PUBLIC_VAPID_KEY = "<?php echo VAPID_PUBLIC_KEY; ?>";
    const URL_BASE = "<?php echo URL_BASE; ?>";
</script>
<script src="<?php echo URL_BASE; ?>recursos/js/push_registro.js"></script>

<!-- Variable de permisos -->
<?php
// Validamos si el controlador actual definió la variable $permisosVista
if (isset($permisosVista) && is_array($permisosVista)) {
    // json_encode convierte el arreglo de PHP en un objeto JSON para JavaScript
    $permisosJson = json_encode($permisosVista);
    echo "<script> window.PermisosModulo = {$permisosJson}; </script>";
} else {
    // Si no hay permisos (ej. módulo de inicio), creamos un objeto vacío
    echo "<script> window.PermisosModulo = {}; </script>";
}
