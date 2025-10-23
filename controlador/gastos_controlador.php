<?php
// -------------------- 1. INCLUDES Y OBJETOS --------------------
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
$tipo_gasto_obj = new Tipo_Gasto();
$bancos_transacciones_obj = new Bancos_Transacciones();

// -------------------- 2. MANEJO DE OPERACIONES (AJAX) --------------------
if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    if ($operacion == "consulta") {
        $gastos_obj->registrar_bitacora(CONSULTAR, GESTIONAR_GASTOS, "TODOS LOS GASTOS");
        echo json_encode($gastos_obj->realizar_consulta("consultar"));
        exit;
    }
    
    elseif ($operacion == "registrar") {
        $gastos_obj->set_tipo($_POST["tipo"]);
        $gastos_obj->set_descripcion_gasto($_POST["descripcion_gasto"]);
        $gastos_obj->set_tipo_gasto_id($_POST["tipo_gasto"]);
        $gastos_obj->set_solicitud_id($_POST["solicitud"]);
        $gastos_obj->set_proveedor_id($_POST["proveedor"]);
        $resultado = $gastos_obj->realizar_consulta("registrar");

        if ($resultado["estatus"]) {
            $gastos_obj->registrar_bitacora(REGISTRAR, GESTIONAR_GASTOS, $gastos_obj->get_descripcion_gasto());
        }

        $gasto_id_respuesta = $gastos_obj->realizar_consulta("lastId");
        $gasto_id = $gasto_id_respuesta["mensaje"];

        $banco_index = 0;
        $referencia_index = 0;
        $imagen_bancaria_index = 0;
        $fechas_detalles = $_POST["fecha_detalle"];

        foreach ($fechas_detalles as $indice => $fecha_detalle) {
            $detalles_gastos_obj->set_fecha($fecha_detalle);
            $detalles_gastos_obj->set_monto($_POST["monto"][$indice]);
            $detalles_gastos_obj->set_metodo_pago($_POST["metodo_pago"][$indice]);
            $detalles_gastos_obj->set_descripcion_detalle_gasto($_POST["descripcion_detalle"][$indice]);
            $detalles_gastos_obj->set_gasto_id($gasto_id);
            // CORRECCIÓN: Se usa la acción 'registrar' del modelo de detalles
            $detalles_gastos_obj->realizar_consulta("registrar");
            
            $id_detalle_respuesta = $detalles_gastos_obj->realizar_consulta("lastId");
            $id_detalle = $id_detalle_respuesta["mensaje"];

            $metodo_actual = $_POST['metodo_pago'][$indice];
            if ($metodo_actual === 'Pago Movil' || $metodo_actual === 'Transferencia') {
                $banco_id = $_POST['banco'][$banco_index++] ?? null;
                $referencia = $_POST['referencia'][$referencia_index++] ?? null;
                $imagen_detalle = '';

                if (isset($_FILES['imagen']['name'][$imagen_bancaria_index]) && $_FILES['imagen']['error'][$imagen_bancaria_index] === 0) {
                    $nombre_original = $_FILES['imagen']['name'][$imagen_bancaria_index];
                    $temporal = $_FILES['imagen']['tmp_name'][$imagen_bancaria_index];
                    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                    $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                    $nombre_unico = $nombre_sanitizado . '_' . time() . '_' . rand(100, 999) . '.' . $extension;
                    $ruta_destino = "recursos/img/gastos/" . $nombre_unico;
                    if (move_uploaded_file($temporal, $ruta_destino)) {
                        $imagen_detalle = $nombre_unico;
                    }
                }
                $imagen_bancaria_index++;

                if (!empty($banco_id)) {
                    // Este modelo (Bancos_Transacciones) no lo hemos refactorizado, así que se queda con la llamada directa
                    $transaccion_especifica = new Bancos_Transacciones(); 
                    $transaccion_especifica->set_referencia($referencia);
                    $transaccion_especifica->set_imagen($imagen_detalle);
                    $transaccion_especifica->set_banco_id($banco_id);
                    $transaccion_especifica->set_detalle_gasto_id($id_detalle);
                    $transaccion_especifica->registrar_banco_transaccion_gasto();
                }
            }
        }
        
        $gastos_obj->set_id_gasto($gasto_id);
        $gasto_completo = $gastos_obj->realizar_consulta("consultar_gasto");
        echo json_encode(["estatus" => true, "mensaje" => "Gasto registrado correctamente", "gasto" => $gasto_completo]);
        exit;
    } 
    
    elseif ($operacion == "consulta_especifica") {
        $gastos_obj->set_id_gasto($_POST["id_gasto"]);
        $gasto_principal = $gastos_obj->realizar_consulta("consultar_gasto");
        if (!$gasto_principal) {
            echo json_encode(["estatus" => false, "mensaje" => "No se encontró el gasto solicitado."]);
            exit;
        }

        $detalles_gastos_obj->set_gasto_id($_POST["id_gasto"]);
        // CORRECCIÓN: Se usa la acción 'consultar_por_gasto' del modelo de detalles
        $lista_detalles = $detalles_gastos_obj->realizar_consulta("consultar_por_gasto");

        echo json_encode(["gasto" => $gasto_principal, "detalles" => $lista_detalles]);
        exit;
    } 
    
    elseif ($operacion == "modificar") {
        $id_gasto = $_POST["id_gasto"];
        $gastos_obj->set_id_gasto($id_gasto);
        $gastos_obj->set_tipo($_POST["tipo"]);
        $gastos_obj->set_descripcion_gasto($_POST["descripcion_gasto"]);
        $gastos_obj->set_tipo_gasto_id($_POST["tipo_gasto"]);
        $gastos_obj->set_solicitud_id($_POST["solicitud"]);
        $gastos_obj->set_proveedor_id($_POST["proveedor"]);
        $resultado = $gastos_obj->realizar_consulta("editar_gasto");

        if ($resultado["estatus"]) {
            $gastos_obj->registrar_bitacora(MODIFICAR, GESTIONAR_GASTOS, $gastos_obj->get_descripcion_gasto());
        }

        $detalles_gastos_obj->set_gasto_id($id_gasto);
        // CORRECCIÓN: Se usa la acción 'eliminar_por_gasto' del modelo de detalles
        $detalles_gastos_obj->realizar_consulta("eliminar_por_gasto");

        $banco_index = 0;
        $referencia_index = 0;
        $imagen_bancaria_index = 0;
        $imagen_existente_index = 0;
        $fechas_detalles = $_POST["fecha_detalle"];

        foreach ($fechas_detalles as $indice => $fecha_detalle) {
        // 1. Registrar el detalle
        $detalles_gastos_obj->set_fecha($fecha_detalle);
        $detalles_gastos_obj->set_monto($_POST["monto"][$indice]);
        $detalles_gastos_obj->set_metodo_pago($_POST["metodo_pago"][$indice]);
        $detalles_gastos_obj->set_descripcion_detalle_gasto($_POST["descripcion_detalle"][$indice]);
        $detalles_gastos_obj->set_gasto_id($id_gasto);
        $detalles_gastos_obj->realizar_consulta("registrar");
        $id_detalle = $detalles_gastos_obj->realizar_consulta("lastId")["mensaje"];

        // 2. Si el método de pago es bancario, manejar la transacción
        $metodo_actual = $_POST['metodo_pago'][$indice];
        if ($metodo_actual === 'Pago Movil' || $metodo_actual === 'Transferencia') {
            
            $banco_id = $_POST['banco'][$banco_index++] ?? null;
            $referencia = $_POST['referencia'][$referencia_index++] ?? null;

            // Procesamos la imagen (nueva o existente) usando su propio índice
            $imagen_detalle = '';
            if (isset($_FILES['imagen']['name'][$imagen_bancaria_index]) && $_FILES['imagen']['error'][$imagen_bancaria_index] === 0) {
                // ... (código para mover el archivo nuevo) ...
                $nombre_original = $_FILES['imagen']['name'][$imagen_bancaria_index];
                $temporal = $_FILES['imagen']['tmp_name'][$imagen_bancaria_index];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $nombre_unico = $nombre_sanitizado . '_' . time() . '_' . rand(100, 999) . '.' . $extension;
                $ruta_destino = "recursos/img/gastos/" . $nombre_unico;
                if (move_uploaded_file($temporal, $ruta_destino)) {
                    $imagen_detalle = $nombre_unico;
                }
            } elseif (isset($_POST['imagen_existente'][$imagen_existente_index])) {
                $imagen_detalle = $_POST['imagen_existente'][$imagen_existente_index++];
            }
            $imagen_bancaria_index++;

            // Guardamos la transacción
            if (!empty($banco_id)) {
                $transaccion_especifica = new Bancos_Transacciones(); 
                $transaccion_especifica->set_referencia($referencia);
                $transaccion_especifica->set_imagen($imagen_detalle);
                $transaccion_especifica->set_banco_id($banco_id);
                $transaccion_especifica->set_detalle_gasto_id($id_detalle);
                $transaccion_especifica->registrar_banco_transaccion_gasto();
            }
        }
    }
        $gastos_obj->set_id_gasto($id_gasto);
        $gasto_completo = $gastos_obj->realizar_consulta("consultar_gasto");
        echo json_encode(["estatus" => true, "mensaje" => "Gasto modificado correctamente", "gasto" => $gasto_completo]);
        exit;
    }

    elseif ($operacion == "eliminar") {
        $gastos_obj->set_id_gasto($_POST["id_gasto"]);
        $gasto_alterado = $gastos_obj->realizar_consulta('consultar_gasto');
        $resultado = $gastos_obj->realizar_consulta("eliminar_gasto");
        if ($resultado["estatus"]) {
            $gastos_obj->registrar_bitacora(ELIMINAR, GESTIONAR_GASTOS,  $gasto_alterado["descripcion_gasto"]);
        }
        echo json_encode($resultado);
        exit;
    }

    elseif ($operacion == "consultar_detalles") {
        $detalles_gastos_obj->set_gasto_id($_POST["id_gasto"]);
        // CORRECCIÓN: Se usa la acción 'consultar_por_gasto'
        echo json_encode($detalles_gastos_obj->realizar_consulta("consultar_por_gasto"));
        exit;
    } 
    
    elseif ($operacion == "consulta_especifica_detalles") {
        $detalles_gastos_obj->set_id_detalle_gasto($_POST["id_detalle_gasto"]);
        // CORRECCIÓN: Se usa 'realizar_consulta' en lugar de la llamada directa
        echo json_encode($detalles_gastos_obj->realizar_consulta("consultar_detalle_gasto"));
        exit;
    }

    // ================== OPERACIONES DE FILTRADO Y TOTALES ==================
    elseif ($operacion == "listar_gastos_mes") {
        echo json_encode($gastos_obj->listar_gastos_mes());
        exit;
    } 

    elseif ($operacion == "filtrar_gastos_mes") {
        $fecha = $_POST["fecha"];
        $gastos_obj->set_fecha($fecha);
        echo json_encode($gastos_obj->filtrar_por_mes());
        exit;
    } 
    
    elseif ($operacion == "totales_metodo_pago") {
        $fecha = $_POST["fecha"];
        $gastos_obj->set_fecha($fecha);
        echo json_encode($gastos_obj->total_por_metodo_pago());
        exit;
    } 
    
    // ================== OBTENER ÚLTIMO ID ==================
    elseif ($operacion == "ultimo_id") {
        echo json_encode($gastos_obj->realizar_consulta("lastId"));
        exit;
    }

} // Fin del if (isset($_POST["operacion"]))

// -------------------- 3. CARGA DE DATOS PARA LA VISTA --------------------
// Estos datos se cargan para los <select> en el formulario cuando la página se carga por primera vez
$proveedores = $proveedor_obj->realizar_consulta("consultar");
$bancos = $banco_obj->realizar_consulta('consultar');
$solicitudes_gasto = $solicitud_gasto_obj->realizar_consulta("consultar");
$tipos_gasto = $tipo_gasto_obj->realizar_consulta("consultar");

// -------------------- 4. INCLUSIÓN DE LA VISTA --------------------
require_once "vista/gastos/gastos_vista.php";
?>