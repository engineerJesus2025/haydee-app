<?php
require_once "modelo/gastos_modelo.php";
require_once "modelo/banco_modelo.php";
require_once "modelo/proveedores_modelo.php";

$gastos_obj = new Gastos();
$banco_obj = new Banco();
$proveedor_obj = new Proveedores();

$proveedores = $proveedor_obj->consultar();
$bancos = $banco_obj->consultar();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    if ($operacion == "consulta") {
        echo json_encode($gastos_obj->consultar());
    } elseif ($operacion == "registrar") {
        $fecha = $_POST["fecha"];
        $mes = date("m", strtotime($fecha));
        $anio = date("Y", strtotime($fecha));
        $monto = $_POST["monto"];

        // Verificamos si ya existe el mes y el año de gasto_mes y sino, se crea
        $gasto_mes = $gastos_obj->consultar_gasto_mes($mes, $anio);
        if (!$gasto_mes) {
            $gastos_obj->registrar_gasto_mes($mes, $anio);
            $gasto_mes = $gastos_obj->consultar_gasto_mes($mes, $anio);
        }
        $gastos_obj->set_gasto_mes_id($gasto_mes["id_gasto_mes"]);

        $tipo_gasto = $_POST["tipo_gasto"];
        $metodo_pago = $_POST["metodo_pago"];
        $referencia = $_POST["referencia"];
        $descripcion_gasto = $_POST["descripcion_gasto"];
        $banco = !empty($_POST["banco"]) ? $_POST["banco"] : null;
        $proveedor = $_POST["proveedor"];

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
            $ruta_destino = "recursos/img/gastos/";
            // ESTO NUNCA VA A PASAR!! PERO POR SI ACASO (BASICAMENTE SI NO EXISTE LA RUTA, SE CREA)
            if (!is_dir($ruta_destino)) {
                mkdir($ruta_destino, 0777, true);
            }
            // AQUI SE CAMBIA LA RUTA DE LA IMAGEN, DE UNA IMAGEN TEMPORAL A LA RUTA DE DESTINO FISICA
            move_uploaded_file($temporal, $ruta_destino . $nombre_archivo);
        }

        $gastos_obj->set_fecha($fecha);
        $gastos_obj->set_monto($monto);
        $gastos_obj->set_tipo_gasto($tipo_gasto);
        $gastos_obj->set_metodo_pago($metodo_pago);
        $gastos_obj->set_referencia($referencia);
        $gastos_obj->set_descripcion_gasto($descripcion_gasto);
        $gastos_obj->set_banco_id($banco);
        $gastos_obj->set_proveedor_id($proveedor);
        $gastos_obj->set_imagen($nombre_archivo);

        echo json_encode($gastos_obj->registrar());

    } elseif ($operacion == "listar_gastos_mes") {
        echo json_encode($gastos_obj->listar_gastos_mes());
    } elseif ($operacion == "filtrar_gastos_mes") {
        $id_mes = $_POST["gasto_mes_id"];
        echo json_encode($gastos_obj->filtrar_por_mes($id_mes));
    } elseif ($operacion == "totales_metodo_pago") {
        $id_mes = $_POST["gasto_mes_id"];
        echo json_encode($gastos_obj->total_por_metodo_pago($id_mes));
    } elseif ($operacion == "consulta_especifica") {
        $id_gasto = $_POST["id_gasto"];
        $gastos_obj->set_id_gasto($id_gasto);
        echo json_encode($gastos_obj->consultar_gasto_id());

    } elseif ($operacion == "modificar") {
        $fecha = $_POST["fecha"];
        $mes = date("m", strtotime($fecha));
        $anio = date("Y", strtotime($fecha));
        $monto = $_POST["monto"];

        // Verificamos si ya existe el mes y el año de gasto_mes y sino, se crea
        $gasto_mes = $gastos_obj->consultar_gasto_mes($mes, $anio);
        if (!$gasto_mes) {
            $gastos_obj->registrar_gasto_mes($mes, $anio);
            $gasto_mes = $gastos_obj->consultar_gasto_mes($mes, $anio);
        }
        $gastos_obj->set_gasto_mes_id($gasto_mes["id_gasto_mes"]);
        $id_gasto = $_POST["id_gasto"];
        $fecha = $_POST["fecha"];
        $monto = $_POST["monto"];
        $tipo_gasto = $_POST["tipo_gasto"];
        $metodo_pago = $_POST["metodo_pago"];
        $referencia = $_POST["referencia"];
        $descripcion_gasto = $_POST["descripcion_gasto"];
        $banco = !empty($_POST["banco"]) ? $_POST["banco"] : null;
        $proveedor = $_POST["proveedor"];

        // IMAGEN
        $eliminar_imagen = isset($_POST["eliminar_imagen"]);

        $nombre_archivo = $gastos_obj->obtener_imagen_actual();
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

        //  Reemplazo de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            // Si hay imagen previa, se elimina
            if (!empty($nombre_archivo) && file_exists("recursos/img/gastos/" . $nombre_archivo) && is_file("recursos/img/" . $nombre_archivo)) {
                unlink("recursos/img/gastos/" . $nombre_archivo);
            }

            // LO MISMO QUE EN REGISTRAR
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $nombre_archivo = $nombre_sanitizado . '_' . time() . '.' . $extension;

            move_uploaded_file($temporal, "recursos/img/gastos/" . $nombre_archivo);
        }

        $gastos_obj->set_id_gasto($id_gasto);
        $gastos_obj->set_fecha($fecha);
        $gastos_obj->set_monto($monto);
        $gastos_obj->set_tipo_gasto($tipo_gasto);
        $gastos_obj->set_metodo_pago($metodo_pago);
        $gastos_obj->set_referencia($referencia);
        $gastos_obj->set_imagen($nombre_archivo);
        $gastos_obj->set_descripcion_gasto($descripcion_gasto);
        $gastos_obj->set_banco_id($banco);
        $gastos_obj->set_proveedor_id($proveedor);
        echo json_encode($gastos_obj->editar_gasto());

    } elseif ($operacion == "eliminar") {
        $id_gasto = $_POST["id_gasto"];
        $gastos_obj->set_id_gasto($id_gasto);
        echo json_encode($gastos_obj->eliminar_gasto());

    } elseif ($operacion == "ultimo_id") {
        echo json_encode($gastos_obj->lastId());
    }

    exit;
}

require_once "vista/gastos/gastos_vista.php";


