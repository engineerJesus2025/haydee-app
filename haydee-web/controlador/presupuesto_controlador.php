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
use haydee\servicios\GestorTasa;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PRESUPUESTO, $operacion);

    // VALIDACION DE LA CABECERA
    $reglasCabecera = Presupuesto::obtenerReglas($operacion);

    if (!empty($reglasCabecera)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglasCabecera);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    if ($operacion === 'registrar_presupuesto' || $operacion === 'modificar_presupuesto') {
        
        $configPresupuesto = [
            'campos' => ['concepto_id', 'monto']
        ];
        
        $detalles = ConstructorDetalles::construirDetalles($_POST, [], $configPresupuesto);

        if (empty($detalles)) {
            echo json_encode(['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un renglón en el presupuesto.']);
            exit;
        }

        $reglasDetalle = Presupuesto::obtenerReglasDetalles();
        $erroresDetalles = [];

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

    if (isset($detalles)) {
        $presupuesto->setDetallesTemp($detalles); 
    }

    // Asignacion masiva de la cabecera
    $presupuesto->set_id_presupuesto($_POST['id_presupuesto'] ?? null);
    $presupuesto->set_fecha($_POST['fecha'] ?? null);
    $presupuesto->set_cuota_reserva($_POST['cuota_reserva'] ?? null);
    $presupuesto->set_observacion($_POST['observacion'] ?? "Sin observación");

    $tasaDolar = GestorTasa::obtener();
    $presupuesto->set_tasa_dolar($tasaDolar);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($presupuesto, Modulo::GESTIONAR_PRESUPUESTO);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consultar':
            $respuesta = $presupuesto->realizar_consulta('consultar');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'consultar_meses_faltantes':
            $respuesta = $presupuesto->realizar_consulta('consultar_meses_faltantes');
            break;

        case 'consultar_tipo_gastos':
            $tipoGasto = new TipoGasto();
            $respuesta = $tipoGasto->realizar_consulta('consultar_conceptos');
            
            $tipoGasto->cerrar(); 
            break;

        case 'consultar_presupuesto':
            $respuesta = $presupuesto->realizar_consulta('consultar_presupuesto');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'registrar_presupuesto':
            $respuesta = $presupuesto->realizar_consulta('registrar_presupuesto');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'modificar_presupuesto':
            // Obtener datos anteriores (consulta plana)
            $auditor->capturarDatosAnteriores('consultar_cabecera_presupuesto');

            //  Ejecutar y auditar
            $respuesta = $presupuesto->realizar_consulta('modificar_presupuesto');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::MODIFICAR); 
            }
            break;

        case 'eliminar_presupuesto':
            // Utilizamos la consulta plana
            $auditor->capturarDatosAnteriores('consultar_cabecera_presupuesto');

            $respuesta = $presupuesto->realizar_consulta('eliminar_presupuesto');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::ELIMINAR); 
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($presupuesto)) {$presupuesto->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

// VALIDACIONES AJAX
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $validadorBD = new ValidadorBD();

    switch ($validar) {
        case 'validar_fecha_presupuesto':
            $fecha = $_POST['fecha'] ?? '';
            $presupuestoTemp = new Presupuesto();
            $presupuestoTemp->set_fecha($fecha);
            
            $resp = $presupuestoTemp->realizar_consulta('consultar_presupuestos_mensualidades');
            $presupuestoTemp->cerrar();
            
            $existe = $resp['estatus'] && !empty($resp['datos']);
            $respuesta = ['estatus' => $existe];
            break;

        case 'validar_clave_foranea':
            $tabla = $_POST['tabla'] ?? '';
            $campo = $_POST['nombre_clave'] ?? '';
            $valor = $_POST['valor'] ?? '';

            if (empty($tabla) || empty($campo) || empty($valor)) {
                throw new HaydeeException('Faltan parámetros de validación', HttpCodigo::BAD_REQUEST->value);
            }

            $existe = $validadorBD->existe($tabla, $campo, $valor);
            $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'No existe'];
            break;

        default:
            throw new HaydeeException('Validación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }

    http_response_code(HttpCodigo::OK->value);
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
    $tipos_gasto = $tipoGasto->realizar_consulta('consultar_conceptos');
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

