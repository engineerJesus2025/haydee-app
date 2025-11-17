<?php
use haydee\ayuda\Sesiones;
Sesiones::verificarSesion();

use haydee\modelo\Usuario;
use haydee\modelo\CarteleraVirtual;

$cartelera_virtual_obj = new CarteleraVirtual();
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
            $peso_maximo = 2 * 1024 * 1024; // 2 MB en bytes
            $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];            
            $nombre_original = $_FILES['imagen']['name'];
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $tamano_archivo = $_FILES['imagen']['size']; // Peso del archivo
            if ($tamano_archivo > $peso_maximo) {
                echo json_encode([
                    "estatus" => false, 
                    "mensaje" => "La imagen es muy pesada. El límite es de 2MB."
                ]);
                exit;
            }

            // VALIDAR QUE SEA REALMENTE UNA IMAGEN (MIME TYPE)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $temporal);
            finfo_close($finfo);

            if (!in_array($mime, $tipos_permitidos)) {
                echo json_encode([
                    "estatus" => false, 
                    "mensaje" => "El archivo no es una imagen válida. Use JPG, PNG, GIF o WEBP."
                ]);
                exit;
            }

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
        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);
        $titulo = $_POST["titulo"];
        $descripcion = $_POST["descripcion"];
        $fecha = $_POST["fecha"];
        $prioridad = $_POST["prioridad"];
        $eliminar_imagen = isset($_POST["eliminar_imagen"]);

        $cartelera_virtual_obj->set_id_cartelera($id_cartelera);

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

        // 1. Verificamos si se intentó subir un archivo
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            
            // 2. Primero verificamos si hubo error en la carga (ej. excede post_max_size del servidor)
            if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode([
                    "estatus" => false, 
                    "mensaje" => "Error al subir el archivo. Posiblemente el archivo es demasiado grande o está corrupto."
                ]);
                exit;
            }

            $peso_maximo = 2 * 1024 * 1024; // 2 MB
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $tamano_archivo = $_FILES['imagen']['size'];

            if ($tamano_archivo > $peso_maximo) {
                echo json_encode([
                    "estatus" => false, 
                    "mensaje" => "La imagen es muy pesada. El límite es de 2MB."
                ]);
                exit;
            }

            $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $temporal);
            finfo_close($finfo);

            if (!in_array($mime, $tipos_permitidos)) {
                echo json_encode([
                    "estatus" => false, 
                    "mensaje" => "Formato inválido. Use JPG, PNG, GIF o WEBP."
                ]);
                exit;
            }

            // 5. Preparar nombre nuevo
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $nuevo_nombre_archivo = $nombre_sanitizado . '_' . time() . '.' . $extension;

            // 6. Guardar la nueva imagen
            if (move_uploaded_file($temporal, "recursos/img/cartelera/" . $nuevo_nombre_archivo)) {
                
                if (!empty($nombre_archivo) && file_exists("recursos/img/cartelera/" . $nombre_archivo) && is_file("recursos/img/cartelera/" . $nombre_archivo)) {
                    unlink("recursos/img/cartelera/" . $nombre_archivo);
                }

                // Actualizamos la variable $nombre_archivo para que se guarde en la BD el nuevo nombre
                $nombre_archivo = $nuevo_nombre_archivo; 

            } else {
                echo json_encode([
                    "estatus" => false, 
                    "mensaje" => "Error al guardar la imagen en el servidor."
                ]);
                exit;
            }
        }

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