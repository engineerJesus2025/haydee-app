<?php 

require_once "modelo/conexion.php";

$conexion = new Conexion();

if (isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];

        if ($operacion == "generar_copia_seguridad"){
            $db = $_POST["db"];
            echo json_encode($conexion->generarCopiaSeguridad($db));
        }
        if ($operacion == "obtener_copias") {
        	echo json_encode($conexion->obtenerCopias());
        }
        if ($operacion == "importar_copia_seguridad"){
            $db = $_POST["db"];
            $fichero = $_POST["fichero"];

            echo json_encode($conexion->importarCopiaSeguridad($db,$fichero));
        }

        exit;//es salida en ingles... No puede faltar
    }

require_once 'vista/mantenimiento/mantenimiento_vista.php';
 ?>