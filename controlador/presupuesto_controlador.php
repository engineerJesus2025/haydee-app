<?php
use haydee\servicios\Sesiones;
use haydee\modelo\Presupuesto;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\ayuda\ConstructorDetalles;
use haydee\servicios\GestorAuditoria;

Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_PRESUPUESTO, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(GESTIONAR_PRESUPUESTO, $operacion);

    // =========================================================
    // 1. VALIDACIÓN DE LA CABECERA
    // =========================================================
    $reglasCabecera = Presupuesto::obtenerReglas($operacion);

    if (!empty($reglasCabecera)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglasCabecera);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // =========================================================
    // 2. CONSTRUCCIÓN Y VALIDACIÓN DE DETALLES
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

    // Asignación de detalles si existen
    if (isset($detalles)) {
        $presupuesto->setDetallesTemp($detalles); 
    }

    // Asignación masiva de la cabecera
    $presupuesto->set_id_presupuesto($_POST['id_presupuesto'] ?? null);
    $presupuesto->set_fecha($_POST['fecha'] ?? null);
    $presupuesto->set_cuota_reserva($_POST['cuota_reserva'] ?? null);
    $presupuesto->set_observacion($_POST['observacion'] ?? "Sin observación");
    $presupuesto->set_tasa_dolar($_POST['tasa_dolar'] ?? 1); 

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($presupuesto, GESTIONAR_PRESUPUESTO);

    try {
        switch ($operacion) {
            case 'consultar':
                $respuesta = $presupuesto->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_meses_faltantes':
                $respuesta = $presupuesto->realizar_consulta('consultar_meses_faltantes');
                break;

            case 'consultar_tipo_gastos':
                $tipoGasto = new TipoGasto();
                $respuesta = $tipoGasto->realizar_consulta('consultar');
                $tipoGasto->cerrar(); 
                break;

            case 'consultar_presupuesto':
                $respuesta = $presupuesto->realizar_consulta('consultar_presupuesto');
                break;

            case 'registrar_presupuesto':
                $respuesta = $presupuesto->realizar_consulta('registrar_presupuesto');
                if ($respuesta['estatus']) {

                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar_presupuesto':
                // Obtener datos anteriores (consulta plana)
                $auditor->capturarDatosAnteriores('consultar_cabecera_presupuesto');

                //  Ejecutar y auditar
                $respuesta = $presupuesto->realizar_consulta('modificar_presupuesto');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar_presupuesto':
                // Utilizamos la consulta plana
                $auditor->capturarDatosAnteriores('consultar_cabecera_presupuesto');

                $respuesta = $presupuesto->realizar_consulta('eliminar_presupuesto');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador presupuesto: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($presupuesto)) { $presupuesto->cerrar(); }
            Bitacora::cerrarConexionBitacora();

            header('Content-Type: application/json');
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

    try {
        if ($validar === 'validar_fecha_presupuesto') {
            $fecha = $_POST['fecha'] ?? '';
            $presupuestoTemp = new Presupuesto();
            $presupuestoTemp->set_fecha($fecha);
            
            $resp = $presupuestoTemp->realizar_consulta('consultar_presupuestos_mensualidades');
            $existe = $resp['estatus'] && !empty($resp['datos']);
            
            echo json_encode(['estatus' => $existe]);
            exit;

        } elseif ($validar === 'validar_clave_foranea') {
            if (isset($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor'])) {
                $validadorBD = new ValidadorBD();
                $existe = $validadorBD->existe($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor']);
                echo json_encode(['estatus' => $existe, 'mensaje' => 'OK']);
            } else {
                echo json_encode(['estatus' => false, 'mensaje' => 'Faltan parámetros']);
            }
            exit;
        } else {
            echo json_encode(['estatus' => false, 'mensaje' => 'Validación no implementada']);
            exit;
        }
    } catch (Exception $e) {
        error_log("Error en validación AJAX Presupuesto: " . $e->getMessage());
        echo json_encode(['estatus' => false, 'mensaje' => 'Error interno']);
        exit;
    }
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA (Solo al cargar la página)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_PRESUPUESTO);

    // Instanciamos solo cuando vamos a renderizar el HTML
    $tipoGasto = new TipoGasto();
    $tipos_gasto = $tipoGasto->realizar_consulta('consultar');
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_PRESUPUESTO);
$btn_nuevo = [
    'target'  => '#modal_presupuesto',
    'texto'   => 'Nuevo Presupuesto',
    'tooltip' => 'Registrar Nuevo Presupuesto'
];
$placeholder_buscar = "Buscar presupuesto...";

// Cargar vista
require_once "vista/presupuesto_mensual/presupuesto_vista.php";