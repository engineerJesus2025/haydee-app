<?php
require_once "modelo/gastos_modelo.php";
require_once "modelo/banco_modelo.php";
require_once "modelo/proveedores_modelo.php";
require_once "modelo/solicitud_gasto_modelo.php";
require_once "modelo/caja_chica_modelo.php";
require_once "modelo/tipo_gasto_modelo.php";

$gastos_obj = new Gastos();
$banco_obj = new Banco();
$proveedor_obj = new Proveedores();
$solicitud_gasto_obj = new Solicitud_Gasto();
$caja_chica_obj = new Caja_Chica();
$tipo_gasto_obj = new Tipo_Gasto();

$proveedores = $proveedor_obj->consultar();
$bancos = $banco_obj->consultar();
$solicitudes_gasto = $solicitud_gasto_obj->consultar();
$cajas_chica = $caja_chica_obj->consultar();
$tipos_gasto = $tipo_gasto_obj->consultar();

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta") {
        echo json_encode($gastos_obj->consultar());

    } elseif ($operacion == "registrar") {
        $fecha = $_POST["fecha"];
        $mes = date("m", strtotime($fecha));
        $anio = date("Y", strtotime($fecha));
        $monto = $_POST["monto"];
        $tipo = $_POST["tipo"];
        $metodo_pago = strtolower($_POST["metodo_pago"]);
        $tipo_gasto = $_POST["tipo_gasto"];
        $solicitud = $_POST["solicitud"];
        $descripcion_gasto = $_POST["descripcion_gasto"];
        $proveedor = $_POST["proveedor"];
        $referencia = $_POST["referencia"] ?? null;
        $banco = $_POST["banco"] ?? null;

        // Procesar imagen
        $nombre_archivo = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $nombre_archivo = $nombre_sanitizado . '_' . time() . '.' . $extension;

            $ruta_destino = "recursos/img/gastos/";
            if (!is_dir($ruta_destino)) {
                mkdir($ruta_destino, 0777, true);
            }
            move_uploaded_file($temporal, $ruta_destino . $nombre_archivo);
        }

        // Obtener el id de la caja
        $caja = $gastos_obj->consultarCajaActual($fecha);

        if (!isset($caja["id_caja_chica"])) {
            echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
            exit;
        }

        $id_caja = $caja["id_caja_chica"];

        // Setear datos
        $gastos_obj->set_fecha($fecha);
        $gastos_obj->set_monto($monto);
        $gastos_obj->set_tipo($tipo);
        $gastos_obj->set_metodo_pago($metodo_pago);
        $gastos_obj->set_tipo_gasto_id($tipo_gasto);
        $gastos_obj->set_solicitud_id($solicitud);
        $gastos_obj->set_caja_id($id_caja);
        $gastos_obj->set_descripcion_gasto($descripcion_gasto);
        $gastos_obj->set_proveedor_id($proveedor);

        // Registrar gasto y obtener el ID
        $id_gasto = $gastos_obj->registrar();

        if (!empty($solicitud)) {
        require_once "modelo/solicitud_gasto_modelo.php";
        $solicitud_gasto_obj = new Solicitud_gasto();
        $solicitud_gasto_obj->set_id_solicitud($solicitud);
        $solicitud_gasto_obj->cambiar_estado_asignado();
    }

        if (!$id_gasto || !is_numeric($id_gasto)) {
            echo json_encode(["estatus" => false, "mensaje" => "Error al registrar el gasto"]);
            exit;
        }

        $respuesta = ["estatus" => true, "mensaje" => "Gasto registrado correctamente"];

        // Si es método transferencia o pago movil, registrar transacción bancaria
        if (in_array($metodo_pago, ['transferencia', 'pago_movil'])) {
            $resultado_banco = $gastos_obj->registrar_banco_transacciones($referencia, $nombre_archivo, $banco, $id_gasto);
            $respuesta["banco"] = $resultado_banco;
        }

        echo json_encode($respuesta);
    } elseif ($operacion == "listar_gastos_mes") {
        echo json_encode($gastos_obj->listar_gastos_mes());
    } elseif ($operacion == "filtrar_gastos_mes") {
        // $id_mes = $_POST["gasto_mes_id"]; -> quite esto tambien y lo cambie por fecha
        $fecha = $_POST["fecha"];
        $gastos_obj->set_fecha($fecha);
        echo json_encode($gastos_obj->filtrar_por_mes());
    } elseif ($operacion == "totales_metodo_pago") {
        $fecha = $_POST["fecha"];
        $gastos_obj->set_fecha($fecha);

        echo json_encode($gastos_obj->total_por_metodo_pago());
    } elseif ($operacion == "consulta_especifica") {
        $id_gasto = $_POST["id_gasto"];
        $gastos_obj->set_id_gasto($id_gasto);
        echo json_encode($gastos_obj->consultar_gasto_id());

    } elseif ($operacion == "modificar") {
        $id_gasto = $_POST["id_gasto"];
        $fecha = $_POST["fecha"];
        $monto = $_POST["monto"];
        $tipo = $_POST["tipo"];
        $tipo_gasto = $_POST["tipo_gasto"];
        $metodo_pago = strtolower($_POST["metodo_pago"]);
        $referencia = $_POST["referencia"];
        $descripcion_gasto = $_POST["descripcion_gasto"];
        $banco = !empty($_POST["banco"]) ? $_POST["banco"] : null;
        $proveedor = $_POST["proveedor"];
        $solicitud = $_POST["solicitud"];

        // Imagen
        $eliminar_imagen = isset($_POST["eliminar_imagen"]);
        $nombre_archivo = $gastos_obj->obtener_imagen_actual();

        if ($eliminar_imagen && !empty($nombre_archivo)) {
            $ruta_imagen = "recursos/img/gastos/" . $nombre_archivo;
            if (file_exists($ruta_imagen) && is_file($ruta_imagen)) {
                unlink($ruta_imagen);
                $nombre_archivo = '';
            }
        }

        // Reemplazo de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            if (!empty($nombre_archivo) && file_exists("recursos/img/gastos/" . $nombre_archivo)) {
                unlink("recursos/img/gastos/" . $nombre_archivo);
            }

            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $nombre_archivo = $nombre_sanitizado . '_' . time() . '.' . $extension;

            move_uploaded_file($temporal, "recursos/img/gastos/" . $nombre_archivo);
        }

        // Obtener el id de la caja
        $caja = $gastos_obj->consultarCajaActual($fecha);

        if (!isset($caja["id_caja_chica"])) {
            echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
            exit;
        }

        $id_caja = $caja["id_caja_chica"];


        // Setters
        $gastos_obj->set_id_gasto($id_gasto);
        $gastos_obj->set_fecha($fecha);
        $gastos_obj->set_monto($monto);
        $gastos_obj->set_tipo($tipo);
        $gastos_obj->set_tipo_gasto_id($tipo_gasto);
        $gastos_obj->set_metodo_pago($metodo_pago);
        $gastos_obj->set_descripcion_gasto($descripcion_gasto);
        $gastos_obj->set_proveedor_id($proveedor);
        $gastos_obj->set_solicitud_id($solicitud);
        $gastos_obj->set_caja_id($id_caja);

        $respuesta = $gastos_obj->editar_gasto();

        // 🔁 Manejo de transacción bancaria
        if ($metodo_pago === "transferencia" || $metodo_pago === "pago_movil") {
            // Si ya existe, la eliminamos para que no haya duplicado
            $gastos_obj->eliminar_banco_transacciones();

            // Registrar nueva transacción bancaria con datos correctos
            $gastos_obj->registrar_banco_transacciones($referencia, $nombre_archivo, $banco, $id_gasto);
        } else {
            // Si ya no aplica transacción bancaria, la eliminamos si existía
            $gastos_obj->eliminar_banco_transacciones();
        }

        echo json_encode($respuesta);
    } elseif ($operacion == "eliminar") {
        $id_gasto = $_POST["id_gasto"];
        $gastos_obj->set_id_gasto($id_gasto);
        $respuesta = $gastos_obj->eliminar_gasto();
        echo json_encode($respuesta);

    } elseif ($operacion == "ultimo_id") {
        echo json_encode($gastos_obj->lastId());
    }
    exit;

}
require_once "vista/gastos/gastos_vista.php";


