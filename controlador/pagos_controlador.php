<?php
require_once "vista/componentes/sesion.php";
require_once("modelo/pagos_modelo.php");
require_once("modelo/banco_modelo.php");
require_once("modelo/detalles_pago_modelo.php");
require_once("modelo/pagos_mensualidad_modelo.php");
require_once("modelo/bancos_transacciones_modelo.php");
require_once("modelo/apartamentos_modelo.php");
require_once("modelo/notificaciones_modelo.php");

$obj_pago = new Pagos(); // Objeto pago
$obj_banco = new Banco(); // Objeto banco
$obj_apartamento = new Apartamento(); // Objeto apartamento
$obj_detalles_pago = new Detalles_pago(); // Objeto detalles pago
$obj_pagos_mensualidad = new Pagos_mensualidad(); // Objeto pagos mensualidad
$obj_bancos_transacciones = new Bancos_transacciones(); // Objeto bancos transacciones
$notificacion_obj = new Notificaciones(); // Objeto notificaciones

if (isset($_SESSION["rol"]) && $_SESSION["rol"] != "Propietario") {
    //$registro_mensualidad = $obj_pago->consultarMensualidad();
    $registro_apartamento = $obj_apartamento->realizar_consulta('consultar');
} else {
    $registro_apartamento = $obj_apartamento->consultar_propietario($_SESSION["usuario"]);
}

$registro_banco = $obj_banco->realizar_consulta('consultar');

