<?php
use haydee\ayuda\Sesiones;
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
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_GASTOS, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    // =========================================================
    // 1. VALIDACIÓN DE LA CABECERA
    // =========================================================
    $reglasCabecera = Gastos::obtenerReglas($operacion);

    if (!empty($reglasCabecera)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglasCabecera);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // =========================================================
    // 2. CONSTRUCCIÓN Y VALIDACIÓN DE DETALLES (Renglones)
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
                // Adjuntamos el número de fila (ej: "Fila 1 - monto") para que el Frontend sepa dónde marcar el rojo
                foreach($erroresFila as $campo => $mensajes) {
                    $erroresDetalles["detalle_" . $index . "_" . $campo] = $mensajes; 
                }
            }
        }

        // Si alguna fila falló, rebotamos la petición entera
        if (!empty($erroresDetalles)) {
            echo json_encode([
                'estatus' => false, 
                'errores' => $erroresDetalles, 
                'mensaje' => 'Hay errores en los renglones del gasto. Por favor, revíselos.'
            ]);
            exit;
        }
    }

    // Instancia del modelo principal
    $gastos = new Gastos();

    // Si pasamos por registro/modificación, le pasamos los detalles limpios
    if (isset($detalles)) {
        $gastos->set_detalles($detalles);
    }

    // =========================================================
    // ASIGNACIÓN MASIVA DE CAMPOS ESCALARES
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
    $auditor = new GestorAuditoria($gastos, GESTIONAR_GASTOS);

    try {
        switch ($operacion) {
            // =========================================================
            // CONSULTAS
            // =========================================================
            case 'consulta':
                $respuesta = $gastos->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_gasto':
                $respuesta = $gastos->realizar_consulta('consultar_gasto');
                break;

            case 'consultar_detalles':
                $respuesta = $gastos->realizar_consulta('consultar_detalles_por_gasto');
                break;

            case 'consulta_especifica_detalles':
                $respuesta = $gastos->realizar_consulta('consultar_detalle_unico');
                break;

            // =========================================================
            // REGISTRO Y EDICIÓN UNIFICADOS
            // =========================================================
            case 'registrar_gasto':
                $respuesta = $gastos->realizar_consulta('registrar_gasto');
                if ($respuesta['estatus']) {
                    // Ocultamos el arreglo masivo al auditor
                    $gastos->set_detalles(null);
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar_gasto':
                // Utilizamos la nueva consulta plana para la foto previa
                $auditor->capturarDatosAnteriores('consultar_cabecera_gasto');

                $respuesta = $gastos->realizar_consulta('modificar_gasto');
                if ($respuesta['estatus']) { 
                    // Ocultamos el arreglo masivo al auditor
                    $gastos->set_detalles(null);
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            // =========================================================
            // ELIMINACIÓN
            // =========================================================
            case 'eliminar_gasto':
                // Utilizamos la nueva consulta plana para la foto previa
                $auditor->capturarDatosAnteriores('consultar_cabecera_gasto');

                $respuesta = $gastos->realizar_consulta('eliminar_gasto');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
                }
                break;

            // =========================================================
            // REPORTES Y OTROS (opcional)
            // =========================================================
            case 'listar_gastos_mes':
                $respuesta = $gastos->realizar_consulta('listar_gastos_mes');
                break;

            case 'filtrar_gastos_mes':
                $respuesta = $gastos->realizar_consulta('filtrar_por_mes');
                break;

            case 'totales_metodo_pago':
                $respuesta = $gastos->realizar_consulta('total_por_metodo_pago');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador gastos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($gastos)) { $gastos->cerrar(); }
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
        if ($validar === 'referencia') {
            $referencia = $_POST["referencia"] ?? '';
            $id_gasto = $_POST["id_gasto"] ?? null; 
            
            $gastosTemp = new Gastos();
            $existe = $gastosTemp->verificarReferenciaDisponible($referencia, $id_gasto);
            
            echo json_encode(['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'La referencia ya está registrada en otro gasto' : 'Disponible']);
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
        error_log("Error en validación AJAX Gastos: " . $e->getMessage());
        echo json_encode(['estatus' => false, 'mensaje' => 'Error interno del servidor']);
        exit;
    }
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_GASTOS);

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

require_once "vista/gastos/gastos_vista.php";
?>