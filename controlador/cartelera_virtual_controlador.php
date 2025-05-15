<?php

require_once "modelo/cartelera_virtual_modelo.php";

$cartelera_virtual = new Cartelera_virtual();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta") {
        // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
        echo json_encode($cartelera_virtual->consultar());
        // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
    }
    $usuario_id = $_SESSION["id_usuario"];
    $cartelera_virtual->set_usuario_id($usuario_id);

    //Despues de cada echo se regresa al javascript como respuesta en json
    if ($operacion == "registrar") {
        //se guardan las variables a registrar
        $titulo = $_POST["titulo"];
        $descripcion = $_POST["descripcion"];
        $fecha = $_POST["fecha"];
        $prioridad = $_POST["prioridad"];
        // PROCESAR IMAGEN
        $nombre_archivo = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

            $ruta_destino = "recursos/img/";
            if (!is_dir($ruta_destino)) {
                mkdir($ruta_destino, 0777, true); // Crea la carpeta si no existe
            }

            // Crear nombre único
            $nombre_archivo = uniqid('img_') . '.' . $extension;

            // Mover archivo
            move_uploaded_file($temporal, $ruta_destino . $nombre_archivo);
        }

        //se usan los setters correspondientes
        $cartelera_virtual->set_titulo($titulo);
        $cartelera_virtual->set_descripcion($descripcion);
        $cartelera_virtual->set_fecha($fecha);
        $cartelera_virtual->set_imagen($nombre_archivo);
        $cartelera_virtual->set_prioridad($prioridad);

        //se ejecuta la funcion:    
        echo json_encode($cartelera_virtual->registrar());
    } 
    elseif ($operacion == "consulta_especifica") {
        //se guardan el id para buscar
        $id_cartelera = $_POST["id_cartelera"];

        //se usan el setter correspondientes
        $cartelera_virtual->set_id_cartelera($id_cartelera);

        // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
        echo json_encode($cartelera_virtual->consultar_cartelera_id());
        // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
    } elseif ($operacion == "modificar") {
        //se guardan las variables a modificar
        $id_cartelera = $_POST["id_cartelera"];
        $titulo = $_POST["titulo"];
        $descripcion = $_POST["descripcion"];
        $fecha = $_POST["fecha"];
        $tipo = $_POST["tipo"];
        $imagen = $_POST["imagen"];
        $prioridad = $_POST["prioridad"];
        $usuario_id = $_POST["usuario_id"];

        //se usan los setters correspondientes
        $cartelera_virtual->set_id_cartelera($id_cartelera);
        $cartelera_virtual->set_titulo($titulo);
        $cartelera_virtual->set_descripcion($descripcion);
        $cartelera_virtual->set_fecha($fecha);
        $cartelera_virtual->set_tipo($tipo);
        $cartelera_virtual->set_imagen($imagen);
        $cartelera_virtual->set_prioridad($prioridad);
        $cartelera_virtual->set_usuario_id($usuario_id);
        //se ejecuta la funcion:    
        echo json_encode($cartelera_virtual->editar_publicacion());
    } 
    elseif ($operacion == "eliminar") {
        //se guardan el id para eliminar
        $id_cartelera = $_POST["id_cartelera"];

        //se usan el setter correspondientes
        $cartelera_virtual->set_id_cartelera($id_cartelera);

        // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
        echo json_encode($cartelera_virtual->eliminar_publicacion());
    } elseif ($operacion == "ultimo_id") {
        echo json_encode($cartelera_virtual->lastId());
    }

    exit;

}
require_once "vista/cartelera_virtual/cartelera_virtual_vista.php";

?>