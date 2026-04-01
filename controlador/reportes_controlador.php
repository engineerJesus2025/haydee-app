<?php
use haydee\ayuda\Sesiones;
use haydee\ayuda\Validador;
use haydee\modelo\Habitantes;
use haydee\modelo\Gastos;
use haydee\modelo\Mensualidad;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\servicios\Reportes; 
use Dompdf\Dompdf;

// ====================================================================
// Seguridad y sesión
// ====================================================================
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_REPORTES, CONSULTAR);

// ====================================================================
// Instancias de Modelos Utilitarios
// (Solo para llenar combos o verificaciones atómicas)
// ====================================================================
$habitantesModel = new Habitantes();
$mensualidadModel = new Mensualidad();
$gastosModel = new Gastos();

// ====================================================================
// Manejo de peticiones AJAX (POST)
// ====================================================================
if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    // 1. OBTENER REGLAS Y VALIDAR (Solo para operaciones del servicio Reportes)
    $reglas = Reportes::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    
    // 2. INSTANCIAR SERVICIO DE REPORTES
    $reportesServicio = new Reportes();
    
    // 3. ASIGNACIÓN MASIVA MEDIANTE SETTERS
    $reportesServicio->set_balance($_POST['balance'] ?? 'todos');
    $reportesServicio->set_metodo_pago($_POST['metodo_pago'] ?? 'todos');
    $reportesServicio->set_tipo_gasto($_POST['tipo_gasto'] ?? 'todos');
    $reportesServicio->set_fecha_inicio($_POST['fecha_inicio'] ?? $_POST['fecha_inicio_habitantes'] ?? null);
    $reportesServicio->set_fecha_fin($_POST['fecha_fin'] ?? $_POST['fecha_fin_habitantes'] ?? null);
    
    $reportesServicio->set_mes($_POST['mes'] ?? null);
    $reportesServicio->set_anio($_POST['anio'] ?? null);
    
    $reportesServicio->set_id_habitante($_POST['select_reporte'] ?? null);
    $reportesServicio->set_rango_edades($_POST['rango_edades'] ?? 'todos');
    $reportesServicio->set_edad_minima($_POST['edad_minima'] ?? null);
    $reportesServicio->set_edad_maxima($_POST['edad_maxima'] ?? null);
    $reportesServicio->set_tipo_residente($_POST['tipo_residente'] ?? 'todos');
    $reportesServicio->set_servicios($_POST['servicios'] ?? []);
    $reportesServicio->set_filtro_tiempo($_POST['filtro_tiempo'] ?? 'todo');
    
    try {
        switch ($operacion) {
            // ---- Operaciones Complejas (Delegadas al Servicio Reportes) ----
            case 'reporte_ingresos_egresos_completo':
                $respuesta = $reportesServicio->realizar_consulta('reporte_ingresos_egresos_completo');
                break;

            case 'obtener_datos_reporte_mensual':
                $respuesta = $reportesServicio->realizar_consulta('obtener_datos_reporte_mensual');
                break;

            case 'listar_meses_con_gastos':
                $respuesta = $reportesServicio->realizar_consulta('listar_meses_con_gastos');
                break;

            case 'consultar_personas_solvencia':
                $respuesta = $reportesServicio->realizar_consulta('consultar_personas_solvencia');
                break;

            case 'consultar_personas_residencia':
                $respuesta = $reportesServicio->realizar_consulta('consultar_propietarios');
                break;

            case 'consultar_habitantes':
                $respuesta = $reportesServicio->realizar_consulta('obtener_datos_habitantes');
                break;

            // ---- Operaciones Utilitarias Simples ----
            case 'consultar_meses_mensualidad':
                $respuesta = $mensualidadModel->realizar_consulta('consultar_meses_mensualidad');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador reportes (POST): " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones
            $reportesServicio->cerrar();
            $mensualidadModel->cerrar();
            $gastosModel->cerrar();
            $habitantesModel->cerrar();

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// ====================================================================
// Validaciones AJAX Puras
// ====================================================================
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];

    switch ($validar) {
        case 'validar_clave_foranea':
            $tabla = $_POST["tabla"] ?? '';
            $campo = $_POST["nombre_clave"] ?? '';
            $valor = $_POST["valor"] ?? '';

            if ($tabla === 'habitantes' && $campo === 'id_habitante' && !empty($valor)) {
                $habitantesModel->set_id_habitante($valor);
                $respuesta = $habitantesModel->realizar_consulta('existe_habitante');
                echo json_encode($respuesta['estatus'] ? ['estatus' => $respuesta['existe']] : ['estatus' => false, 'mensaje' => $respuesta['mensaje']]);
            } else {
                echo json_encode(['estatus' => false, 'mensaje' => 'Validación no soportada']);
            }
            break;
        default:
            echo json_encode(['estatus' => false, 'mensaje' => 'Validación no reconocida']);
    }
    exit;
}

// ====================================================================
// Manejo de acciones GET (vistas y generación de PDF)
// ====================================================================
$accion = $_GET['accion'] ?? 'reportes_pdf'; 

