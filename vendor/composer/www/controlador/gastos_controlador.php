<?php
require_once "modelo/gastos_modelo.php";
require_once "modelo/banco_modelo.php";
require_once "modelo/proveedores_modelo.php";
require_once "modelo/solicitud_gasto_modelo.php";
require_once "modelo/caja_chica_modelo.php";
require_once "modelo/tipo_gasto_modelo.php";
require_once "modelo/detalles_gastos_modelo.php";
require_once "modelo/bancos_transacciones_modelo.php";

$gastos_obj = new Gastos();
$banco_obj = new Banco();
$proveedor_obj = new Proveedores();
$solicitud_gasto_obj = new Solicitud_Gasto();
$detalles_gastos_obj = new Detalles_Gasto();
$caja_chica_obj = new Caja_Chica();
$tipo_gasto_obj = new Tipo_Gasto();
$bancos_transacciones_obj = new Bancos_Transacciones();

$proveedores = $proveedor_obj->consultar();
$bancos = $banco_obj->consultar();
$solicitudes_gasto = $solicitud_gasto_obj->consultar();
$cajas_chica = $caja_chica_obj->consultar();
$tipos_gasto = $tipo_gasto_obj->consultar();



if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta") {
        echo json_encode($gastos_obj->consultar());

    } elseif ($operacion == "consultar_detalles") {
        $detalles_gastos_obj->set_gasto_id($_POST["id_gasto"]);
        echo json_encode($detalles_gastos_obj->consultar_detalles_por_gasto());
        exit;
    } elseif ($operacion == "consulta_especifica_detalles") {
        $detalles_gastos_obj->set_id_detalle_gasto($_POST["id_detalle_gasto"]);
        echo json_encode($detalles_gastos_obj->consultar_detalle_gasto());
        exit;
    } elseif ($operacion == "registrar_detalles") {
        $fecha = $_POST["fecha"];
        $monto = $_POST["monto"];
        $metodo_pago = strtolower($_POST["metodo_pago"]);
        $descripcion_detalle_gasto = $_POST["descripcion_detalle_gasto"];
        $gasto_id = $_POST["gasto_id"];
        $banco_id = $_POST["banco"] ?? null;
        $referencia = $_POST["referencia"] ?? null;
        $imagen = '';


        // Guardamos imagen si se subió
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;
            $ruta_destino = "recursos/img/gastos/";

            if (!is_dir($ruta_destino)) {
                mkdir($ruta_destino, 0777, true);
            }
            move_uploaded_file($temporal, $ruta_destino . $imagen);
        }

        // Obtener el id de la caja
        $caja = $gastos_obj->consultarCajaActual();

        if (!isset($caja["id_caja_chica"])) {
            echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
            exit;
        }

        $id_caja = $caja["id_caja_chica"];

        // Registrar detalle de gasto
        $detalles_gastos_obj->set_fecha($fecha);
        $detalles_gastos_obj->set_monto($monto);
        $detalles_gastos_obj->set_metodo_pago($metodo_pago);
        $detalles_gastos_obj->set_descripcion_detalle_gasto($descripcion_detalle_gasto);
        $detalles_gastos_obj->set_gasto_id($gasto_id);
        $detalles_gastos_obj->set_caja_id($id_caja);

        $resultado_detalle_gasto = $detalles_gastos_obj->registrar_detalle_gasto();
        if (!$resultado_detalle_gasto["estatus"]) {
            echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle"]);
            exit;
        }

        $detalle_gasto_id = $detalles_gastos_obj->lastId()["mensaje"];

        // Registrar transacción bancaria (si aplica)
        if (!empty($referencia) || !empty($imagen)) {
            $bancos_transacciones_obj->set_referencia($referencia);
            $bancos_transacciones_obj->set_imagen($imagen);
            $bancos_transacciones_obj->set_banco_id($banco_id);
            $bancos_transacciones_obj->set_detalle_gasto_id($detalle_gasto_id);

            $resultado_banco = $bancos_transacciones_obj->registrar_banco_transaccion_gasto();
            if (!$resultado_banco["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar banco/transacción"]);
                exit;
            }
        }

        echo json_encode([
            "estatus" => true,
            "mensaje" => "Detalle de gasto registrado exitosamente",
            "id_detalle" => $detalle_gasto_id
        ]);


    } elseif ($operacion == "consulta_especifica_detalles") {
        $detalles_gastos_obj->set_id_detalle_gasto($_POST["id_detalle_gasto"]);
        echo json_encode($detalles_gastos_obj->consultar_detalle_gasto());
        exit;
    } elseif ($operacion == "modificar_detalles") {
        $id_gasto = $_POST["id_gasto"];
        $id_detalle_gasto = $_POST["id_detalle_gasto"];
        $id_banco_transaccion = $_POST["id_banco_transaccion"] ?? null;
        $fecha = $_POST["fecha_detalle"];
        $monto = $_POST["monto"];
        $metodo_pago = $_POST["metodo_pago"];
        $referencia = $_POST["referencia"] ?? '';
        $banco_id = $_POST["banco_id"] ?? '';
        $eliminar_imagen = isset($_POST["eliminar_imagen"]);

        // Obtener imagen actual
        $imagen = $bancos_transacciones_obj->obtener_imagen_actual();

        if (!empty($imagen)) {
            $ruta_imagen = "recursos/img/gastos/" . $imagen;

            // Eliminar imagen si el usuario lo pidió
            if ($eliminar_imagen && file_exists($ruta_imagen) && is_file($ruta_imagen)) {
                unlink($ruta_imagen);
                $imagen = '';
            }
        } else {
            $ruta_imagen = '';
        }

        // Si se subió una nueva imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            if (!empty($imagen) && file_exists("recursos/img/gastos/" . $imagen) && is_file("recursos/img/" . $imagen)) {
                unlink("recursos/img/gastos/" . $imagen);
            }

            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;

            move_uploaded_file($temporal, "recursos/img/gastos/" . $imagen);
        }

        // Caja activa
        $caja = $gastos_obj->consultarCajaActual();
        if (!isset($caja["id_caja_chica"])) {
            echo json_encode(["estatus" => false, "mensaje" => "No hay caja activa"]);
            exit;
        }
        $id_caja = $caja["id_caja_chica"];

        // Modificar detalle
        $detalles_gastos_obj->set_id_detalle_gasto($id_detalle_gasto);
        $detalles_gastos_obj->set_fecha($fecha);
        $detalles_gastos_obj->set_monto($monto);
        $detalles_gastos_obj->set_metodo_pago($metodo_pago);
        $detalles_gastos_obj->set_gasto_id($id_gasto);
        $detalles_gastos_obj->set_caja_id($id_caja);

        $resultado_modificar = $detalles_gastos_obj->editar_detalle_gasto();
        if (!$resultado_modificar["estatus"]) {
            echo json_encode(["estatus" => false, "mensaje" => "Error al modificar detalle"]);
            exit;
        }

        // Transacción bancaria si aplica
        $requiere_transaccion = in_array($metodo_pago, ["Transferencia", "Pago Movil"]);
        if ($requiere_transaccion) {
            $bancos_transacciones_obj->set_detalle_gasto_id($id_detalle_gasto);
            $bancos_transacciones_obj->set_referencia($referencia);
            $bancos_transacciones_obj->set_imagen($imagen);
            $bancos_transacciones_obj->set_banco_id($banco_id);

            if (!empty($id_banco_transaccion)) {
                $bancos_transacciones_obj->set_id_banco_transaccion($id_banco_transaccion);
                $consulta_transaccion = $bancos_transacciones_obj->consultar_banco_transaccion();

                if ($consulta_transaccion && isset($consulta_transaccion["id_banco_transaccion"])) {
                    $resultado_banco = $bancos_transacciones_obj->editar_banco_transaccion();
                } else {
                    $resultado_banco = $bancos_transacciones_obj->registrar_banco_transaccion_gasto();
                }
            } else {
                $resultado_banco = $bancos_transacciones_obj->registrar_banco_transaccion_gasto();
                if ($resultado_banco["estatus"] && isset($resultado_banco["id_banco_transaccion"])) {
                    $id_banco_transaccion = $resultado_banco["id_banco_transaccion"];
                }
            }

            if (!$resultado_banco["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error en transacción bancaria"]);
                exit;
            }

        } else {
            // Es efectivo. Si existía una transacción, se elimina
            if (!empty($id_banco_transaccion)) {
                $bancos_transacciones_obj->set_id_banco_transaccion($id_banco_transaccion);
                $resultado_eliminar = $bancos_transacciones_obj->eliminar_banco_transaccion_gasto();
                if (!$resultado_eliminar["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar transacción bancaria"]);
                    exit;
                }
            }
        }

        echo json_encode([
            "estatus" => true,
            "mensaje" => "Detalle modificado correctamente",
            "id_detalle" => $id_detalle_gasto,
            "imagen" => $imagen ?? "",
            "referencia" => $referencia,
            "id_banco_transaccion" => $id_banco_transaccion
        ]);
        exit;
    } elseif ($operacion == "registrar") {
        
        // $mes = date("m", strtotime($fecha)); ??
        // $anio = date("Y", strtotime($fecha)); ??
        // $monto = $_POST["monto"]; YA NO SE NECEISTA, SE GUARDA COMO 0 AUTOMATICAMENTE

        
        // $metodo_pago = strtolower($_POST["metodo_pago"]);//Esto no va aqui maquina
        $fecha = $_POST["fecha"];
        $tipo = $_POST["tipo"];
        $descripcion_gasto = $_POST["descripcion_gasto"];
        $tipo_gasto = $_POST["tipo_gasto"];
        $solicitud = $_POST["solicitud"];
        $proveedor = $_POST["proveedor"];

        // Gastos
        $gastos_obj->set_fecha($fecha);
        $gastos_obj->set_tipo($tipo);
        $gastos_obj->set_descripcion_gasto($descripcion_gasto);
        $gastos_obj->set_tipo_gasto_id($tipo_gasto);
        $gastos_obj->set_solicitud_id($solicitud);
        $gastos_obj->set_proveedor_id($proveedor);

        $resultado_registro_gasto = $gastos_obj->registrar();

        if (!$resultado_registro_gasto) {
            echo json_encode(["estatus" => false, "mensaje" => "Error al registrar gasto"]);
            exit;
        }

        $caja = $gastos_obj->consultarCajaActual();

        if (!isset($caja["id_caja_chica"])) {
            echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
            exit;
        }

        $id_caja = $caja["id_caja_chica"];

        $ultimo_gasto_id = $gastos_obj->lastId();
        $gasto_id = $ultimo_gasto_id["mensaje"];
        // Detalles de Gasto
        
        $fechas_detalles = $_POST["fecha_detalle"];
        $monto = $_POST["monto"];
        $metodo_pago = $_POST["metodo_pago"];
        $referencia = $_POST["referencia"];
        $descripcion_detalle = $_POST["descripcion_detalle"];
        $banco = $_POST["banco"];
        $proveedor = $_POST["proveedor"];

        $detalle_gasto_id = null;

        $indice_imagen = 0; // porque lo del ajax lo ignora

        // era fecha detalle no fecha
        foreach ($fechas_detalles as $indice => $fecha_detalle) {
            $detalles_gastos_obj->set_fecha($fecha_detalle);
            $detalles_gastos_obj->set_monto($monto[$indice]);
            $detalles_gastos_obj->set_monto_dolar(100);// Vos veras si la usais, de momento la dejo
            $detalles_gastos_obj->set_metodo_pago($metodo_pago[$indice]);
            $detalles_gastos_obj->set_descripcion_detalle_gasto($descripcion_detalle[$indice]);
            $detalles_gastos_obj->set_gasto_id($gasto_id);
            $detalles_gastos_obj->set_caja_id($id_caja);

            $resultado_detalle_gasto = $detalles_gastos_obj->registrar_detalle_gasto();
            if (!$resultado_detalle_gasto["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle"]);
                exit;
            }

            $id_detalle = $detalles_gastos_obj->lastId()["mensaje"];

            $imagen_detalle = '';
            if (isset($_FILES['imagen']['name'][$indice_imagen]) && $_FILES['imagen']['error'][$indice_imagen] === 0) {
                $nombre_original = $_FILES['imagen']['name'][$indice_imagen];
                $temporal = $_FILES['imagen']['tmp_name'][$indice_imagen];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $imagen_detalle = $nombre_sanitizado . '_' . time() . '_' . rand(100, 999) . '.' . $extension;

                $ruta_destino = "recursos/img/gastos/";
                if (!is_dir($ruta_destino)) {
                    mkdir($ruta_destino, 0777, true);
                }

                move_uploaded_file($temporal, $ruta_destino . $imagen_detalle);
            }
                                            //Habias puesto || -_-
            if (!empty($referencia[$indice]) && !empty($imagen_detalle)) {
                $bancos_transacciones_obj->set_referencia($referencia[$indice]);
                $bancos_transacciones_obj->set_imagen($imagen_detalle);
                $bancos_transacciones_obj->set_banco_id($banco[$indice]);
                $bancos_transacciones_obj->set_detalle_gasto_id($id_detalle);
                $resultado_banco = $bancos_transacciones_obj->registrar_banco_transaccion_gasto();
                if (!$resultado_banco["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar banco en detalle $indice"]);
                    exit;
                }

                $indice_imagen++;
            }
        }

        // el final si todo salio bien ;-;
        echo json_encode([
            "estatus" => true,
            "mensaje" => "Gasto registrado con éxito",
            "id_gasto" => $gasto_id,
            "id_detalle" => $detalle_gasto_id,
            "referencia" => $referencia,
            "imagen" => $imagen ?? null
        ]);
        exit;


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
        echo json_encode($gastos_obj->consultar_gasto());

    }
    elseif ($operacion == "modificar") {
        $id_gasto = $_POST["id_gasto"];
        $id_detalle_gasto = $_POST["id_detalle_gasto"];
        $id_banco_transaccion = $_POST["id_banco_transaccion"];
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
        $imagen = $detalles_gastos_obj->obtener_imagen_actual();

        if (!empty($imagen)) {
            $ruta_imagen = "recursos/img/gastos/" . $imagen;
            if (file_exists($ruta_imagen) && is_file($ruta_imagen)) {
                unlink($ruta_imagen);
                $imagen = '';
            }
        }

        // Reemplazo de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            if (!empty($imagen) && file_exists("recursos/img/gastos/" . $imagen)) {
                unlink("recursos/img/gastos/" . $imagen);
            }

            $nombre_original = $_FILES['imagen']['name'];
            $temporal = $_FILES['imagen']['tmp_name'];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;

            move_uploaded_file($temporal, "recursos/img/gastos/" . $imagen);
        }

        // Obtener el id de la caja
        $caja = $gastos_obj->consultarCajaActual($fecha);

        if (!isset($caja["id_caja_chica"])) {
            echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
            exit;
        }

        $id_caja = $caja["id_caja_chica"];


        // Modificar Gasto
        $gastos_obj->set_id_gasto($id_gasto);
        $gastos_obj->set_fecha($fecha);
        $gastos_obj->set_monto($monto);
        $gastos_obj->set_tipo($tipo);
        $gastos_obj->set_tipo_gasto_id($tipo_gasto);
        $gastos_obj->set_descripcion_gasto($descripcion_gasto);
        $gastos_obj->set_proveedor_id($proveedor);
        $gastos_obj->set_solicitud_id($solicitud);

        $resultado_modificar_gasto = $gastos_obj->editar_gasto();
        if (!$resultado_modificar_gasto["estatus"]) {
            echo json_encode(["estatus" => false, "mensaje" => "Error al actualizar gasto"]);
            exit;
        }

        // Modificar Detalles del Gasto
        $detalles_gastos_obj->set_id_detalle_gasto($id_detalle_gasto);
        $detalles_gastos_obj->set_fecha($fecha);
        $detalles_gastos_obj->set_monto($monto);
        $detalles_gastos_obj->set_metodo_pago($metodo_pago);
        $detalles_gastos_obj->set_descripcion_detalle_gasto($descripcion_gasto);
        $detalles_gastos_obj->set_caja_id($id_caja);
        $detalles_gastos_obj->set_gasto_id($id_gasto);

        $resultado_modificar_detalle = $detalles_gastos_obj->editar_detalle_gasto();
        if (!$resultado_modificar_detalle["estatus"]) {
            echo json_encode(["estatus" => false, "mensaje" => "Error al actualizar detalle de gasto"]);
            exit;
        }


        // 🔁 Manejo de transacción bancaria
        if ($metodo_pago === "transferencia" || $metodo_pago === "pago_movil") {
            // Si ya existe, la eliminamos para que no haya duplicado
            $detalles_gastos_obj->eliminar_banco_transacciones();

            // Registrar nueva transacción bancaria con datos correctos
            $detalles_gastos_obj->registrar_banco_transacciones($referencia, $nombre_archivo, $banco, $detalle_gasto_id);
        } else {
            // Si ya no aplica transacción bancaria, la eliminamos si existía
            $detalles_gastos_obj->eliminar_banco_transacciones();
        }

        echo json_encode($respuesta);

        echo json_encode([
            "estatus" => true,
            "mensaje" => "Pago modificado con éxito",
            "id_pago" => $id_pago,
            "id_detalle" => $id_detalle_pago,
            "referencia" => $referencia,
            "imagen" => $imagen
        ]);
        exit;

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

