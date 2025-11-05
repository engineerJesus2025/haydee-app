<?php
require_once "vista/componentes/sesion.php";
require_once "modelo/cartelera_virtual_modelo.php";
require_once "modelo/usuario_modelo.php";

$cartelera_virtual_obj = new Cartelera_virtual();
$usuario_obj = new Usuario(); //objeto usuario
$usuarios = $usuario_obj->realizar_consulta('consultar'); // consulta todos los usuarios

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta") {
        $cartelera_virtual_obj->registrar_bitacora(CONSULTAR, GESTIONAR_CARTELERA_VIRTUAL, "TODAS LAS PUBLICACIONES");
        echo json_encode($cartelera_virtual_obj->realizar_consulta('consultar'));
    }
    $usuario_id = $_SESSION["id_usuario"];
    $cartelera_virtual_obj->set_usuario_id($usuario_id);


    // ----------- REGISTRAR ---------------
    if ($operacion == "registrar") {
        $titulo = $_POST["titulo"];
        $descripcion = $_POST["descripcion"];
        $fecha = $_POST["fecha"];
        $prioridad = $_POST["prioridad"];


        // PARA INSERTAR UNA IMAGEN (ESTO FUE UN PEO)
        $nombre_archivo = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

            // ESTO ES PARA QUE SE GUARDE LA IMAGEN CON EL NOMBRE ORIGINAL + UNOS NUMEROS RANDOM PARA EVITAR DUPLICACION
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $nombre_archivo = $nombre_sanitizado . '_' . time() . '.' . $extension;

            // AQUI ES DONDE SE VA A GUARDAR LA IMAGEN FISICAMENTE
            $ruta_destino = "recursos/img/cartelera/";
            // ESTO NUNCA VA A PASAR!! PERO POR SI ACASO (BASICAMENTE SI NO EXISTE LA RUTA, SE CREA)
            if (!is_dir($ruta_destino)) {
                mkdir($ruta_destino, 0777, true);
            }
            // AQUI SE CAMBIA LA RUTA DE LA IMAGEN, DE UNA IMAGEN TEMPORAL A LA RUTA DE DESTINO FISICA
            move_uploaded_file($temporal, $ruta_destino . $nombre_archivo);
        }

        $cartelera_virtual_obj->set_titulo($titulo);
        $cartelera_virtual_obj->set_descripcion($descripcion);
        $cartelera_virtual_obj->set_fecha($fecha);
        $cartelera_virtual_obj->set_imagen($nombre_archivo);
        $cartelera_virtual_obj->set_prioridad($prioridad);

        $respuesta = $cartelera_virtual_obj->realizar_consulta('registrar');

        if ($respuesta["estatus"]) {
            $cartelera_virtual_obj->registrar_bitacora(REGISTRAR, GESTIONAR_CARTELERA_VIRTUAL, $titulo . " - " . $fecha);
        }
        echo json_encode($respuesta);


    } elseif ($operacion == "consulta_especifica") {
        $id_cartelera = $_POST["id_cartelera"];

        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);

        echo json_encode($cartelera_virtual_obj->realizar_consulta('consultar_cartelera_id'));

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
            $ruta_imagen = "recursos/img/cartelera/" . $nombre_archivo;

            // Eliminar imagen si el usuario lo pidió y el archivo existe
            if ($eliminar_imagen && file_exists($ruta_imagen) && is_file($ruta_imagen)) {
                unlink($ruta_imagen);
                $nombre_archivo = '';
            }
        } else {
            $ruta_imagen = ''; // No hay imagen previa
        }

        //  Reemplazo de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            // Si hay imagen previa, se elimina
            if (!empty($nombre_archivo) && file_exists("recursos/img/cartelera/" . $nombre_archivo) && is_file("recursos/img/cartelera/" . $nombre_archivo)) {
                unlink("recursos/img/cartelera/" . $nombre_archivo);
            }
            
            // LO MISMO QUE EN REGISTRAR
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $nombre_archivo = $nombre_sanitizado . '_' . time() . '.' . $extension;

            move_uploaded_file($temporal, "recursos/img/cartelera/" . $nombre_archivo);
        }

        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);
        $cartelera_virtual_obj->set_titulo($titulo);
        $cartelera_virtual_obj->set_descripcion($descripcion);
        $cartelera_virtual_obj->set_fecha($fecha);
        $cartelera_virtual_obj->set_imagen($nombre_archivo);
        $cartelera_virtual_obj->set_prioridad($prioridad);

        $respuesta = $cartelera_virtual_obj->realizar_consulta("editar_publicacion");

        if ($respuesta["estatus"]) {
            $cartelera_virtual_obj->registrar_bitacora(MODIFICAR, GESTIONAR_CARTELERA_VIRTUAL, $titulo . " - " . $fecha);
        }

        echo json_encode($respuesta);

    } 
    
    elseif ($operacion == "eliminar") {
    $id_cartelera = $_POST["id_cartelera"];
    $cartelera_virtual_obj->set_id_cartelera($id_cartelera);

    //  Consultar la imagen antes de eliminar
    $datos = $cartelera_virtual_obj->realizar_consulta("consultar_cartelera_id"); // debe retornar imagen

    // 🗑 Eliminar la publicación de la base de datos
    $resultado = $cartelera_virtual_obj->realizar_consulta("eliminar_publicacion");

    if ($resultado["estatus"]) {
        if ($datos){
            $cartelera_virtual_obj->registrar_bitacora(ELIMINAR, GESTIONAR_CARTELERA_VIRTUAL, $datos["titulo"] . " - " . $datos["fecha"]);
        }
    }
    echo json_encode($resultado);
} 

elseif ($operacion == "ultimo_id") {
        echo json_encode($cartelera_virtual_obj->realizar_consulta("lastId"));
    }

    exit;

}
require_once "vista/cartelera_virtual/cartelera_virtual_vista.php";

?>