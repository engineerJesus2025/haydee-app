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

    // ----------- REGISTRAR ---------------
    if ($operacion == "registrar") {
        $titulo = $_POST["titulo"];
        $descripcion = $_POST["descripcion"];
        $fecha = $_POST["fecha"];
        $prioridad = $_POST["prioridad"];

        $nombre_archivo = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $nombre_archivo = $nombre_sanitizado . '_' . time() . '.' . $extension;

            $ruta_destino = "recursos/img/";
            if (!is_dir($ruta_destino)) {
                mkdir($ruta_destino, 0777, true);
            }

            move_uploaded_file($temporal, $ruta_destino . $nombre_archivo);
        }

        $cartelera_virtual_obj->set_titulo($titulo);
        $cartelera_virtual_obj->set_descripcion($descripcion);
        $cartelera_virtual_obj->set_fecha($fecha);
        $cartelera_virtual_obj->set_imagen($nombre_archivo);
        $cartelera_virtual_obj->set_prioridad($prioridad);

        echo json_encode($cartelera_virtual_obj->registrar());
    } elseif ($operacion == "consulta_especifica") {
        //se guardan el id para buscar
        $id_cartelera = $_POST["id_cartelera"];

        //se usan el setter correspondientes
        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);

        // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
        echo json_encode($cartelera_virtual_obj->consultar_cartelera_id());
        // igual hice para que retorne un arreglo, si sale vacio solo mandara un array con false

        // ----------- MODIFICAR ---------------
    } elseif ($operacion == "modificar") {
        $id_cartelera = $_POST["id_cartelera"];
        $titulo = $_POST["titulo"];
        $descripcion = $_POST["descripcion"];
        $fecha = $_POST["fecha"];
        $prioridad = $_POST["prioridad"];
        $eliminar_imagen = isset($_POST["eliminar_imagen"]);

        $nombre_archivo = $cartelera_virtual_obj->obtener_imagen_actual();
        if (!empty($nombre_archivo)) {
            $ruta_imagen = "recursos/img/" . $nombre_archivo;

            // Eliminar imagen si el usuario lo pidió y el archivo existe
            if ($eliminar_imagen && file_exists($ruta_imagen) && is_file($ruta_imagen)) {
                unlink($ruta_imagen);
                $nombre_archivo = '';
            }
        } else {
            $ruta_imagen = ''; // No hay imagen previa
        }

        // ✅ Reemplazo de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            // Si hay imagen previa, elimínala
            if (!empty($nombre_archivo) && file_exists("recursos/img/" . $nombre_archivo) && is_file("recursos/img/" . $nombre_archivo)) {
                unlink("recursos/img/" . $nombre_archivo);
            }

            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $nombre_archivo = $nombre_sanitizado . '_' . time() . '.' . $extension;

            move_uploaded_file($temporal, "recursos/img/" . $nombre_archivo);
        }

        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);
        $cartelera_virtual_obj->set_titulo($titulo);
        $cartelera_virtual_obj->set_descripcion($descripcion);
        $cartelera_virtual_obj->set_fecha($fecha);
        $cartelera_virtual_obj->set_imagen($nombre_archivo);
        $cartelera_virtual_obj->set_prioridad($prioridad);

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