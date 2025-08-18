<?php
    require_once("modelo/pagos_modelo.php");
    require_once("modelo/banco_modelo.php");
    require_once("modelo/detalles_pago_modelo.php");
    require_once("modelo/pagos_mensualidad_modelo.php");
    require_once("modelo/bancos_transacciones_modelo.php");
    require_once("modelo/apartamentos_modelo.php");
    require_once("modelo/notificaciones_modelo.php");

    require_once("vendor/autoload.php");
    use Dompdf\Dompdf;

    $obj_pago = new Pagos(); // Objeto pago
    $obj_banco = new Banco(); // Objeto banco
    $obj_apartamento = new Apartamento(); // Objeto apartamento
    $obj_detalles_pago = new Detalles_pago(); // Objeto detalles pago
    $obj_pagos_mensualidad = new Pagos_mensualidad(); // Objeto pagos mensualidad
    $obj_bancos_transacciones = new Bancos_transacciones(); // Objeto bancos transacciones
    $notificacion_obj = new Notificaciones();

    if(isset($_SESSION["rol"]) && $_SESSION["rol"] != "Propietario"){
        //$registro_mensualidad = $obj_pago->consultarMensualidad();
        $registro_apartamento = $obj_apartamento->consultar();
    }else{
        $registro_apartamento = $obj_apartamento->consultar_propietario($_SESSION["usuario"]);
    }
    
    $registro_banco = $obj_banco->consultar();

    if(isset($_SESSION["rol"]) && $_SESSION["rol"] != "Propietario"){
        if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];
        
        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_pago->consultar());
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json
        elseif ($operacion == "consultar_mensualidades"){
            $apartamento_id = $_POST["apartamento_id"];
            $mensualidades = $obj_pago->consultarMensualidad($apartamento_id);
            echo json_encode($mensualidades);
        }
        elseif ($operacion == "consultar_detalles") {
            $obj_detalles_pago->set_pago_id($_POST["id_pago"]);
            echo json_encode($obj_detalles_pago->consultar_detalles_por_pago());
            exit;
        }
        elseif ($operacion == "registrar_detalles") {
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

            // Caja activa
            $caja = $obj_pago->consultarCajaActual();
            if (!isset($caja["id_caja_chica"])) {
                echo json_encode(["estatus" => false, "mensaje" => "No hay caja activa"]);
                exit;
            }
            $id_caja = $caja["id_caja_chica"];

            // Registrar detalle de pago
            $obj_detalles_pago->set_fecha($fecha);
            $obj_detalles_pago->set_monto($monto);
            $obj_detalles_pago->set_monto_dolar($monto_dolar);
            $obj_detalles_pago->set_tipo_pago($tipo_pago);
            $obj_detalles_pago->set_pago_id($pago_id);
            $obj_detalles_pago->set_caja_id($id_caja);
            
            $resultado_detalle_pago = $obj_detalles_pago->registrar_detalle_pago();
            if (!$resultado_detalle_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle"]);
                exit;
            }

            $detalle_pago_id = $obj_detalles_pago->lastId()["mensaje"];

            // Registrar transacción bancaria (si aplica)
            if (!empty($referencia) || !empty($imagen)) {
                $obj_bancos_transacciones->set_referencia($referencia);
                $obj_bancos_transacciones->set_imagen($imagen);
                $obj_bancos_transacciones->set_banco_id($banco_id);
                $obj_bancos_transacciones->set_detalle_pago_id($detalle_pago_id);

                $resultado_banco = $obj_bancos_transacciones->registrar_banco_transaccion();
                if (!$resultado_banco["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar banco/transacción"]);
                    exit;
                }
            }

            // Registrar relación con mensualidad
            $obj_pagos_mensualidad->set_detalle_pago_id($detalle_pago_id);
            $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
            $resultado_relacion = $obj_pagos_mensualidad->registrar_pagos_mensualidad();

            if (!$resultado_relacion["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar relación con mensualidad"]);
                exit;
            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Detalle de pago registrado exitosamente",
                "id_detalle" => $detalle_pago_id
            ]);
        }
        elseif ($operacion == "consulta_especifica_detalles") {
            $obj_detalles_pago->set_id_detalle_pago($_POST["id_detalle_pago"]);
            echo json_encode($obj_detalles_pago->consultar_detalle_pago());
            exit;
        } 
        elseif ($operacion == "modificar_detalles") {
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
            $imagen = $obj_bancos_transacciones->obtener_imagen_actual();

            if (!empty($imagen)) {
                $ruta_imagen = "recursos/img/" . $imagen;

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

            // Caja activa
            $caja = $obj_pago->consultarCajaActual();
            if (!isset($caja["id_caja_chica"])) {
                echo json_encode(["estatus" => false, "mensaje" => "No hay caja activa"]);
                exit;
            }
            $id_caja = $caja["id_caja_chica"];
 
            // Modificar detalle
            $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
            $obj_detalles_pago->set_fecha($fecha);
            $obj_detalles_pago->set_monto($monto);
            $obj_detalles_pago->set_monto_dolar($monto_dolar);
            $obj_detalles_pago->set_tipo_pago($tipo_pago);
            $obj_detalles_pago->set_pago_id($id_pago);
            $obj_detalles_pago->set_caja_id($id_caja);

            $resultado_modificar = $obj_detalles_pago->editar_detalle_pago();
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

                if (!empty($id_banco_transaccion)) {
                    $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                    $consulta_transaccion = $obj_bancos_transacciones->consultar_banco_transaccion();

                    if ($consulta_transaccion && isset($consulta_transaccion["id_banco_transaccion"])) {
                        $resultado_banco = $obj_bancos_transacciones->editar_banco_transaccion();
                    } else {
                        $resultado_banco = $obj_bancos_transacciones->registrar_banco_transaccion();
                    }
                } else {
                    $resultado_banco = $obj_bancos_transacciones->registrar_banco_transaccion();
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
                    $resultado_eliminar = $obj_bancos_transacciones->eliminar_banco_transaccion();
                    if (!$resultado_eliminar["estatus"]) {
                        echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar transacción bancaria"]);
                        exit;
                    }
                }
            }

            // Relación en tabla puente
            $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);
            $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
            $resultado_puente = $obj_pagos_mensualidad->editar_pagos_mensualidad();

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
        }
        elseif ($operacion == "eliminar_detalles") {
            $id_detalle_pago = $_POST["id_detalle_pago"];

            // Seteamos el ID en cada objeto
            $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
            $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago);
            $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);

            // Consultamos el detalle
            $detalle = $obj_detalles_pago->consultar_detalle_pago();

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

            // Si tiene transacción bancaria, la eliminamos
            if (!empty($detalle["id_banco_transaccion"])) {
                $obj_bancos_transacciones->set_id_banco_transaccion($detalle["id_banco_transaccion"]);
                $resultado_banco = $obj_bancos_transacciones->eliminar_banco_transaccion();
            } else {
                $resultado_banco = ["estatus" => true];
            }

            // Eliminar relación con mensualidad
            $resultado_puente = $obj_pagos_mensualidad->eliminar_pagos_mensualidad();

            // Eliminar el detalle
            $resultado_detalle = $obj_detalles_pago->eliminar_detalle_pago();

            // Confirmamos que todo fue bien
            if (
                $resultado_detalle["estatus"] &&
                $resultado_banco["estatus"] &&
                $resultado_puente["estatus"]
            ) {
                echo json_encode(["estatus" => true, "mensaje" => "Detalle eliminado correctamente"]);
            } else {
                echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar el detalle y sus relaciones"]);
            }

            exit;
        }
        elseif ($operacion == "registrar") {
            // se guardan las variables a registrar
            $monto_mensualidad = $_POST["monto_mensualidad"];
            $estado = $_POST["estado"];
            $observacion = $_POST["observacion"];

            $obj_pago->set_monto($monto_mensualidad);
            $obj_pago->set_estado($estado);
            $obj_pago->set_observacion($observacion);
            $resultado_registro_pago = $obj_pago->registrar_pago();  // Registrar el pagoç
            if (!$resultado_registro_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar pago"]);
                exit;
            }

            $caja = $obj_pago->consultarCajaActual();

            if (!isset($caja["id_caja_chica"])){
                echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
                exit;
            }

            $id_caja = $caja["id_caja_chica"];

            $ultimo_pago = $obj_pago->lastId();
            $pago_id = $ultimo_pago["mensaje"];

            // Variable para guardar último detalle pago

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
                $obj_detalles_pago->set_caja_id($id_caja);

                $resultado_detalle = $obj_detalles_pago->registrar_detalle_pago();
                if (!$resultado_detalle["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle de pago $i"]);
                    exit;
                }

                $id_detalle = $obj_detalles_pago->lastId()["mensaje"];
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

                    $resultado_banco = $obj_bancos_transacciones->registrar_banco_transaccion();
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

                $resultado_puente = $obj_pagos_mensualidad->registrar_pagos_mensualidad();
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
        }

        elseif ($operacion == "consulta_especifica"){ // Traer los otros consultar especificos
            //se guardan el id para buscar
            $id_pago = $_POST["id_pago"];

            //se usan el setter correspondientes
            $obj_pago->set_id_pago($id_pago);

            echo json_encode($obj_pago->consultar_pago());
        }
 
        elseif ($operacion == "modificar") {
                //se guardan las variables a modificar
                $id_pago = $_POST["id_pago"];
                $id_detalle_pago = $_POST["id_detalle_pago"];
                $id_banco_transaccion = $_POST["id_banco_transaccion"];
                $fecha = $_POST["fecha"];  
                $monto = $_POST["monto"];  
                $monto_dolar = $_POST["monto_dolar"]; 
                $estado = $_POST["estado"];
                $tipo_pago = $_POST["tipo_pago"];
                $banco_id = $_POST["banco_id"];  
                $referencia = $_POST["referencia"];
                $observacion = $_POST["observacion"];
                $mensualidad_id = $_POST["mensualidad_id"];
                $monto_mensualidad = $_POST["monto_mensualidad"];
                // ...

                $eliminar_imagen = isset($_POST["eliminar_imagen"]);

                $imagen = $obj_bancos_transacciones->obtener_imagen_actual();
                if (!empty($imagen)) {
                    $ruta_imagen = "recursos/img/" . $imagen;

                    // Eliminar imagen si el usuario lo pidió y el archivo existe
                    if ($eliminar_imagen && file_exists($ruta_imagen) && is_file($ruta_imagen)) {
                        unlink($ruta_imagen);
                        $imagen = '';
                    }
                } else {
                    $ruta_imagen = ''; // No hay imagen previa
                }

                //  Reemplazo de imagen
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                    // Si hay imagen previa, se elimina
                    if (!empty($imagen) && file_exists("recursos/img/" . $imagen) && is_file("recursos/img/" . $imagen)) {
                        unlink("recursos/img/" . $imagen);
                    }
                    
                    // LO MISMO QUE EN REGISTRAR
                    $nombre_original = $_FILES['imagen']['name'];
                    $temporal = $_FILES['imagen']['tmp_name'];
                    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                    $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                    $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;

                    move_uploaded_file($temporal, "recursos/img/" . $imagen);
                }

                $caja = $obj_pago->consultarCajaActual();

                if (!isset($caja["id_caja_chica"])){
                    echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
                    exit;
                }

                $id_caja = $caja["id_caja_chica"];

                // Modificar Pago
                $obj_pago->set_id_pago($id_pago);
                $obj_pago->set_monto($monto_mensualidad);
                $obj_pago->set_estado($estado);
                $obj_pago->set_observacion($observacion);
                $resultado_modificar_pago = $obj_pago->editar_pago();
                
                if (!$resultado_modificar_pago["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al actualizar pago"]);
                    exit;
                }
            
                // Modificar Detalles del Pago
                $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
                $obj_detalles_pago->set_fecha($fecha);
                $obj_detalles_pago->set_monto($monto);
                $obj_detalles_pago->set_monto_dolar($monto_dolar);
                $obj_detalles_pago->set_tipo_pago($tipo_pago);
                $obj_detalles_pago->set_pago_id($id_pago);
                $obj_detalles_pago->set_caja_id($id_caja);

                $resultado_modificar_detalle = $obj_detalles_pago->editar_detalle_pago();
                if (!$resultado_modificar_detalle["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al actualizar detalle del pago"]);
                    exit;
                }

                $requiere_transaccion_bancaria = in_array($tipo_pago, ["Transferencia", "Pago Movil"]);

                if ($requiere_transaccion_bancaria) {
                    // Asignar datos obligatorios
                    $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago);
                    $obj_bancos_transacciones->set_referencia($referencia ?? 'Sin referencia');

                    $imagen = $imagen ?? "";

                    $obj_bancos_transacciones->set_imagen($imagen);
                    $obj_bancos_transacciones->set_banco_id($banco_id);

                    if (!empty($id_banco_transaccion)) {
                        $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                        $existe_transaccion = $obj_bancos_transacciones->consultar_banco_transaccion();

                        if ($existe_transaccion && isset($existe_transaccion["id_banco_transaccion"])) {
                            $resultado_banco_transaccion = $obj_bancos_transacciones->editar_banco_transaccion();
                        } else {
                            $resultado_banco_transaccion = $obj_bancos_transacciones->registrar_banco_transaccion();
                        }
                    } else {
                        $resultado_banco_transaccion = $obj_bancos_transacciones->registrar_banco_transaccion();
                    }

                    if (!$resultado_banco_transaccion["estatus"]) {
                        echo json_encode(["estatus" => false, "mensaje" => "Error al actualizar transacción bancaria"]);
                        exit;
                    }

                } else {
                    // Es efectivo. Si existía una transacción, la eliminamos
                    if (!empty($id_banco_transaccion)) {
                        $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                        $resultado_eliminar = $obj_bancos_transacciones->eliminar_banco_transaccion();
                        if (!$resultado_eliminar["estatus"]) {
                            echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar transacción bancaria"]);
                            exit;
                        }
                    }
                }

                // Modificar la relación en la tabla puente pagos_mensualidad
                $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);
                $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
                $resultado_puente = $obj_pagos_mensualidad->editar_pagos_mensualidad(); // DEBES TENER ESTA FUNCIÓN

                if (!$resultado_puente["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Pago modificado, pero error en la relación con mensualidad"]);
                    exit;
                }

                echo json_encode([
                    "estatus" => true,
                    "mensaje" => "Pago modificado con éxito",
                    "id_pago" => $id_pago,
                    "id_detalle" => $id_detalle_pago,
                    "referencia" => $referencia,
                    "imagen" => $imagen
                ]);
                exit;
            }

            elseif ($operacion == "eliminar") {
                //se guardan el id de la variable a eliminar
                $id_pago = $_POST["id_pago"];
                $obj_pago->set_id_pago($id_pago);
        
                echo json_encode($obj_pago->eliminar_pago());

            }elseif ($operacion == "ultimo_id"){
                echo json_encode($obj_pago->lastId());
            }

            exit;//es salida en ingles... No puede faltar
        }
 
        if (isset($_POST["validar"])) {
            $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
            if ($validar == "referencia"){
                $obj_bancos_transacciones->set_referencia($_POST["referencia"]);
                echo  json_encode($obj_bancos_transacciones->verificar_bancos_transacciones());
            }
            
            exit;
        }
        //FIN de AJAX
        require_once "vista/pagos/pagos_vista.php";
    }else{ //date("Y-m-d")  ==================================== DETALLES PAGOS ====================================
        if(isset($_POST["operacion"])){
        $operacion = $_POST["operacion"];
        
        if ($operacion == "consulta"){
            // llamamos a la funcion, lo convertimos a json y la mandamos al js con echo
            echo  json_encode($obj_pago->consultarPorCorreo($_SESSION["usuario"]));
            // la hice para que retorne un arreglo, si sale vacio solo mandara un array con false
        }
        //Despues de cada echo se regresa al javascript como respuesta en json
        elseif ($operacion == "consultar_mensualidades"){
            $apartamento_id = $_POST["apartamento_id"];
            $mensualidades = $obj_pago->consultarMensualidad($apartamento_id);
            echo json_encode($mensualidades);
        }
        elseif ($operacion == "consultar_detalles") {
            $obj_detalles_pago->set_pago_id($_POST["id_pago"]);
            echo json_encode($obj_detalles_pago->consultar_detalles_por_pago());
            exit;
        }
        elseif ($operacion == "registrar_detalles") {
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

            // Caja activa
            $caja = $obj_pago->consultarCajaActual();
            if (!isset($caja["id_caja_chica"])) {
                echo json_encode(["estatus" => false, "mensaje" => "No hay caja activa"]);
                exit;
            }
            $id_caja = $caja["id_caja_chica"];

            // Registrar detalle de pago
            $obj_detalles_pago->set_fecha($fecha);
            $obj_detalles_pago->set_monto($monto);
            $obj_detalles_pago->set_monto_dolar($monto_dolar);
            $obj_detalles_pago->set_tipo_pago($tipo_pago);
            $obj_detalles_pago->set_pago_id($pago_id);
            $obj_detalles_pago->set_caja_id($id_caja);
            
            $resultado_detalle_pago = $obj_detalles_pago->registrar_detalle_pago();
            if (!$resultado_detalle_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle"]);
                exit;
            }

            $detalle_pago_id = $obj_detalles_pago->lastId()["mensaje"];

            // Registrar transacción bancaria (si aplica)
            if (!empty($referencia) || !empty($imagen)) {
                $obj_bancos_transacciones->set_referencia($referencia);
                $obj_bancos_transacciones->set_imagen($imagen);
                $obj_bancos_transacciones->set_banco_id($banco_id);
                $obj_bancos_transacciones->set_detalle_pago_id($detalle_pago_id);

                $resultado_banco = $obj_bancos_transacciones->registrar_banco_transaccion();
                if (!$resultado_banco["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar banco/transacción"]);
                    exit;
                }
            }

            // Registrar relación con mensualidad
            $obj_pagos_mensualidad->set_detalle_pago_id($detalle_pago_id);
            $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
            $resultado_relacion = $obj_pagos_mensualidad->registrar_pagos_mensualidad();

            if (!$resultado_relacion["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar relación con mensualidad"]);
                exit;
            }

            $notificacion_obj->set_titulo("Pago registrado");
            $notificacion_obj->set_descripcion("Se ha registrado un nuevo detalle de un pago por parte del usuario: ".$_SESSION["nombre_completo"]);
            $notificacion_obj->set_fecha($fecha);

            $resultado_notificacion = $notificacion_obj->notificar_pago();
            if (!$resultado_notificacion["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Pago registrado, pero falló al notificar"]);
                exit;
            }

            echo json_encode([
                "estatus" => true,
                "mensaje" => "Detalle de pago registrado exitosamente",
                "id_detalle" => $detalle_pago_id
            ]);
        }
        elseif ($operacion == "consulta_especifica_detalles") {
            $obj_detalles_pago->set_id_detalle_pago($_POST["id_detalle_pago"]);
            echo json_encode($obj_detalles_pago->consultar_detalle_pago());
            exit;
        } 
        elseif ($operacion == "modificar_detalles") {
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
            $imagen = $obj_bancos_transacciones->obtener_imagen_actual();

            if (!empty($imagen)) {
                $ruta_imagen = "recursos/img/" . $imagen;

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

            // Caja activa
            $caja = $obj_pago->consultarCajaActual();
            if (!isset($caja["id_caja_chica"])) {
                echo json_encode(["estatus" => false, "mensaje" => "No hay caja activa"]);
                exit;
            }
            $id_caja = $caja["id_caja_chica"];
 
            // Modificar detalle
            $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
            $obj_detalles_pago->set_fecha($fecha);
            $obj_detalles_pago->set_monto($monto);
            $obj_detalles_pago->set_monto_dolar($monto_dolar);
            $obj_detalles_pago->set_tipo_pago($tipo_pago);
            $obj_detalles_pago->set_pago_id($id_pago);
            $obj_detalles_pago->set_caja_id($id_caja);

            $resultado_modificar = $obj_detalles_pago->editar_detalle_pago();
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

                if (!empty($id_banco_transaccion)) {
                    $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                    $consulta_transaccion = $obj_bancos_transacciones->consultar_banco_transaccion();

                    if ($consulta_transaccion && isset($consulta_transaccion["id_banco_transaccion"])) {
                        $resultado_banco = $obj_bancos_transacciones->editar_banco_transaccion();
                    } else {
                        $resultado_banco = $obj_bancos_transacciones->registrar_banco_transaccion();
                    }
                } else {
                    $resultado_banco = $obj_bancos_transacciones->registrar_banco_transaccion();
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
                    $resultado_eliminar = $obj_bancos_transacciones->eliminar_banco_transaccion();
                    if (!$resultado_eliminar["estatus"]) {
                        echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar transacción bancaria"]);
                        exit;
                    }
                }
            }

            // Relación en tabla puente
            $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);
            $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
            $resultado_puente = $obj_pagos_mensualidad->editar_pagos_mensualidad();

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
        }
        elseif ($operacion == "eliminar_detalles") {
            $id_detalle_pago = $_POST["id_detalle_pago"];

            // Seteamos el ID en cada objeto
            $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
            $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago);
            $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);

            // Consultamos el detalle
            $detalle = $obj_detalles_pago->consultar_detalle_pago();

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

            // Si tiene transacción bancaria, la eliminamos
            if (!empty($detalle["id_banco_transaccion"])) {
                $obj_bancos_transacciones->set_id_banco_transaccion($detalle["id_banco_transaccion"]);
                $resultado_banco = $obj_bancos_transacciones->eliminar_banco_transaccion();
            } else {
                $resultado_banco = ["estatus" => true];
            }

            // Eliminar relación con mensualidad
            $resultado_puente = $obj_pagos_mensualidad->eliminar_pagos_mensualidad();

            // Eliminar el detalle
            $resultado_detalle = $obj_detalles_pago->eliminar_detalle_pago();

            // Confirmamos que todo fue bien
            if (
                $resultado_detalle["estatus"] &&
                $resultado_banco["estatus"] &&
                $resultado_puente["estatus"]
            ) {
                echo json_encode(["estatus" => true, "mensaje" => "Detalle eliminado correctamente"]);
            } else {
                echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar el detalle y sus relaciones"]);
            }

            exit;
        }
        elseif ($operacion == "registrar") {
            // se guardan las variables a registrar
            $monto_mensualidad = $_POST["monto_mensualidad"];
            $estado = $_POST["estado"];
            $observacion = $_POST["observacion"];

            $obj_pago->set_monto($monto_mensualidad);
            $obj_pago->set_estado($estado);
            $obj_pago->set_observacion($observacion);
            $resultado_registro_pago = $obj_pago->registrar_pago();  // Registrar el pagoç
            if (!$resultado_registro_pago["estatus"]) {
                echo json_encode(["estatus" => false, "mensaje" => "Error al registrar pago"]);
                exit;
            }

            $caja = $obj_pago->consultarCajaActual();

            if (!isset($caja["id_caja_chica"])){
                echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
                exit;
            }

            $id_caja = $caja["id_caja_chica"];

            $ultimo_pago = $obj_pago->lastId();
            $pago_id = $ultimo_pago["mensaje"];

            // Variable para guardar último detalle pago

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
                $obj_detalles_pago->set_caja_id($id_caja);

                $resultado_detalle = $obj_detalles_pago->registrar_detalle_pago();
                if (!$resultado_detalle["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al registrar detalle de pago $i"]);
                    exit;
                }

                $id_detalle = $obj_detalles_pago->lastId()["mensaje"];
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

                    $resultado_banco = $obj_bancos_transacciones->registrar_banco_transaccion();
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

                $resultado_puente = $obj_pagos_mensualidad->registrar_pagos_mensualidad();
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
        }
        elseif ($operacion == "consulta_especifica"){ // Traer los otros consultar especificos
            //se guardan el id para buscar
            $id_pago = $_POST["id_pago"];

            //se usan el setter correspondientes
            $obj_pago->set_id_pago($id_pago);

            echo json_encode($obj_pago->consultar_pago());
        }
 
        elseif ($operacion == "modificar") {
                //se guardan las variables a modificar
                $id_pago = $_POST["id_pago"];
                $id_detalle_pago = $_POST["id_detalle_pago"];
                $id_banco_transaccion = $_POST["id_banco_transaccion"];
                $fecha = $_POST["fecha"];  
                $monto = $_POST["monto"];  
                $monto_dolar = $_POST["monto_dolar"]; 
                $estado = $_POST["estado"];
                $tipo_pago = $_POST["tipo_pago"];
                $banco_id = $_POST["banco_id"];  
                $referencia = $_POST["referencia"];
                $observacion = $_POST["observacion"];
                $mensualidad_id = $_POST["mensualidad_id"];
                $monto_mensualidad = $_POST["monto_mensualidad"];
                // ...

                $eliminar_imagen = isset($_POST["eliminar_imagen"]);

                $imagen = $obj_bancos_transacciones->obtener_imagen_actual();
                if (!empty($imagen)) {
                    $ruta_imagen = "recursos/img/" . $imagen;

                    // Eliminar imagen si el usuario lo pidió y el archivo existe
                    if ($eliminar_imagen && file_exists($ruta_imagen) && is_file($ruta_imagen)) {
                        unlink($ruta_imagen);
                        $imagen = '';
                    }
                } else {
                    $ruta_imagen = ''; // No hay imagen previa
                }

                //  Reemplazo de imagen
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                    // Si hay imagen previa, se elimina
                    if (!empty($imagen) && file_exists("recursos/img/" . $imagen) && is_file("recursos/img/" . $imagen)) {
                        unlink("recursos/img/" . $imagen);
                    }
                    
                    // LO MISMO QUE EN REGISTRAR
                    $nombre_original = $_FILES['imagen']['name'];
                    $temporal = $_FILES['imagen']['tmp_name'];
                    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                    $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                    $imagen = $nombre_sanitizado . '_' . time() . '.' . $extension;

                    move_uploaded_file($temporal, "recursos/img/" . $imagen);
                }

                $caja = $obj_pago->consultarCajaActual();

                if (!isset($caja["id_caja_chica"])){
                    echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
                    exit;
                }

                $id_caja = $caja["id_caja_chica"];

                // Modificar Pago
                $obj_pago->set_id_pago($id_pago);
                $obj_pago->set_monto($monto_mensualidad);
                $obj_pago->set_estado($estado);
                $obj_pago->set_observacion($observacion);
                $resultado_modificar_pago = $obj_pago->editar_pago();
                
                if (!$resultado_modificar_pago["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al actualizar pago"]);
                    exit;
                }
            
                // Modificar Detalles del Pago
                $obj_detalles_pago->set_id_detalle_pago($id_detalle_pago);
                $obj_detalles_pago->set_fecha($fecha);
                $obj_detalles_pago->set_monto($monto);
                $obj_detalles_pago->set_monto_dolar($monto_dolar);
                $obj_detalles_pago->set_tipo_pago($tipo_pago);
                $obj_detalles_pago->set_pago_id($id_pago);
                $obj_detalles_pago->set_caja_id($id_caja);

                $resultado_modificar_detalle = $obj_detalles_pago->editar_detalle_pago();
                if (!$resultado_modificar_detalle["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Error al actualizar detalle del pago"]);
                    exit;
                }

                $requiere_transaccion_bancaria = in_array($tipo_pago, ["Transferencia", "Pago Movil"]);

                if ($requiere_transaccion_bancaria) {
                    // Asignar datos obligatorios
                    $obj_bancos_transacciones->set_detalle_pago_id($id_detalle_pago);
                    $obj_bancos_transacciones->set_referencia($referencia ?? 'Sin referencia');

                    $imagen = $imagen ?? "";

                    $obj_bancos_transacciones->set_imagen($imagen);
                    $obj_bancos_transacciones->set_banco_id($banco_id);

                    if (!empty($id_banco_transaccion)) {
                        $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                        $existe_transaccion = $obj_bancos_transacciones->consultar_banco_transaccion();

                        if ($existe_transaccion && isset($existe_transaccion["id_banco_transaccion"])) {
                            $resultado_banco_transaccion = $obj_bancos_transacciones->editar_banco_transaccion();
                        } else {
                            $resultado_banco_transaccion = $obj_bancos_transacciones->registrar_banco_transaccion();
                        }
                    } else {
                        $resultado_banco_transaccion = $obj_bancos_transacciones->registrar_banco_transaccion();
                    }

                    if (!$resultado_banco_transaccion["estatus"]) {
                        echo json_encode(["estatus" => false, "mensaje" => "Error al actualizar transacción bancaria"]);
                        exit;
                    }

                } else {
                    // Es efectivo. Si existía una transacción, la eliminamos
                    if (!empty($id_banco_transaccion)) {
                        $obj_bancos_transacciones->set_id_banco_transaccion($id_banco_transaccion);
                        $resultado_eliminar = $obj_bancos_transacciones->eliminar_banco_transaccion();
                        if (!$resultado_eliminar["estatus"]) {
                            echo json_encode(["estatus" => false, "mensaje" => "Error al eliminar transacción bancaria"]);
                            exit;
                        }
                    }
                }

                // Modificar la relación en la tabla puente pagos_mensualidad
                $obj_pagos_mensualidad->set_detalle_pago_id($id_detalle_pago);
                $obj_pagos_mensualidad->set_mensualidad_id($mensualidad_id);
                $resultado_puente = $obj_pagos_mensualidad->editar_pagos_mensualidad(); // DEBES TENER ESTA FUNCIÓN

                if (!$resultado_puente["estatus"]) {
                    echo json_encode(["estatus" => false, "mensaje" => "Pago modificado, pero error en la relación con mensualidad"]);
                    exit;
                }

                echo json_encode([
                    "estatus" => true,
                    "mensaje" => "Pago modificado con éxito",
                    "id_pago" => $id_pago,
                    "id_detalle" => $id_detalle_pago,
                    "referencia" => $referencia,
                    "imagen" => $imagen
                ]);
                exit;
            }

            elseif ($operacion == "eliminar") {
                //se guardan el id de la variable a eliminar
                $id_pago = $_POST["id_pago"];
                $obj_pago->set_id_pago($id_pago);
        
                echo json_encode($obj_pago->eliminar_pago());

            }elseif ($operacion == "ultimo_id"){
                echo json_encode($obj_pago->lastId());
            }

            exit;//es salida en ingles... No puede faltar
        }
 
        if (isset($_POST["validar"])) {
            $validar = $_POST["validar"]; //Esto es igual pero para las validaciones
            if ($validar == "referencia"){
                $obj_bancos_transacciones->set_referencia($_POST["referencia"]);
                echo  json_encode($obj_bancos_transacciones->verificar_bancos_transacciones());
            }
            
            exit;
        }
        //FIN de AJAX
        require_once "vista/pagos/pagos_propietarios_vista.php";
    }
?>