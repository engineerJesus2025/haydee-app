<?php
require_once "vista/componentes/sesion.php";
require_once "modelo/cartelera_virtual_modelo.php";
require_once "modelo/mensualidad_modelo.php";

$cartelera_virtual_obj = new Cartelera_virtual();
$mensualidad_obj = new Mensualidad();

if (isset($_POST["operacion"])) {
  $operacion = $_POST["operacion"];

  if ($operacion == "consulta_inicio") {
  	$limite = $_POST["limite"];

    echo json_encode($cartelera_virtual_obj->consultar_inicio($limite));
  }
  if ($operacion == "consulta_inicio_grafico") {
    echo json_encode($mensualidad_obj->consultar_estadisticas_inicio());
  }
  exit();
}
	// Include Composer autoload file to load Resend SDK classes...


require_once "vista/inicio/inicio_vista.php";

?>