if (isset($_SESSION["rol"]) && $_SESSION["rol"] != "Propietario") {
    if (isset($_POST["operacion"])) {
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta") {

            echo json_encode($obj_pago->realizar_consulta('consultar'));

        }
        // ---- Para cargar las mensualidades en el select en base al apartamento seleccionado ------
        elseif ($operacion == "consultar_mensualidades") {
            $apartamento_id = $_POST["apartamento_id"];
            $mensualidades = $obj_pago->consultarMensualidadPendiente($apartamento_id);
            echo json_encode($mensualidades);
        }
        // ---------- Todo este bloque es para registrar los detalles de un pago ----------
        elseif ($operacion == "consultar_detalles") {
            $obj_detalles_pago->set_pago_id($_POST["id_pago"]);
            echo json_encode($obj_detalles_pago->realizar_consulta('consultar_detalles'));
            exit;
        } elseif ($operacion == "registrar_detalles") {
            // Validación básica
            if (!isset($_POST["pago_id"]) || !isset($_POST["mensualidad_id"])) {
                echo json_encode(["estatus" => false, "mensaje" => "Faltan datos obligatorios"]);
                exit;
            }

            // Capturamos los datos del formulario
            $fecha = $_POST["fecha"];
            $monto = $_POST["monto"];
            $monto_dolar = $_POST["monto_dolar"];
            $tipo_pago = $_POST["tipo_pago"];
            $referencia = $_POST["referencia"] ?? '';
            $banco_id = $_POST["banco_id"] ?? '';
            $imagen = '';
            $pago_id = $_POST["pago_id"];
            $mensualidad_id = $_POST["mensualidad_id"];

            // Guardamos imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                $nombre_original = $_FILES['imagen']['name'];
                $temporal = $_FILES['imagen']['tmp_name'];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;
                $ruta_destino = "recursos/img/";

                if (!is_dir($ruta_destino)) {
                    mkdir($ruta_destino, 0777, true);
                }
                move_uploaded_file($temporal, $ruta_destino . $imagen);
            }

            // Registrar detalle de pago
            $obj_detalles_pago->set_fecha($fecha);
            $obj_detalles_pago->set_monto($monto);
            $obj_detalles_pago->set_monto_dolar($monto_dolar);
            $obj_detalles_pago->set_tipo_pago($tipo_pago);
            $obj_detalles_pago->set_pago_id($pago_id);

            $resultado_detalle_pago = $obj_detalles_pago->realizar_consulta('registrar_detalles');
            if (!$resultado_detalle_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle"]);
                exit;
            }

            $detalle_pago_id = $obj_detalles_pago->realizar_consulta('lastId')["last_id"]; // PUEDE QUE AQUI DE ERROR

            // Registrar transacción bancaria (si aplica)
            if (!empty($referencia) || !empty($imagen)) {
                $obj_bancos_transacciones->set_referencia($referencia);
                $obj_bancos_transacciones->set_imagen($imagen);
                $obj_bancos_transacciones->set_banco_id($banco_id);
                $obj_bancos_transacciones->set_detalle_pago_id($detalle_pago_id);

                $resultado_banco = $obj_bancos_transacciones->realizar_consulta('registrar');
                if (!$resultado_banco["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar banco/transacción"]);
                    exit;
                }
            }

            // Registrar relación con mensualidad
            $obj_pagos_mensualidad->set_detalle_pago_id($detalle_pago_id);
            $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
            $resultado_relacion = $obj_pagos_mensualidad->realizar_consulta('registrar');

            if (!$resultado_relacion["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar relación con mensualidad"]);
                exit;
            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Detalle de pago registrado exitosamente",
                "id_detalle" => $detalle_pago_id
            ]);
        } elseif ($operacion == "consulta_especifica_detalles") {
            $obj_detalles_pago->set_id_detalle_pago($_POST["id_detalle_pago"]);
            echo json_encode($obj_detalles_pago->realizar_consulta('consulta_especifica_detalles'));
            exit;
        } elseif ($operacion == "modificar_detalles") {
            $id_pago = $_POST["id_pago"];
            $id_detalle_pago = $_POST["id_detalle_pago"];
            $id_banco_transaccion = $_POST["id_banco_transaccion"] ?? null;
            $fecha = $_POST["fecha"];
            $monto = $_POST["monto"];
            $monto_dolar = $_POST["monto_dolar"];
            $tipo_pago = $_POST["tipo_pago"];
            $referencia = $_POST["referencia"] ?? '';
            $banco_id = $_POST["banco_id"] ?? '';
            $mensualidad_id = $_POST["mensualidad_id"];
            $eliminar_imagen = isset($_POST["eliminar_imagen"]);

            // Obtener imagen actual
            $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
            $imagen_actual = $obj_bancos_transacciones->obtener_imagen_actual();
            $imagen = $imagen_actual;

            if ($eliminar_imagen && !empty($imagen_actual)) {
                $ruta_imagen = "recursos/img/" . $imagen_actual;

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
                if (!empty($imagen) && file_exists("recursos/img/" . $imagen) && is_file("recursos/img/" . $imagen)) {
                    unlink("recursos/img/" . $imagen);
                }

                $nombre_original = $_FILES['imagen']['name'];
                $temporal = $_FILES['imagen']['tmp_name'];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;

                move_uploaded_file($temporal, "recursos/img/" . $imagen);
            }

            // Modificar detalle
            $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
            $obj_detalles_pago->set_fecha($fecha);
            $obj_detalles_pago->set_monto($monto);
            $obj_detalles_pago->set_monto_dolar($monto_dolar);
            $obj_detalles_pago->set_tipo_pago($tipo_pago);
            $obj_detalles_pago->set_pago_id($id_pago);

            $resultado_modificar = $obj_detalles_pago->realizar_consulta('modificar_detalles');
            if (!$resultado_modificar["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al modificar detalle"]);
                exit;
            }

            // Transacción bancaria si aplica
            $requiere_transaccion = in_array($tipo_pago, ["Transferencia", "Pago Movil"]);
            if ($requiere_transaccion) {
                $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago);
                $obj_bancos_transacciones->set_referencia($referencia);
                $obj_bancos_transacciones->set_imagen($imagen);
                $obj_bancos_transacciones->set_banco_id($banco_id);

                if (!empty($id_banco_transaccion)) { // POSIBLEMENTE DE ERROR
                    $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                    $consulta_transaccion = $obj_bancos_transacciones->realizar_consulta('consulta_especifica');

                    if ($consulta_transaccion && isset($consulta_transaccion["id_banco_transaccion"])) {
                        $resultado_banco = $obj_bancos_transacciones->realizar_consulta('modificar');
                    } else {
                        $resultado_banco = $obj_bancos_transacciones->realizar_consulta('registrar');
                    }
                } else {
                    $resultado_banco = $obj_bancos_transacciones->realizar_consulta('registrar');
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
                    $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                    $resultado_eliminar = $obj_bancos_transacciones->realizar_consulta('eliminar');
                    if (!$resultado_eliminar["estatus"]) {
                        echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar transacción bancaria"]);
                        exit;
                    }
                }
            }

            // Relación en tabla puente
            $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);
            $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
            $resultado_puente = $obj_pagos_mensualidad->realizar_consulta('modificar');

            if (!$resultado_puente["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error en relación mensualidad"]);
                exit;
            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Detalle modificado correctamente",
                "id_detalle" => $id_detalle_pago,
                "imagen" => $imagen,
                "referencia" => $referencia,
                "id_banco_transaccion" => $id_banco_transaccion
            ]);
            exit;
        } elseif ($operacion == "eliminar_detalles") {
            $id_detalle_pago = $_POST["id_detalle_pago"];

            // Seteamos el ID en cada objeto
            $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
            $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago);
            $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);

            // Consultamos el detalle
            $detalle = $obj_detalles_pago->realizar_consulta('consulta_especifica_detalles');

            if (!$detalle || !isset($detalle["id_detalle_pago"])) {
                echo json_encode(["estatus" => false, "mensaje" => "No se encontró el detalle a eliminar"]);
                exit;
            }

            // Eliminar imagen si existe
            if (!empty($detalle["imagen"])) {
                $ruta = "recursos/img/" . $detalle["imagen"];
                if (file_exists($ruta) && is_file($ruta)) {
                    unlink($ruta);
                }
            }

            // ====== DESHABILITADO POR LOS MOMENTOS =======
            // Si tiene transacción bancaria, la eliminamos
            // if (!empty($detalle["id_banco_transaccion"])) {
            //     $obj_bancos_transacciones->set_id_banco_transaccion($detalle["id_banco_transaccion"]);
            //     $resultado_banco = $obj_bancos_transacciones->realizar_consulta('eliminar');
            // } else {
            //     $resultado_banco = ["estatus" => true];
            // }

            // // Eliminar relación con mensualidad 
            // $resultado_puente = $obj_pagos_mensualidad->realizar_consulta('eliminar');

            // Eliminar el detalle
            $resultado_detalle = $obj_detalles_pago->realizar_consulta('eliminar_detalles');

            // var_dump("Resultado Banco: ",$resultado_banco["estatus"]);
            // var_dump("Resultado Puente: ",$resultado_puente["estatus"]);
            // var_dump("Resultado Detalle: ",$resultado_detalle["estatus"]);

            // Confirmamos que todo fue bien
            if (
                $resultado_detalle["estatus"]
            ) {
                echo json_encode(["estatus" => true, "mensaje" => "Detalle eliminado correctamente"]);
            } else {
                echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar el detalle y sus relaciones"]);
            }

            exit;
        } elseif ($operacion == "ultimo_id_detalle") {
            echo json_encode($obj_detalles_pago->realizar_consulta('lastId'));
        }
        // ---------------------- Todo este bloque es para registrar un pago ----------------------
        elseif ($operacion == "registrar") {

            $monto_mensualidad = $_POST["monto_mensualidad"];
            $estado = $_POST["estado"];
            $observacion = $_POST["observacion"];

            $obj_pago->set_monto($monto_mensualidad);
            $obj_pago->set_estado($estado);
            $obj_pago->set_observacion($observacion);
            $resultado_registro_pago = $obj_pago->realizar_consulta('registrar');  // Registrar el pagoç
            if (!$resultado_registro_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar pago"]);
                exit;
            }

            $ultimo_pago = $obj_pago->realizar_consulta('lastId');
            $pago_id = $ultimo_pago["last_id"];

            $monto = $_POST["monto"];
            $fecha = $_POST["fecha"];
            $monto_dolar = $_POST["monto_dolar"];
            $tipo_pago = $_POST["tipo_pago"];
            $banco_id = $_POST["banco_id"];
            $referencia = $_POST["referencia"];
            $imagen = '';
            $apartamento_id = $_POST["apartamento_id"];
            $mensualidad_id = $_POST["mensualidad_id"];

            $detalle_pago_id = null;

            $indice_imagen = 0; // porque lo del ajax lo ignora

            foreach ($fecha as $i => $f) {
                $obj_detalles_pago->set_fecha($f);
                $obj_detalles_pago->set_monto($monto[$i]);
                $obj_detalles_pago->set_monto_dolar($monto_dolar[$i] ?? 0);
                $obj_detalles_pago->set_tipo_pago($tipo_pago[$i]);
                $obj_detalles_pago->set_pago_id($pago_id);

                $resultado_detalle = $obj_detalles_pago->realizar_consulta('registrar_detalles');
                if (!$resultado_detalle["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle de pago $i"]);
                    exit;
                }

                $id_detalle = $obj_detalles_pago->realizar_consulta('lastId')["last_id"];
                $detalle_pago_id = $id_detalle; // guardo el último detalle creado

                if (!empty($referencia[$i])) {

                    $imagen_detalle = '';
                    if (isset($_FILES['imagen']['name'][$indice_imagen]) && $_FILES['imagen']['error'][$indice_imagen] === 0) {
                        $nombre_original = $_FILES['imagen']['name'][$indice_imagen];
                        $temporal = $_FILES['imagen']['tmp_name'][$indice_imagen];
                        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

                        $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-\.]/", "", pathinfo($nombre_original, PATHINFO_FILENAME));
                        $imagen_detalle = $nombre_sanitizado . '' . time() . '' . rand(100, 999) . '.' . $extension;

                        $ruta_destino = "recursos/img/pagos/";
                        if (!is_dir($ruta_destino)) {
                            mkdir($ruta_destino, 0777, true);
                        }

                        move_uploaded_file($temporal, $ruta_destino . $imagen_detalle);
                    }

                    $obj_bancos_transacciones->set_referencia($referencia[$i]);
                    $obj_bancos_transacciones->set_imagen($imagen_detalle);
                    $obj_bancos_transacciones->set_banco_id($banco_id[$i]);
                    $obj_bancos_transacciones->set_detalle_pago_id($id_detalle);

                    $resultado_banco = $obj_bancos_transacciones->realizar_consulta('registrar');
                    if (!$resultado_banco["estatus"]) {
                        echo json_encode(["estatus" => false, "mensaje" => "Error al registrar banco en detalle $i"]);
                        exit;
                    }
                    $indice_imagen++;
                }

                $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle);

                // Aquí ojo con mensualidad_id, si es array usa $mensualidad_id[$i]
                if (is_array($mensualidad_id)) {
                    $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id[$i]);
                } else {
                    $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
                }

                $resultado_puente = $obj_pagos_mensualidad->realizar_consulta('registrar');
                if (!$resultado_puente["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al asociar mensualidad en detalle $i"]);
                    exit;
                }

            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Pago registrado con éxito",
                "id_pago" => $pago_id,
                "id_detalle" => $detalle_pago_id,
                "referencia" => $referencia,
                "imagen" => $imagen
            ]);
            exit;
        } elseif ($operacion == "consulta_especifica") { // Metodo privado

            $id_pago = $_POST["id_pago"];
            $obj_pago->set_id_pago($id_pago);
            echo json_encode($obj_pago->realizar_consulta('consulta_especifica'));

        } elseif ($operacion == "modificar") { // Metodo privado

            $id_pago = $_POST["id_pago"];
            // $id_detalle_pago = $_POST["id_detalle_pago"];
            // $id_banco_transaccion = $_POST["id_banco_transaccion"];

            // Modificar un Pago
            $monto_mensualidad = $_POST["monto_mensualidad"];
            $estado = $_POST["estado"];
            $observacion = $_POST["observacion"];
            $obj_pago->set_id_pago($id_pago);
            $obj_pago->set_estado($estado);
            $obj_pago->set_observacion($observacion);
            $resultado_modificar_pago = $obj_pago->realizar_consulta('modificar');  // Modificar el pago
            if (!$resultado_modificar_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al modificar el pago", 'err' => $resultado_modificar_pago["mensaje"]]);
                exit;
            }
            // ...

            $obj_detalles_pago->set_pago_id($id_pago);
            $obj_detalles_pago->eliminar_detalles_por_pago();

            $banco_index = 0;
            $referencia_index = 0;
            $imagen_bancaria_index = 0;
            $imagen_existente_index = 0;

            // Modificar Detalles del Pago
            // $monto = $_POST["monto"];
            $fecha = $_POST["fecha"];
            // $monto_dolar = $_POST["monto_dolar"];
            // $tipo_pago = $_POST["tipo_pago"];

            $apartamento_id = $_POST["apartamento_id"];
            //$mensualidad_id = $_POST["mensualidad_id"];

            foreach ($fecha as $indice => $f) {
                $obj_detalles_pago->set_fecha($f);
                $obj_detalles_pago->set_monto($_POST["monto"][$indice]);
                $obj_detalles_pago->set_monto_dolar($_POST["monto_dolar"][$indice]);
                $obj_detalles_pago->set_tipo_pago($_POST["tipo_pago"][$indice]);
                $obj_detalles_pago->set_pago_id($id_pago);
                $obj_detalles_pago->realizar_consulta('registrar_detalles');
                $id_detalle = $obj_detalles_pago->realizar_consulta('lastId')["last_id"];

                $metodo_actual = $_POST["tipo_pago"][$indice];

                if ($metodo_actual === "Transferencia" || $metodo_actual === "Pago Movil") {

                    $banco_id = $_POST["banco_id"][$banco_index++] ?? null;
                    $referencia = $_POST["referencia"][$referencia_index++] ?? null;

                    $imagen_detalle = '';
                    if (isset($_FILES['imagen']['name'][$imagen_bancaria_index]) && $_FILES['imagen']['error'][$imagen_bancaria_index] === 0) {
                        // ... (código para mover el archivo nuevo) ...
                        $nombre_original = $_FILES['imagen']['name'][$imagen_bancaria_index];
                        $temporal = $_FILES['imagen']['tmp_name'][$imagen_bancaria_index];
                        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                        $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                        $nombre_unico = $nombre_sanitizado . '_' . time() . '_' . rand(100, 999) . '.' . $extension;
                        $ruta_destino = "recursos/img/pagos/" . $nombre_unico;
                        if (move_uploaded_file($temporal, $ruta_destino)) {
                            $imagen_detalle = $nombre_unico;
                        }
                    } elseif (isset($_POST['imagen_existente'][$imagen_existente_index])) {
                        $imagen_detalle = $_POST['imagen_existente'][$imagen_existente_index++];
                    }
                    $imagen_bancaria_index++;

                    if (!empty($banco_id)) {
                        $obj_bancos_transacciones->set_referencia($referencia);
                        $obj_bancos_transacciones->set_imagen($imagen_detalle);
                        $obj_bancos_transacciones->set_banco_id($banco_id);
                        $obj_bancos_transacciones->set_detalle_pago_id($id_detalle);
                        $obj_bancos_transacciones->realizar_consulta('registrar');
                    }
                }

                $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle);
                $obj_pagos_mensualidad->set_mensualidad_id($_POST["mensualidad_id"]);
                $obj_pagos_mensualidad->realizar_consulta('registrar');
            }

            $obj_pago->set_id_pago($id_pago);
            $pago_completo = $obj_pago->realizar_consulta('consulta_especifica');
            echo json_encode(["estatus" => true, "mensaje" => "Pago modificado correctamente", "pago" => $pago_completo]);

        } elseif ($operacion == "eliminar") {
            
            //se guardan el id de la variable a eliminar
            $id_pago = $_POST["id_pago"];
            $obj_pago->set_id_pago($id_pago);

            echo json_encode($obj_pago->realizar_consulta('eliminar'));

        } elseif ($operacion == "ultimo_id") {
            echo json_encode($obj_pago->realizar_consulta('lastId'));
        }

        exit;//es salida en ingles... No puede faltar
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "referencia") {
            $obj_bancos_transacciones->set_referencia($_POST["referencia"]);
            echo json_encode($obj_bancos_transacciones->realizar_consulta('validar'));
        }

        exit;
    }
    //FIN de AJAX
    require_once "vista/pagos/pagos_vista.php";

    // ===================== USUARIO ========================
} else { //date("Y-m-d")  ==================================== DETALLES PAGOS ====================================
    if (isset($_POST["operacion"])) {
        $operacion = $_POST["operacion"];

        if ($operacion == "consulta") {
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo json_encode($obj_pago->consultarPorCorreo($_SESSION["usuario"]));
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json
        elseif ($operacion == "consultar_mensualidades") {
            $apartamento_id = $_POST["apartamento_id"];
            $mensualidades = $obj_pago->consultarMensualidadPendiente($apartamento_id);
            echo json_encode($mensualidades);
        } elseif ($operacion == "consultar_detalles") {
            $obj_detalles_pago->set_pago_id($_POST["id_pago"]);
            echo json_encode($obj_detalles_pago->realizar_consulta('consultar_detalles'));
            exit;
        } elseif ($operacion == "registrar_detalles") {
            // Validación básica
            if (!isset($_POST["pago_id"]) || !isset($_POST["mensualidad_id"])) {
                echo json_encode(["estatus" => false, "mensaje" => "Faltan datos obligatorios"]);
                exit;
            }

            // Capturamos los datos del formulario
            $fecha = $_POST["fecha"];
            $monto = $_POST["monto"];
            $monto_dolar = $_POST["monto_dolar"];
            $tipo_pago = $_POST["tipo_pago"];
            $referencia = $_POST["referencia"] ?? '';
            $banco_id = $_POST["banco_id"] ?? '';
            $imagen = '';
            $pago_id = $_POST["pago_id"];
            $mensualidad_id = $_POST["mensualidad_id"];

            // Guardamos imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                $nombre_original = $_FILES['imagen']['name'];
                $temporal = $_FILES['imagen']['tmp_name'];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;
                $ruta_destino = "recursos/img/";

                if (!is_dir($ruta_destino)) {
                    mkdir($ruta_destino, 0777, true);
                }
                move_uploaded_file($temporal, $ruta_destino . $imagen);
            }

            // Registrar detalle de pago
            $obj_detalles_pago->set_fecha($fecha);
            $obj_detalles_pago->set_monto($monto);
            $obj_detalles_pago->set_monto_dolar($monto_dolar);
            $obj_detalles_pago->set_tipo_pago($tipo_pago);
            $obj_detalles_pago->set_pago_id($pago_id);

            $resultado_detalle_pago = $obj_detalles_pago->realizar_consulta('registrar_detalles');
            if (!$resultado_detalle_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle"]);
                exit;
            }

            $detalle_pago_id = $obj_detalles_pago->realizar_consulta('lastId')["last_id"]; // PUEDE QUE AQUI DE ERROR

            // Registrar transacción bancaria (si aplica)
            if (!empty($referencia) || !empty($imagen)) {
                $obj_bancos_transacciones->set_referencia($referencia);
                $obj_bancos_transacciones->set_imagen($imagen);
                $obj_bancos_transacciones->set_banco_id($banco_id);
                $obj_bancos_transacciones->set_detalle_pago_id($detalle_pago_id);

                $resultado_banco = $obj_bancos_transacciones->realizar_consulta('registrar');
                if (!$resultado_banco["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar banco/transacción"]);
                    exit;
                }
            }

            // Registrar relación con mensualidad
            $obj_pagos_mensualidad->set_detalle_pago_id($detalle_pago_id);
            $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
            $resultado_relacion = $obj_pagos_mensualidad->realizar_consulta('registrar');

            if (!$resultado_relacion["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar relación con mensualidad"]);
                exit;
            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Detalle de pago registrado exitosamente",
                "id_detalle" => $detalle_pago_id
            ]);
        } elseif ($operacion == "consulta_especifica_detalles") {
            $obj_detalles_pago->set_id_detalle_pago($_POST["id_detalle_pago"]);
            echo json_encode($obj_detalles_pago->realizar_consulta('consulta_especifica_detalles'));
            exit;
        } elseif ($operacion == "modificar_detalles") {
            $id_pago = $_POST["id_pago"];
            $id_detalle_pago = $_POST["id_detalle_pago"];
            $id_banco_transaccion = $_POST["id_banco_transaccion"] ?? null;
            $fecha = $_POST["fecha"];
            $monto = $_POST["monto"];
            $monto_dolar = $_POST["monto_dolar"];
            $tipo_pago = $_POST["tipo_pago"];
            $referencia = $_POST["referencia"] ?? '';
            $banco_id = $_POST["banco_id"] ?? '';
            $mensualidad_id = $_POST["mensualidad_id"];
            $eliminar_imagen = isset($_POST["eliminar_imagen"]);

            // Obtener imagen actual
            $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
            $imagen_actual = $obj_bancos_transacciones->obtener_imagen_actual();
            $imagen = $imagen_actual;

            if ($eliminar_imagen && !empty($imagen_actual)) {
                $ruta_imagen = "recursos/img/" . $imagen_actual;

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
                if (!empty($imagen) && file_exists("recursos/img/" . $imagen) && is_file("recursos/img/" . $imagen)) {
                    unlink("recursos/img/" . $imagen);
                }

                $nombre_original = $_FILES['imagen']['name'];
                $temporal = $_FILES['imagen']['tmp_name'];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;

                move_uploaded_file($temporal, "recursos/img/" . $imagen);
            }

            // Modificar detalle
            $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
            $obj_detalles_pago->set_fecha($fecha);
            $obj_detalles_pago->set_monto($monto);
            $obj_detalles_pago->set_monto_dolar($monto_dolar);
            $obj_detalles_pago->set_tipo_pago($tipo_pago);
            $obj_detalles_pago->set_pago_id($id_pago);

            $resultado_modificar = $obj_detalles_pago->realizar_consulta('modificar_detalles');
            if (!$resultado_modificar["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al modificar detalle"]);
                exit;
            }

            // Transacción bancaria si aplica
            $requiere_transaccion = in_array($tipo_pago, ["Transferencia", "Pago Movil"]);
            if ($requiere_transaccion) {
                $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago);
                $obj_bancos_transacciones->set_referencia($referencia);
                $obj_bancos_transacciones->set_imagen($imagen);
                $obj_bancos_transacciones->set_banco_id($banco_id);

                if (!empty($id_banco_transaccion)) { // POSIBLEMENTE DE ERROR
                    $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                    $consulta_transaccion = $obj_bancos_transacciones->realizar_consulta('consulta_especifica');

                    if ($consulta_transaccion && isset($consulta_transaccion["id_banco_transaccion"])) {
                        $resultado_banco = $obj_bancos_transacciones->realizar_consulta('modificar');
                    } else {
                        $resultado_banco = $obj_bancos_transacciones->realizar_consulta('registrar');
                    }
                } else {
                    $resultado_banco = $obj_bancos_transacciones->realizar_consulta('registrar');
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
                    $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                    $resultado_eliminar = $obj_bancos_transacciones->realizar_consulta('eliminar');
                    if (!$resultado_eliminar["estatus"]) {
                        echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar transacción bancaria"]);
                        exit;
                    }
                }
            }

            // Relación en tabla puente
            $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);
            $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
            $resultado_puente = $obj_pagos_mensualidad->realizar_consulta('modificar');

            if (!$resultado_puente["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error en relación mensualidad"]);
                exit;
            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Detalle modificado correctamente",
                "id_detalle" => $id_detalle_pago,
                "imagen" => $imagen,
                "referencia" => $referencia,
                "id_banco_transaccion" => $id_banco_transaccion
            ]);
            exit;
        } elseif ($operacion == "eliminar_detalles") {
            $id_detalle_pago = $_POST["id_detalle_pago"];

            // Seteamos el ID en cada objeto
            $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
            $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago);
            $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);

            // Consultamos el detalle
            $detalle = $obj_detalles_pago->realizar_consulta('consulta_especifica_detalles');

            if (!$detalle || !isset($detalle["id_detalle_pago"])) {
                echo json_encode(["estatus" => false, "mensaje" => "No se encontró el detalle a eliminar"]);
                exit;
            }

            // Eliminar imagen si existe
            if (!empty($detalle["imagen"])) {
                $ruta = "recursos/img/" . $detalle["imagen"];
                if (file_exists($ruta) && is_file($ruta)) {
                    unlink($ruta);
                }
            }

            // ====== DESHABILITADO POR LOS MOMENTOS =======
            // Si tiene transacción bancaria, la eliminamos
            // if (!empty($detalle["id_banco_transaccion"])) {
            //     $obj_bancos_transacciones->set_id_banco_transaccion($detalle["id_banco_transaccion"]);
            //     $resultado_banco = $obj_bancos_transacciones->realizar_consulta('eliminar');
            // } else {
            //     $resultado_banco = ["estatus" => true];
            // }

            // // Eliminar relación con mensualidad 
            // $resultado_puente = $obj_pagos_mensualidad->realizar_consulta('eliminar');

            // Eliminar el detalle
            $resultado_detalle = $obj_detalles_pago->realizar_consulta('eliminar_detalles');

            // var_dump("Resultado Banco: ",$resultado_banco["estatus"]);
            // var_dump("Resultado Puente: ",$resultado_puente["estatus"]);
            // var_dump("Resultado Detalle: ",$resultado_detalle["estatus"]);

            // Confirmamos que todo fue bien
            if (
                $resultado_detalle["estatus"]
            ) {
                echo json_encode(["estatus" => true, "mensaje" => "Detalle eliminado correctamente"]);
            } else {
                echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar el detalle y sus relaciones"]);
            }

            exit;
        } elseif ($operacion == "ultimo_id_detalle") {
            echo json_encode($obj_detalles_pago->realizar_consulta('lastId'));
        } elseif ($operacion == "registrar") {
            $monto_mensualidad = $_POST["monto_mensualidad"];
            $estado = $_POST["estado"];
            $observacion = $_POST["observacion"];

            $obj_pago->set_monto($monto_mensualidad);
            $obj_pago->set_estado($estado);
            $obj_pago->set_observacion($observacion);
            $resultado_registro_pago = $obj_pago->realizar_consulta('registrar');  // Registrar el pagoç
            if (!$resultado_registro_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar pago"]);
                exit;
            }

            $ultimo_pago = $obj_pago->realizar_consulta('lastId');
            $pago_id = $ultimo_pago["last_id"];

            $monto = $_POST["monto"];
            $fecha = $_POST["fecha"];
            $monto_dolar = $_POST["monto_dolar"];
            $tipo_pago = $_POST["tipo_pago"];
            $banco_id = $_POST["banco_id"];
            $referencia = $_POST["referencia"];
            $imagen = '';
            $apartamento_id = $_POST["apartamento_id"];
            $mensualidad_id = $_POST["mensualidad_id"];

            $detalle_pago_id = null;

            $indice_imagen = 0; // porque lo del ajax lo ignora

            foreach ($fecha as $i => $f) {
                $obj_detalles_pago->set_fecha($f);
                $obj_detalles_pago->set_monto($monto[$i]);
                $obj_detalles_pago->set_monto_dolar($monto_dolar[$i] ?? 0);
                $obj_detalles_pago->set_tipo_pago($tipo_pago[$i]);
                $obj_detalles_pago->set_pago_id($pago_id);

                $resultado_detalle = $obj_detalles_pago->realizar_consulta('registrar_detalles');
                if (!$resultado_detalle["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle de pago $i"]);
                    exit;
                }

                $id_detalle = $obj_detalles_pago->realizar_consulta('lastId')["last_id"];
                $detalle_pago_id = $id_detalle; // guardo el último detalle creado

                if (!empty($referencia[$i])) {

                    $imagen_detalle = '';
                    if (isset($_FILES['imagen']['name'][$indice_imagen]) && $_FILES['imagen']['error'][$indice_imagen] === 0) {
                        $nombre_original = $_FILES['imagen']['name'][$indice_imagen];
                        $temporal = $_FILES['imagen']['tmp_name'][$indice_imagen];
                        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

                        $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-\.]/", "", pathinfo($nombre_original, PATHINFO_FILENAME));
                        $imagen_detalle = $nombre_sanitizado . '' . time() . '' . rand(100, 999) . '.' . $extension;

                        $ruta_destino = "recursos/img/";
                        if (!is_dir($ruta_destino)) {
                            mkdir($ruta_destino, 0777, true);
                        }

                        move_uploaded_file($temporal, $ruta_destino . $imagen_detalle);
                    }

                    $obj_bancos_transacciones->set_referencia($referencia[$i]);
                    $obj_bancos_transacciones->set_imagen($imagen_detalle);
                    $obj_bancos_transacciones->set_banco_id($banco_id[$i]);
                    $obj_bancos_transacciones->set_detalle_pago_id($id_detalle);

                    $resultado_banco = $obj_bancos_transacciones->realizar_consulta('registrar');
                    if (!$resultado_banco["estatus"]) {
                        echo json_encode(["estatus" => false, "mensaje" => "Error al registrar banco en detalle $i"]);
                        exit;
                    }
                    $indice_imagen++;
                }

                $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle);

                // Aquí ojo con mensualidad_id, si es array usa $mensualidad_id[$i]
                if (is_array($mensualidad_id)) {
                    $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id[$i]);
                } else {
                    $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
                }

                $resultado_puente = $obj_pagos_mensualidad->realizar_consulta('registrar');
                if (!$resultado_puente["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al asociar mensualidad en detalle $i"]);
                    exit;
                }

            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Pago registrado con éxito",
                "id_pago" => $pago_id,
                "id_detalle" => $detalle_pago_id,
                "referencia" => $referencia,
                "imagen" => $imagen
            ]);
            exit;
        } elseif ($operacion == "consulta_especifica") { // Traer los otros consultar especificos
            $id_pago = $_POST["id_pago"];
            $obj_pago->set_id_pago($id_pago);
            echo json_encode($obj_pago->realizar_consulta('consulta_especifica'));
        } elseif ($operacion == "modificar") {
            $id_pago = $_POST["id_pago"];
            $id_detalle_pago = $_POST["id_detalle_pago"];
            $id_banco_transaccion = $_POST["id_banco_transaccion"];

            // Modificar un Pago
            $monto_mensualidad = $_POST["monto_mensualidad"];
            $estado = $_POST["estado"];
            $observacion = $_POST["observacion"];
            $obj_pago->set_id_pago($id_pago);
            $obj_pago->set_estado($estado);
            $obj_pago->set_observacion($observacion);
            $resultado_modificar_pago = $obj_pago->realizar_consulta('modificar');  // Modificar el pago
            if (!$resultado_modificar_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al modificar el pago"]);
                exit;
            }
            // ...

            // Modificar Detalles del Pago
            $monto = $_POST["monto"];
            $fecha = $_POST["fecha"];
            $monto_dolar = $_POST["monto_dolar"];
            $tipo_pago = $_POST["tipo_pago"];
            $banco_id = $_POST["banco_id"];
            $referencia = $_POST["referencia"];
            $imagen = '';
            $apartamento_id = $_POST["apartamento_id"];
            $mensualidad_id = $_POST["mensualidad_id"];

            $indice_imagen = 0;

            for ($i = 0; $i < count($fecha); $i++) {
                $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago[$i]);
                $obj_detalles_pago->set_fecha($fecha[$i]);
                $obj_detalles_pago->set_monto($monto[$i]);
                $obj_detalles_pago->set_monto_dolar($monto_dolar[$i] ?? 0);
                $obj_detalles_pago->set_tipo_pago($tipo_pago[$i]);
                $obj_detalles_pago->set_pago_id($id_pago);

                $resultado_modificar_detalle = $obj_detalles_pago->realizar_consulta('modificar_detalles');
                // if (!$resultado_modificar_detalle["estatus"]) {
                //     echo json_encode(["estatus" => false, "mensaje" => "Error al modificar detalle del pago en $i"]);
                //     exit;
                // }

                if (!empty($referencia[$i])) {
                    $eliminar_imagen = isset($_POST['eliminar_imagen'][$i]);

                    $imagen_actual = null;

                    if (!empty($id_banco_transaccion[$i])) {
                        $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion[$i]);
                        $imagen_actual = $obj_bancos_transacciones->obtener_imagen_actual();
                    }

                    if ($eliminar_imagen && !empty($imagen_actual) && file_exists("recursos/img/" . $imagen_actual)) {
                        unlink("recursos/img/" . $imagen_actual);
                        $imagen_actual = ''; // Para que no quede en la base
                    }

                    if (isset($_FILES['imagen']['name'][$indice_imagen]) && $_FILES['imagen']['error'][$indice_imagen] === 0) {
                        if (!empty($imagen_actual) && file_exists("recursos/img/" . $imagen_actual)) {
                            unlink("recursos/img/" . $imagen_actual);
                        }

                        $nombre_original = $_FILES['imagen']['name'][$indice_imagen];
                        $temporal = $_FILES['imagen']['tmp_name'][$indice_imagen];
                        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

                        $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-\.]/", "", pathinfo($nombre_original, PATHINFO_FILENAME));
                        $imagen_detalle = $nombre_sanitizado . '' . time() . '' . rand(100, 999) . '.' . $extension;

                        $ruta_destino = "recursos/img/";
                        if (!is_dir($ruta_destino)) {
                            mkdir($ruta_destino, 0777, true);
                        }

                        move_uploaded_file($temporal, $ruta_destino . $imagen_detalle);
                    } else {
                        $imagen_detalle = $imagen_actual;
                    }

                    $obj_bancos_transacciones->set_referencia($referencia[$i]);
                    $obj_bancos_transacciones->set_imagen($imagen_detalle);
                    $obj_bancos_transacciones->set_banco_id($banco_id[$i]);
                    $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago[$i]);

                    $resultado_banco = $obj_bancos_transacciones->realizar_consulta('modificar');

                    // if (!$resultado_banco["estatus"]) {
                    //     echo json_encode([
                    //         "estatus" => false,
                    //         "mensaje" => "Error al modificar banco en detalle $i",
                    //         "debug" => [
                    //             "referencia" => $referencia[$i] ?? null,
                    //             "imagen" => $imagen_detalle ?? null,
                    //             "banco_id" => $banco_id[$i] ?? null,
                    //             "detalle_pago_id" => $id_detalle_pago[$i] ?? null
                    //         ]
                    //     ]);
                    //     exit;
                    // }
                    $indice_imagen++;
                }

                $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago[$i]);

                // Aquí ojo con mensualidad_id, si es array usa $mensualidad_id[$i]
                if (is_array($mensualidad_id)) {
                    $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id[$i]);
                } else {
                    $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
                }

                $resultado_puente = $obj_pagos_mensualidad->realizar_consulta('modificar');
                if (!$resultado_puente["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al asociar mensualidad en detalle $i"]);
                    exit;
                }
            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Pago modificado con éxito",
                "id_pago" => $id_pago,
                "id_detalle_pago" => $id_detalle_pago,
                "referencia" => $referencia
            ]);
            exit;
        } elseif ($operacion == "eliminar") {
            //se guardan el id de la variable a eliminar
            $id_pago = $_POST["id_pago"];
            $obj_pago->set_id_pago($id_pago);

            echo json_encode($obj_pago->realizar_consulta('eliminar'));

        } elseif ($operacion == "ultimo_id") {
            echo json_encode($obj_pago->realizar_consulta('lastId'));
        }

        exit;//es salida en ingles... No puede faltar
    }

    if (isset($_POST["validar"])) {
        $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
        if ($validar == "referencia") {
            $obj_bancos_transacciones->set_referencia($_POST["referencia"]);
            echo json_encode($obj_bancos_transacciones->realizar_consulta('validar'));
        }

        exit;
    }
    //FIN de AJAX
    require_once "vista/pagos/pagos_propietarios_vista.php";
}
?>