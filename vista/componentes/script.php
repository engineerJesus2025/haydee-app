<!-- Dependencias -->
<script src="<?php echo URL_BASE; ?>recursos/dependencias/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/dependencias/sweetalert2/sweetalert2.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/dependencias/tabulator/tabulator.min.js"></script>
<!-- Helpers -->
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Patrones.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Alertas.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Peticiones.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/EstadoInputs.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Validador.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Tooltips.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Tablas.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/FormatoFechas.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/ayuda/Notificaciones.js"></script>
<!-- Script personalizados globales -->
<script src="<?php echo URL_BASE; ?>recursos/js/header.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/notificaciones.js"></script>
<script src="<?php echo URL_BASE; ?>recursos/js/driver.js"></script>

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
?>