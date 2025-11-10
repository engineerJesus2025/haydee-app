<?php
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();

use haydee\modelo\Mensualidad;
use haydee\modelo\CarteleraVirtual;

if (isset($_POST["operacion"])) {
  $operacion = $_POST["operacion"];

  if ($operacion == "consulta_inicio") {
    $cartelera_virtual_obj = new CarteleraVirtual();

  	$limite = $_POST["limite"];

    echo json_encode($cartelera_virtual_obj->consultar_inicio($limite));
  }
  if ($operacion == "consulta_inicio_grafico") {
    $mensualidad_obj = new Mensualidad();
    echo json_encode($mensualidad_obj->realizar_consulta('consultar_estadisticas_inicio'));
  }
  exit();
}

require_once "vista/inicio/inicio_vista.php";

?>