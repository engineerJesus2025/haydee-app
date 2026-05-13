<?php
use haydee\enums\Modulo;
use haydee\enums\Accion;

use haydee\servicios\Sesiones;
use haydee\modelo\Gastos;
use haydee\modelo\Banco;
use haydee\modelo\Proveedores;
use haydee\modelo\SolicitudGasto;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\ayuda\ConstructorDetalles;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::autorizarAcceso(Modulo::GESTIONAR_GASTOS, Accion::CONSULTAR);

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_GASTOS, $operacion);

    // =========================================================
    // 1. VALIDACIÃ“N DE LA CABECERA
    // =========================================================
    $reglasCabecera = Gastos::obtenerReglas($operacion);

    if (!empty($reglasCabecera)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglasCabecera);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? 404 : 400;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // =========================================================
    // 2. CONSTRUCCIÃ“N Y VALIDACIÃ“N DE DETALLES (Renglones)
    // =========================================================
    if ($operacion === 'registrar_gasto' || $operacion === 'modificar_gasto') {
        $esModificacion = ($operacion === 'modificar_gasto');
        
        // Construimos el arreglo usando tu Helper
        $detalles = ConstructorDetalles::ConstruirDetallesGastos($_POST, $_FILES, $esModificacion);
        
        if (empty($detalles)) {
            echo json_encode(['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de gasto.']);
            exit;
        }

        // Validamos CADA fila construida
        $reglasDetalle = Gastos::obtenerReglasDetalles();
        $erroresDetalles = [];

        foreach ($detalles as $index => $detalle) {
            $validadorTemp = new Validador();
            $validadorTemp->validarConjunto($detalle, $reglasDetalle);
            
            if ($validadorTemp->tieneErrores()) {
                $erroresFila = $validadorTemp->obtenerErrores();
                // Adjuntamos el nÃºmero de fila (ej: "Fila 1 - monto") para que el Frontend sepa dÃ³nde marcar el rojo
                foreach($erroresFila as $campo => $mensajes) {
                    $erroresDetalles["detalle_" . $index . "_" . $campo] = $mensajes; 
                }
            }
        }

        // Si alguna fila fallÃ³, rebotamos la peticiÃ³n entera
        if (!empty($erroresDetalles)) {
            echo json_encode([
                'estatus' => false, 
                'errores' => $erroresDetalles, 
                'mensaje' => 'Hay errores en los renglones del gasto. Por favor, revÃ­selos.'
            ]);
            exit;
        }
    }

    // Instancia del modelo principal
    $gastos = new Gastos();

    // Si pasamos por registro/modificaciÃ³n, le pasamos los detalles limpios
    if (isset($detalles)) {
        $gastos->set_detalles($detalles);
    }

    // =========================================================
    // ASIGNACIÃ“N MASIVA DE CAMPOS ESCALARES
    // =========================================================
    $gastos->set_id_gasto($_POST['id_gasto'] ?? null);
    $gastos->set_clasificacion($_POST['clasificacion'] ?? null);
    $gastos->set_descripcion_gasto($_POST['descripcion_gasto'] ?? null);
    $gastos->set_solicitud_id($_POST['solicitud'] ?? null);
    $gastos->set_tipo_gasto_id($_POST['tipo_gasto_id'] ?? null);
    $gastos->set_proveedor_id($_POST['proveedor_id'] ?? null);
    $gastos->set_id_detalle_gasto($_POST['id_detalle_gasto'] ?? null);
    $gastos->set_fecha($_POST['fecha'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($gastos, Modulo::GESTIONAR_GASTOS);

    try {
        switch ($operacion) {
            // =========================================================
            // CONSULTAS
            // =========================================================
            case 'consulta':
                $respuesta = $gastos->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::CONSULTAR);
                }
                break;

            case 'consultar_gasto':
                $respuesta = $gastos->realizar_consulta('consultar_gasto');
                http_response_code($respuesta['estatus'] ? 200 : 404);
                break;

            case 'consultar_detalles':
                $respuesta = $gastos->realizar_consulta('consultar_detalles_por_gasto');
                http_response_code($respuesta['estatus'] ? 200 : 404);
                break;

            case 'consulta_especifica_detalles':
                $respuesta = $gastos->realizar_consulta('consultar_detalle_unico');
                break;

            // =========================================================
            // REGISTRO Y EDICIÃ“N UNIFICADOS
            // =========================================================
            case 'registrar_gasto':
                $respuesta = $gastos->realizar_consulta('registrar_gasto');

                http_response_code($respuesta['estatus'] ? 201 : 400);
                if ($respuesta['estatus']) {
                    // Ocultamos el arreglo masivo al auditor
                    $gastos->set_detalles(null);
                    $auditor->registrarAuditoria(Accion::REGISTRAR);
                }
                break;

            case 'modificar_gasto':
                // Utilizamos la nueva consulta plana para la foto previa
                $auditor->capturarDatosAnteriores('consultar_cabecera_gasto');

                $respuesta = $gastos->realizar_consulta('modificar_gasto');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { 
                    // Ocultamos el arreglo masivo al auditor
                    $gastos->set_detalles(null);
                    $auditor->registrarAuditoria(Accion::MODIFICAR); 
                }
                break;

            // =========================================================
            // ELIMINACIÃ“N
            // =========================================================
            case 'eliminar_gasto':
                // Utilizamos la nueva consulta plana para la foto previa
                $auditor->capturarDatosAnteriores('consultar_cabecera_gasto');

                $respuesta = $gastos->realizar_consulta('eliminar_gasto');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria(Accion::ELIMINAR); 
                }
                break;

            // =========================================================
            // REPORTES Y OTROS (opcional)
            // =========================================================
            case 'listar_gastos_mes':
                $respuesta = $gastos->realizar_consulta('listar_gastos_mes');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                break;

            case 'filtrar_gastos_mes':
                $respuesta = $gastos->realizar_consulta('filtrar_por_mes');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                break;

            case 'totales_metodo_pago':
                $respuesta = $gastos->realizar_consulta('total_por_metodo_pago');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en controlador gastos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($gastos)) { $gastos->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            echo json_encode($respuesta);
            exit;
        }
    }
}

