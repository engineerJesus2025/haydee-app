<?php

require_once "modelo/cartelera_virtual_modelo.php";
require_once "modelo/usuario_modelo.php";

$cartelera_virtual_obj = new Cartelera_virtual();
$usuario_obj = new Usuario(); //objeto usuario
$usuarios = $usuario_obj->consultar(); // consulta todos los usuarios

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta") {
        // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
        echo json_encode($cartelera_virtual_obj->consultar());
        // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
    }
    $usuario_id = $_SESSION["id_usuario"];
    $cartelera_virtual_obj->set_usuario_id($usuario_id);

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
        $cartelera_virtual_obj->set_titulo($titulo);
        $cartelera_virtual_obj->set_descripcion($descripcion);
        $cartelera_virtual_obj->set_fecha($fecha);
        $cartelera_virtual_obj->set_imagen($nombre_archivo);
        $cartelera_virtual_obj->set_prioridad($prioridad);

        //se ejecuta la funcion:    
        echo json_encode($cartelera_virtual_obj->registrar());
    } elseif ($operacion == "consulta_especifica") {
        //se guardan el id para buscar
        $id_cartelera = $_POST["id_cartelera"];

        //se usan el setter correspondientes
        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);

        // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
        echo json_encode($cartelera_virtual_obj->consultar_cartelera_id());
        // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false
    } elseif ($operacion == "modificar") {
        //se guardan las variables a modificar
        $id_cartelera = $_POST["id_cartelera"];
        $titulo = $_POST["titulo"];
        $descripcion = $_POST["descripcion"];
        $fecha = $_POST["fecha"];
        $prioridad = $_POST["prioridad"];

        //IMAGEN
        $nombre_archivo = $cartelera_virtual_obj->obtener_imagen_actual(); // necesitas implementar esto

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_archivo = uniqid('img_') . '.' . $extension;
            move_uploaded_file($temporal, "recursos/img/" . $nombre_archivo);
        }

        // Luego
        $cartelera_virtual_obj->set_imagen($nombre_archivo);

        //se usan los setters correspondientes
        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);
        $cartelera_virtual_obj->set_titulo($titulo);
        $cartelera_virtual_obj->set_descripcion($descripcion);
        $cartelera_virtual_obj->set_fecha($fecha);
        $cartelera_virtual_obj->set_imagen($nombre_archivo);
        $cartelera_virtual_obj->set_prioridad($prioridad);
        //se ejecuta la funcion:    
        echo json_encode($cartelera_virtual_obj->editar_publicacion());
    } elseif ($operacion == "eliminar") {
        //se guardan el id para eliminar
        $id_cartelera = $_POST["id_cartelera"];

        //se usan el setter correspondientes
        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);

        // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
        echo json_encode($cartelera_virtual_obj->eliminar_publicacion());
    } elseif ($operacion == "ultimo_id") {
        echo json_encode($cartelera_virtual_obj->lastId());
    }

    exit;

}
require_once "vista/cartelera_virtual/cartelera_virtual_vista.php";

?>