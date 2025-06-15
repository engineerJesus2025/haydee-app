<?php
require_once "vista/componentes/sesion.php";
require_once "modelo/cartelera_virtual_modelo.php";

$cartelera_virtual_obj = new Cartelera_virtual();

if (isset($_POST["operacion"])) {
  $operacion = $_POST["operacion"];

  if ($operacion == "consulta_inicio") {
  	$limite = $_POST["limite"];

    echo json_encode($cartelera_virtual_obj->consultar_inicio($limite));
  }
  exit();
}
require_once "vista/inicio/inicio_vista.php";

?>