<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\ayuda\ConstructorDetalles;
use haydee\modelo\Presupuesto;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PRESUPUESTO, $operacion);

    // =========================================================
    // 1. VALIDACION DE LA CABECERA
    // =========================================================
    $reglasCabecera = Presupuesto::obtenerReglas($operacion);

    if (!empty($reglasCabecera)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglasCabecera);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // =========================================================
    // 2. CONSTRUCCIÓN Y VALIDACION DE DETALLES
    // =========================================================
    if ($operacion === 'registrar_presupuesto' || $operacion === 'modificar_presupuesto') {
        
        // Configuramos el constructor para extraer solo los campos de presupuesto (Sin bancos ni imágenes)
        $configPresupuesto = [
            'campos' => ['nombre', 'monto', 'tipo_gasto_id']
        ];
        
        $detalles = ConstructorDetalles::construirDetalles($_POST, [], $configPresupuesto);

        if (empty($detalles)) {
            echo json_encode(['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un renglón en el presupuesto.']);
            exit;
        }

        $reglasDetalle = Presupuesto::obtenerReglasDetalles();
        $erroresDetalles = [];

        // Validamos cada fila exactamente como en Gastos
        foreach ($detalles as $index => $detalle) {
            $validadorTemp = new Validador();
            $validadorTemp->validarConjunto($detalle, $reglasDetalle);
            
            if ($validadorTemp->tieneErrores()) {
                $erroresFila = $validadorTemp->obtenerErrores();
                foreach($erroresFila as $campo => $mensajes) {
                    $erroresDetalles["detalle_" . $index . "_" . $campo] = $mensajes; 
                }
            }
        }

        if (!empty($erroresDetalles)) {
            echo json_encode([
                'estatus' => false, 
                'errores' => $erroresDetalles, 
                'mensaje' => 'Hay errores en los renglones del presupuesto.'
            ]);
            exit;
        }
    }

    $presupuesto = new Presupuesto();

    // Asignacion de detalles si existen
    if (isset($detalles)) {
        $presupuesto->setDetallesTemp($detalles); 
    }

    // Asignacion masiva de la cabecera
    $presupuesto->set_id_presupuesto($_POST['id_presupuesto'] ?? null);
    $presupuesto->set_fecha($_POST['fecha'] ?? null);
    $presupuesto->set_cuota_reserva($_POST['cuota_reserva'] ?? null);
    $presupuesto->set_observacion($_POST['observacion'] ?? "Sin observación");
    $presupuesto->set_tasa_dolar($_POST['tasa_dolar'] ?? 1); 

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($presupuesto, Modulo::GESTIONAR_PRESUPUESTO);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $presupuesto->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::CONSULTAR);
                }
                break;

            case 'consultar_meses_faltantes':
                $respuesta = $presupuesto->realizar_consulta('consultar_meses_faltantes');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'consultar_tipo_gastos':
                $tipoGasto = new TipoGasto();
                $respuesta = $tipoGasto->realizar_consulta('consultar');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                $tipoGasto->cerrar(); 
                break;

            case 'consultar_presupuesto':
                $respuesta = $presupuesto->realizar_consulta('consultar_presupuesto');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
                break;

            case 'registrar_presupuesto':
                $respuesta = $presupuesto->realizar_consulta('registrar_presupuesto');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::REGISTRAR);
                }
                break;

            case 'modificar_presupuesto':
                // Obtener datos anteriores (consulta plana)
                $auditor->capturarDatosAnteriores('consultar_cabecera_presupuesto');

                //  Ejecutar y auditar
                $respuesta = $presupuesto->realizar_consulta('modificar_presupuesto');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria(Accion::MODIFICAR); 
                }
                break;

            case 'eliminar_presupuesto':
                // Utilizamos la consulta plana
                $auditor->capturarDatosAnteriores('consultar_cabecera_presupuesto');

                $respuesta = $presupuesto->realizar_consulta('eliminar_presupuesto');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria(Accion::ELIMINAR); 
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador presupuesto: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($presupuesto)) { $presupuesto->cerrar(); }
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
            case 'validar_fecha_presupuesto':
                $fecha = $_POST['fecha'] ?? '';
                $presupuestoTemp = new Presupuesto();
                $presupuestoTemp->set_fecha($fecha);
                
                $resp = $presupuestoTemp->realizar_consulta('consultar_presupuestos_mensualidades');
                $existe = $resp['estatus'] && !empty($resp['datos']);
                $respuesta = ['estatus' => $existe];
                break;

            case 'validar_clave_foranea':
                $tabla = $_POST['tabla'] ?? '';
                $campo = $_POST['nombre_clave'] ?? '';
                $valor = $_POST['valor'] ?? '';

                if (empty($tabla) || empty($campo) || empty($valor)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parámetros de Validación'];
                    break;
                }

                $existe = $validadorBD->existe($tabla, $campo, $valor);
                $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'No existe'];
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en Validación AJAX Bancos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(HttpCodigo::OK->value);
    }

    echo json_encode($respuesta);
    exit;
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA (Solo al cargar la página)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_PRESUPUESTO);

    // Instanciamos solo cuando vamos a renderizar el HTML
    $tipoGasto = new TipoGasto();
    $tipos_gasto = $tipoGasto->realizar_consulta('consultar');
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_PRESUPUESTO);
$btn_nuevo = [
    'target'  => '#modal_presupuesto',
    'texto'   => 'Nuevo Presupuesto',
    'tooltip' => 'Registrar Nuevo Presupuesto'
];
$placeholder_buscar = "Buscar presupuesto...";

// Cargar vista
require_once "vista/presupuesto_mensual/presupuesto_vista.php";

