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
$caja_chica_obj = new Caja_Chica();
$tipo_gasto_obj = new Tipo_Gasto();
$bancos_transacciones_obj = new Bancos_Transacciones();

// -------------------- 2. MANEJO DE OPERACIONES (AJAX) --------------------
if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    // ================== CONSULTA GENERAL ==================
    if ($operacion == "consulta") {
        echo json_encode($gastos_obj->consultar());
        exit;
    }
    
    // ================== REGISTRAR NUEVO GASTO ==================
    elseif ($operacion == "registrar") {
        $gastos_obj->set_tipo($_POST["tipo"]);
        $gastos_obj->set_descripcion_gasto($_POST["descripcion_gasto"]);
        $gastos_obj->set_tipo_gasto_id($_POST["tipo_gasto"]);
        $gastos_obj->set_solicitud_id($_POST["solicitud"]);
        $gastos_obj->set_proveedor_id($_POST["proveedor"]);
        $gastos_obj->registrar();

        $caja = $gastos_obj->consultarCajaActual();
        if (!isset($caja["id_caja_chica"])) {
            echo json_encode(["estatus" => false, "mensaje" => "No se encontró caja activa"]);
            exit;
        }
        $id_caja = $caja["id_caja_chica"];
        $gasto_id = $gastos_obj->lastId()["mensaje"];

        $fechas_detalles = $_POST["fecha_detalle"];
        foreach ($fechas_detalles as $indice => $fecha_detalle) {
            $detalles_gastos_obj->set_fecha($fecha_detalle);
            $detalles_gastos_obj->set_monto($_POST["monto"][$indice]);
            $detalles_gastos_obj->set_monto_dolar(0);
            $detalles_gastos_obj->set_metodo_pago($_POST["metodo_pago"][$indice]);
            $detalles_gastos_obj->set_descripcion_detalle_gasto($_POST["descripcion_detalle"][$indice]);
            $detalles_gastos_obj->set_gasto_id($gasto_id);
            $detalles_gastos_obj->set_caja_id($id_caja);
            $detalles_gastos_obj->registrar_detalle_gasto();
            
            $id_detalle = $detalles_gastos_obj->lastId()["mensaje"];

            $imagen_detalle = '';
            if (isset($_FILES['imagen']['name'][$indice]) && $_FILES['imagen']['error'][$indice] === 0) {
                $nombre_original = $_FILES['imagen']['name'][$indice];
                $temporal = $_FILES['imagen']['tmp_name'][$indice];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
                $imagen_detalle = $nombre_sanitizado . '_' . time() . '_' . rand(100, 999) . '.' . $extension;
                move_uploaded_file($temporal, "recursos/img/gastos/" . $imagen_detalle);
            }

            if ((!empty($_POST["referencia"][$indice]) || !empty($_POST["banco"][$indice])) && !empty($imagen_detalle)) {
                $bancos_transacciones_obj->set_referencia($_POST["referencia"][$indice]);
                $bancos_transacciones_obj->set_imagen($imagen_detalle);
                $bancos_transacciones_obj->set_banco_id($_POST["banco"][$indice]);
                $bancos_transacciones_obj->set_detalle_gasto_id($id_detalle);
                $bancos_transacciones_obj->registrar_banco_transaccion_gasto();
            }
        }
        
        $gastos_obj->set_id_gasto($gasto_id);
        $gasto_completo = $gastos_obj->consultar_gasto();
        echo json_encode(["estatus" => true, "mensaje" => "Gasto registrado correctamente", "gasto" => $gasto_completo]);
        exit;
    } 
    
    // ================== CONSULTA ESPECÍFICA (PARA LLENAR FORMULARIO DE EDITAR) ==================
    elseif ($operacion == "consulta_especifica") {
        $gastos_obj->set_id_gasto($_POST["id_gasto"]);
        $gasto_principal = $gastos_obj->consultar_gasto();
        if (!$gasto_principal) {
            echo json_encode(["estatus" => false, "mensaje" => "No se encontró el gasto solicitado."]);
            exit;
        }

        $detalles_gastos_obj->set_gasto_id($_POST["id_gasto"]);
        $lista_detalles = $detalles_gastos_obj->consultar_detalles_por_gasto();

        echo json_encode(["gasto" => $gasto_principal, "detalles" => $lista_detalles]);
        exit;
    } 
    
   // ================== MODIFICAR GASTO EXISTENTE (VERSIÓN FINAL) ==================