// =========================================================
// VALIDACIONES AJAX
// =========================================================
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];

    $validadorBD = new ValidadorBD();

    try {
        switch ($validar) {
            case 'referencia':
                $referencia = $_POST["referencia"] ?? '';
                $id_gasto = $_POST["id_gasto"] ?? null; 
            
                $gastosTemp = new Gastos();
                $existe = $gastosTemp->verificarReferenciaDisponible($referencia, $id_gasto);

                $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'La referencia ya estÃ¡ registrada en otro gasto' : 'Disponible'];
                break;

            case 'validar_clave_foranea':
                $tabla = $_POST['tabla'] ?? '';
                $campo = $_POST['nombre_clave'] ?? '';
                $valor = $_POST['valor'] ?? '';

                if (empty($tabla) || empty($campo) || empty($valor)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parÃ¡metros de Validación'];
                    break;
                }


                $existe = $validadorBD->existe($tabla, $campo, $valor);
                $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'No existe'];
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en Validación AJAX Bancos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(200);
    }

    echo json_encode($respuesta);
    exit;
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_GASTOS);

    // Instanciamos solo cuando vamos a renderizar el HTML
    $banco = new Banco();
    $proveedor = new Proveedores();
    $solicitudGasto = new SolicitudGasto();
    $tipoGasto = new TipoGasto();

    $proveedores = $proveedor->realizar_consulta('consultar');
    $bancos = $banco->realizar_consulta('consultar');
    $solicitudes_gasto = $solicitudGasto->realizar_consulta('consultar');
    $tipos_gasto = $tipoGasto->realizar_consulta('consultar');
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_GASTOS);
$btn_nuevo = [
    'target'  => '#modal_gastos',
    'texto'   => 'Nuevo Gasto',
    'tooltip' => 'Registrar Nuevo Gasto'
];
$placeholder_buscar = "Buscar gasto...";

require_once "vista/gastos/gastos_vista.php";
?>