switch ($accion) {
    case 'reportes_pdf':
        Bitacora::registrar(CONSULTAR, GESTIONAR_REPORTES);
        require_once "vista/reportes/reportes_pdf/reportes_pdf_vista.php";
        break;

    case 'solvencia':
        $id_habitante = $_POST['select_reporte'] ?? 0;
        $habitantesModel->set_id_habitante($id_habitante);
        // Aquí usamos el Modelo porque solo necesitamos los datos básicos de UN habitante
        $registro_propietario = $habitantesModel->realizar_consulta('consultar_habitante');
        if (!$registro_propietario['estatus']) die('Habitante no encontrado');
        $registro_propietario = $registro_propietario['datos'];

        $fecha = new DateTime();
        $fecha->modify("+1 month");
        $mes_fin = $fecha->format("n");
        $anio_fin = $fecha->format("Y");
        $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/reporte_solvencia_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->render();

        $detallesReporte = ['tipo_reporte' => 'Constancia de Solvencia', 'propietario' => $registro_propietario["nombre"] . " " . $registro_propietario["apellido"], 'cedula' => $registro_propietario["cedula"] ?? 'N/A'];
        Bitacora::registrar(DESCARGAR, GESTIONAR_REPORTES, null, null, $detallesReporte);
        $dompdf->stream("solvencia_" . $registro_propietario["nombre"] . "_" . $registro_propietario["apellido"]);
        break;

    case 'residencia':
        $id_habitante = $_POST['select_reporte'] ?? 0;
        $habitantesModel->set_id_habitante($id_habitante);
        $registro_propietario = $habitantesModel->realizar_consulta('consultar_habitante');
        if (!$registro_propietario['estatus']) die('Habitante no encontrado');
        $registro_propietario = $registro_propietario['datos'];
        
        $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/reporte_residencia_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->render();

        $detallesReporte = ['tipo_reporte' => 'Constancia de Residencia', 'habitante' => $registro_propietario["nombre"] . " " . $registro_propietario["apellido"]];
        Bitacora::registrar(DESCARGAR, GESTIONAR_REPORTES, null, null, $detallesReporte);
        $dompdf->stream("constancia_residencia_" . $registro_propietario["nombre"] . "_" . $registro_propietario["apellido"]);
        break;

    case 'cuadro_pagos':
        $limite = explode("-", $_POST["select_reporte"] ?? '');
        $mes_limite = $limite[0] ?? '';
        $anio_limite = $limite[1] ?? '';
        if (!$mes_limite || !$anio_limite) die('Parámetros inválidos');

        $reportesServicio = new Reportes();
        $reportesServicio->set_mes_limite($mes_limite);
        $reportesServicio->set_anio_limite($anio_limite);
        $resultadoCuadro = $reportesServicio->realizar_consulta('cuadro_pagos');
        
        if (!$resultadoCuadro['estatus']) die('Error al obtener datos para el cuadro de pagos.');
        
        $cabecera_tabla = $resultadoCuadro['datos']['cabecera'];
        $cuerpo_tabla = $resultadoCuadro['datos']['cuerpo'];
        $total_mensual = $resultadoCuadro['datos']['totales'];

        // Obtener tasa usando el modelo base de mensualidad
        $mensualidadModel->set_mes($mes_limite);
        $mensualidadModel->set_anio($anio_limite);
        $tasa_resp = $mensualidadModel->realizar_consulta('consultar_tasa_dolar_mensualidades');
        $tasa_dolar = $tasa_resp['estatus'] ? $tasa_resp['datos'] : ['tasa_dolar' => 1, 'mes' => $mes_limite, 'anio' => $anio_limite];
        
        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/cuadro_pagos_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $meses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
        $nombreMes = $meses[(int)$mes_limite];

        $detallesReporte = ['tipo_reporte' => 'Cuadro de Pagos', 'periodo' => $nombreMes . " del " . $anio_limite];
        Bitacora::registrar(DESCARGAR, GESTIONAR_REPORTES, null, null, $detallesReporte);
        $dompdf->stream("Cuadro_Pagos_" . str_pad($mes_limite, 2, '0', STR_PAD_LEFT) . "-" . $anio_limite . ".pdf");
        break;

    case 'gastos_mensual':
        require_once "vista/reportes/reportes_pdf/reporte_gastos_mensual_vista.php";
        break;

    case 'generar_reporte_gastos_mensual':
        $mes = $_POST['mes'] ?? '';
        $anio = $_POST['anio'] ?? '';
        $tasa_dolar = (float)($_POST['tasa_dolar'] ?? 0);
        $formato = $_POST['formato'] ?? 'pdf'; // Capturamos el formato elegido (por defecto PDF)

        if (!$mes || !$anio || $tasa_dolar == 0) die("Parámetros incompletos o tasa no válida.");

        $reportesServicio = new Reportes();
        $reportesServicio->set_mes($mes);
        $reportesServicio->set_anio($anio);
        $respuesta = $reportesServicio->realizar_consulta('obtener_datos_reporte_mensual');

        if (!$respuesta['estatus']) die("Error al obtener datos: " . $respuesta['mensaje']);

        $detalles = $respuesta['datos'];
        $gastos_fijos = [];
        $gastos_variables = [];
        $total_gas_bs = 0;

        foreach ($detalles as $row) {
            $clasificacion = $row['clasificacion'] ?? '';
            $concepto = $row['concepto'] ?? '';
            $monto = (float)($row['monto'] ?? 0);

            if (stripos($concepto, 'GAS LARA') !== false) {
                $total_gas_bs += $monto;
            } else {
                if ($clasificacion === 'Fijo') $gastos_fijos[] = ['descripcion_gasto' => $concepto, 'monto' => $monto];
                else $gastos_variables[] = ['descripcion_gasto' => $concepto, 'monto' => $monto];
            }
        }

        $apartamentoModel = new Apartamento();
        $resp_aptos = $apartamentoModel->realizar_consulta('contar_activos');
        $total_aptos = $resp_aptos['estatus'] ? $resp_aptos['datos'] : 0;

        $datos_reporte = [
            'gastos_fijos' => $gastos_fijos,
            'gastos_variables' => $gastos_variables,
            'gasto_gas' => ['monto' => $total_gas_bs],
            'total_aptos' => $total_aptos
        ];

        // --- AQUI SEPARAMOS LA LÓGICA SEGÚN EL FORMATO ---
        if ($formato === 'excel') {
            // Requerimos el archivo que armará nuestro Excel
            require_once "vista/reportes/reportes_excel/reporte_gastos_mensual_excel.php";
        } else {
            // Lógica original del PDF
            ob_start();
            require_once "vista/reportes/reportes_pdf/pdf/reporte_gastos_mensual_pdf.php";
            $html = ob_get_clean();

            $dompdf = new Dompdf(['enable_remote' => true]);
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream("relacion_gastos_".$mes."_".$anio.".pdf");
        }
        break;

    case 'recibo_pago':
        $id_pago = $_POST['select_reporte'] ?? 0;
        
        $reportesServicio = new Reportes();
        $reportesServicio->set_id_pago($id_pago);
        $detalles_recibo = $reportesServicio->realizar_consulta('consultar_recibo_pago');
        
        if (!$detalles_recibo['estatus']) die("No se encontraron datos para generar el reporte.");
        $detalles_recibo = $detalles_recibo['datos'];

        date_default_timezone_set('America/Caracas');
        $meses_nombres = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
        $fecha_pago = new DateTime($detalles_recibo['fecha_pago']);

        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/recibo_pago_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->render();

        $detallesReporte = ['tipo_reporte' => 'Recibo de Pago Individual', 'recibo_nro' => $detalles_recibo['id_pago'], 'propietario' => $detalles_recibo['nombre'] . " " . $detalles_recibo['apellido'], 'monto_total' => $detalles_recibo['total']];
        Bitacora::registrar(DESCARGAR, GESTIONAR_REPORTES, null, null, $detallesReporte);

        $dompdf->stream("recibo_pago_". $detalles_recibo['nombre'] ."_" . $detalles_recibo['apellido'] . "_" . $fecha_pago->format('Y-m-d') . ".pdf");
        break;

    case 'reportes_estadisticos':
        Bitacora::registrar(CONSULTAR, GESTIONAR_REPORTES);
        require_once "vista/reportes/reportes_estadisticos/reportes_estadisticos_vista.php";
        break;

    case 'ingreso_egreso':
        require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingreso_egreso_vista.php";
        break;

    case 'generar_reporte_ingresos_egresos':
        // Estas variables se mandan directo a la vista del PDF
        $selecion = $_POST["mostrar_datos_input"] ?? '';
        $barra = $_POST["barra"] ?? '';
        $fecha = $_POST["fecha_grafico_input"] ?? '';
        $total_pagos = $_POST["total_pagos_input"] ?? 0;
        $total_gastos = $_POST["total_gastos_input"] ?? 0;
        $gastos_efectivo = $_POST["gastos_efectivo_input"] ?? 0;
        $gastos_transferencia = $_POST["gastos_transferencia_input"] ?? 0;
        $gastos_pago_movil = $_POST["gastos_pago_movil_input"] ?? 0;
        $pagos_efectivo = $_POST["pagos_efectivo_input"] ?? 0;
        $pagos_transferencia = $_POST["pagos_transferencia_input"] ?? 0;
        $pagos_pago_movil = $_POST["pagos_pago_movil_input"] ?? 0;
        $fecha_pagos = $_POST["fecha_pagos_input"] ?? '';
        $fecha_gastos = $_POST["fecha_gastos_input"] ?? '';

        ob_start();
        require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingresos_egreso_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("reporte_ingreso_egreso");
        break;

    case 'habitantes':
        require_once "vista/reportes/reportes_estadisticos/reporte_habitantes/reporte_habitantes_vista.php";
        break;

    default:
        header("Location: ?pagina=reportes&accion=reportes_pdf");
        break;
}