elseif ($operacion == "modificar") {
    $id_gasto = $_POST["id_gasto"];
    $gastos_obj->set_id_gasto($id_gasto);
    $gastos_obj->set_tipo($_POST["tipo"]);
    $gastos_obj->set_descripcion_gasto($_POST["descripcion_gasto"]);
    $gastos_obj->set_tipo_gasto_id($_POST["tipo_gasto"]);
    $gastos_obj->set_solicitud_id($_POST["solicitud"]);
    $gastos_obj->set_proveedor_id($_POST["proveedor"]);
    $gastos_obj->editar_gasto();

    // Eliminar detalles antiguos para luego reinsertarlos (tu estrategia actual)
    $detalles_gastos_obj->set_gasto_id($id_gasto);
    $detalles_gastos_obj->eliminar_detalles_por_gasto();

    $caja = $gastos_obj->consultarCajaActual();
    $id_caja = $caja["id_caja_chica"];

    $fechas_detalles = $_POST["fecha_detalle"];
    foreach ($fechas_detalles as $indice => $fecha_detalle) {
        $detalles_gastos_obj->set_fecha($fecha_detalle);
        $detalles_gastos_obj->set_monto($_POST["monto"][$indice]);
        $detalles_gastos_obj->set_monto_dolar(0);
        $detalles_gastos_obj->set_metodo_pago($_POST["metodo_pago"][$indice]);
        $detalles_gastos_obj->set_descripcion_detalle_gasto($_POST["descripcion_detalle"][$indice]);
        $detalles_gastos_obj->set_gasto_id($id_gasto);
        $detalles_gastos_obj->set_caja_id($id_caja);
        $detalles_gastos_obj->registrar_detalle_gasto();
        
        $id_detalle = $detalles_gastos_obj->lastId()["mensaje"];

        // ✅ LÓGICA CRÍTICA PARA LA IMAGEN
        $imagen_detalle = '';
        
        // Prioridad 1: ¿Se subió una imagen NUEVA?
        if (isset($_FILES['imagen']['name'][$indice]) && $_FILES['imagen']['error'][$indice] === 0) {
            $nombre_original = $_FILES['imagen']['name'][$indice];
            $temporal = $_FILES['imagen']['tmp_name'][$indice];
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_sanitizado = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", pathinfo($nombre_original, PATHINFO_FILENAME));
            $imagen_detalle = $nombre_sanitizado . '_' . time() . '_' . rand(100, 999) . '.' . $extension;
            move_uploaded_file($temporal, "recursos/img/gastos/" . $imagen_detalle);
        } 
        // Prioridad 2: Si no, ¿llegó el nombre de una imagen EXISTENTE?
        elseif (!empty($_POST['imagen_existente'][$indice])) {
            $imagen_detalle = $_POST['imagen_existente'][$indice];
        }

        // Registrar la transacción si hay datos para ello
        if (!empty($_POST["referencia"][$indice]) || !empty($_POST["banco"][$indice]) || !empty($imagen_detalle)) {
            $bancos_transacciones_obj->set_referencia($_POST["referencia"][$indice]);
            $bancos_transacciones_obj->set_imagen($imagen_detalle);
            $bancos_transacciones_obj->set_banco_id($_POST["banco"][$indice]);
            $bancos_transacciones_obj->set_detalle_gasto_id($id_detalle);
            $bancos_transacciones_obj->registrar_banco_transaccion_gasto();
        }
    }

    $gastos_obj->set_id_gasto($id_gasto);
    $gasto_completo = $gastos_obj->consultar_gasto();
    echo json_encode(["estatus" => true, "mensaje" => "Gasto modificado correctamente", "gasto" => $gasto_completo]);
    exit;
}

    // ================== ELIMINAR GASTO ==================
    elseif ($operacion == "eliminar") {
        $gastos_obj->set_id_gasto($_POST["id_gasto"]);
        echo json_encode($gastos_obj->eliminar_gasto());
        exit;
    }

    // ================== CONSULTAR DETALLES DE UN GASTO ==================
    elseif ($operacion == "consultar_detalles") {
        $detalles_gastos_obj->set_gasto_id($_POST["id_gasto"]);
        echo json_encode($detalles_gastos_obj->consultar_detalles_por_gasto());
        exit;
    } 
    
    // ================== CONSULTAR UN DETALLE ESPECÍFICO ==================
    elseif ($operacion == "consulta_especifica_detalles") {
        $detalles_gastos_obj->set_id_detalle_gasto($_POST["id_detalle_gasto"]);
        echo json_encode($detalles_gastos_obj->consultar_detalle_gasto());
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
        echo json_encode($gastos_obj->lastId());
        exit;
    }

} // Fin del if (isset($_POST["operacion"]))

// -------------------- 3. CARGA DE DATOS PARA LA VISTA --------------------
// Estos datos se cargan para los <select> en el formulario cuando la página se carga por primera vez
$proveedores = $proveedor_obj->consultar();
$bancos = $banco_obj->consultar();
$solicitudes_gasto = $solicitud_gasto_obj->consultar();
$cajas_chica = $caja_chica_obj->consultar();
$tipos_gasto = $tipo_gasto_obj->consultar();

// -------------------- 4. INCLUSIÓN DE LA VISTA --------------------
require_once "vista/gastos/gastos_vista.php";
?>