<?php 
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();

use haydee\modelo\Conexion;

$conexion = new Conexion();

if (isset($_POST["operacion"])){
    $operacion = $_POST["operacion"];

    if ($operacion == "generar_copia_seguridad"){
        $db = $_POST["db"];

        if ($db == "seguridad" || $db == "negocio") {
            echo json_encode($conexion->generarCopiaSeguridad($db));
        }
        else{
            return ["estatus"=>false,"mensaje"=>"No existe la Base de datos seleccionada"];;
        }
    }
    if ($operacion == "descargar_copia_seguridad"){
        $db = $_POST["db"];
        if ($db == "seguridad" || $db == "negocio") {
            $conexion->descargarCopiaSeguridad($db);
        }
        else{
            return ["estatus"=>false,"mensaje"=>"No existe la Base de datos seleccionada"];;
        }        
    }
    if ($operacion == "obtener_copias") {
        echo json_encode($conexion->obtenerCopias());
    }
    if ($operacion == "importar_copia_seguridad"){
        $db = $_POST["db"];
        $fichero = $_POST["fichero"];

        if ($db == "seguridad" || $db == "negocio") {            
            echo json_encode($conexion->importarCopiaSeguridad($db,$fichero));
        }
        else{
            return ["estatus"=>false,"mensaje"=>"No existe la Base de datos seleccionada"];;
        }

        echo json_encode($conexion->importarCopiaSeguridad($db,$fichero));
    }

    if ($operacion == "importar_archivo_sql"){
        //Forma de saber la base de datos
        if ($_FILES['fichero']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(["estatus"=>false,"mensaje"=>"El archivo no se subio correctamente."]);
            exit;
        }

        $extension = pathinfo($_FILES['fichero']['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'sql') {
            echo json_encode(["estatus"=>false,"mensaje"=>"El archivo debe ser en formato sql."]);
            exit;
        }

        $max_size = 50 * 1024 * 1024;
        if ($_FILES['fichero']['size'] > $max_size) {
            echo json_encode(["estatus"=>false,"mensaje"=>"El archivo no debe pesar mas de 50 MB."]);
            exit;
        }

        $contenido_sql = file_get_contents($_FILES['fichero']['tmp_name']);
        if ($contenido_sql === false) {
            echo json_encode(["estatus"=>false,"mensaje"=>"Ha ocurrido un error al tratar de leer el archivo."]);
            exit;
        }
        
        if (str_contains($contenido_sql, "Database: seguridad_haydee_db")) {
            $db = "seguridad";
        } 
        else {
            $db = "negocio";
        }

        echo json_encode($conexion->importarSQL($contenido_sql,$db));
    }

    exit;
}

$conexion->registrar_bitacora(CONSULTAR, GESTIONAR_MANTENIMIENTO, "COPIAS DE SEGURIDAD");

require_once 'vista/mantenimiento/mantenimiento_vista.php';

?>