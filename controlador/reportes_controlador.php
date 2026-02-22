<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Habitantes;
use haydee\modelo\Gastos;
use haydee\modelo\Mensualidad;
use haydee\modelo\Pagos;
use haydee\modelo\Bitacora;

use Dompdf\Dompdf;

// ====================================================================
// Seguridad y sesión
// ====================================================================
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_REPORTES, CONSULTAR);

// ====================================================================
// Instancias de modelos (se crean una vez para reutilizar)
// ====================================================================
$gastos = new Gastos();
$habitantes = new Habitantes();
$mensualidad = new Mensualidad();
$pagos = new Pagos();

// ====================================================================
// Manejo de peticiones AJAX (POST)
// ====================================================================
if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            case 'consultar_ingresos_egresos':
                $gastos->set_filtros_reporte([
                    'balance' => $_POST['balance'] ?? 'todos',
                    'metodo_pago' => $_POST['metodo_pago'] ?? 'todos',
                    'tipo_gasto' => $_POST['tipo_gasto'] ?? 'todos',
                    'filtro' => $_POST['filtro'] ?? '',
                    'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                    'fecha_fin' => $_POST['fecha_fin'] ?? ''
                ]);
                $respuesta = $gastos->realizar_consulta('consultar_ingresos_egresos');
                break;

            case 'consultar_estadisticas_ingresos_egresos':
                // mismo array
                $gastos->set_filtros_reporte([
                    'balance' => $_POST['balance'] ?? 'todos',
                    'metodo_pago' => $_POST['metodo_pago'] ?? 'todos',
                    'tipo_gasto' => $_POST['tipo_gasto'] ?? 'todos',
                    'filtro' => $_POST['filtro'] ?? '',
                    'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                    'fecha_fin' => $_POST['fecha_fin'] ?? ''
                ]);
                $respuesta = $gastos->realizar_consulta('estadisticas_ingresos_egresos');
                break;

            case 'listar_meses_con_gastos':
                $respuesta = $gastos->realizar_consulta('listar_meses_con_gastos');
                break;

            case 'obtener_datos_reporte_mensual':
                $gastos->set_filtros_reporte([
                    'mes' => $_POST['mes'] ?? 0,
                    'anio' => $_POST['anio'] ?? 0
                ]);
                $respuesta = $gastos->realizar_consulta('obtener_datos_reporte_mensual');
                break;

            case 'consultar_personas_solvencia':
                $respuesta = $habitantes->realizar_consulta('consultar_personas_solvencia');
                break;

            case 'consultar_personas_residencia':
                $respuesta = $habitantes->realizar_consulta('consultar_propietarios');
                break;

            case 'consultar_meses_mensualidad':
                $respuesta = $mensualidad->realizar_consulta('consultar_meses_mensualidad');
                break;

            case 'consultar_habitantes':
                $habitantes->set_filtros_reporte([
                    'rango_edades' => $_POST['rango_edades'] ?? 'todos',
                    'edad_minima' => $_POST['edad_minima'] ?? null,
                    'edad_maxima' => $_POST['edad_maxima'] ?? null,
                    'tipo_residente' => $_POST['tipo_residente'] ?? 'todos',
                    'servicios' => $_POST['servicios'] ?? []
                ]);
                $respuesta = $habitantes->realizar_consulta('obtener_datos_habitantes');
                // $respuesta ya tiene la estructura estándar {estatus, datos, mensaje}
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador reportes (POST): " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    }

    echo json_encode($respuesta);
    exit;
}

// ====================================================================
// Validaciones AJAX
// ====================================================================
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];

    switch ($validar) {
        case 'validar_clave_foranea':
            $tabla = $_POST["tabla"] ?? '';
            $campo = $_POST["nombre_clave"] ?? '';
            $valor = $_POST["valor"] ?? '';

            // Solo soportamos validación de habitantes por ahora
            if ($tabla === 'habitantes' && $campo === 'id_habitante' && !empty($valor)) {
                $habitantes->set_id_habitante($valor);
                $respuesta = $habitantes->realizar_consulta('existe_habitante');
                if ($respuesta['estatus']) {
                    echo json_encode(['estatus' => $respuesta['existe']]);
                } else {
                    echo json_encode(['estatus' => false, 'mensaje' => $respuesta['mensaje']]);
                }
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
$accion = $_GET['accion'] ?? 'reportes_pdf'; // Por defecto, la vista principal de reportes PDF

switch ($accion) {
    case 'reportes_pdf':
        Bitacora::registrar(CONSULTAR, GESTIONAR_REPORTES, 'Acceso a reportes PDF');
        require_once "vista/reportes/reportes_pdf/reportes_pdf_vista.php";
        break;

    case 'solvencia':
        $id_habitante = $_POST['select_reporte'] ?? 0;
        $habitantes->set_id_habitante($id_habitante);
        $registro_propietario = $habitantes->realizar_consulta('consultar_habitante');
        if (!$registro_propietario['estatus']) {
            die('Habitante no encontrado');
        }
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
        $dompdf->stream("solvencia_" . $registro_propietario["nombre"] . "_" . $registro_propietario["apellido"]);
        break;

    case 'residencia':
        $id_habitante = $_POST['select_reporte'] ?? 0;
        $habitantes->set_id_habitante($id_habitante);
        $registro_propietario = $habitantes->realizar_consulta('consultar_habitante');
        if (!$registro_propietario['estatus']) {
            die('Habitante no encontrado');
        }
        $registro_propietario = $registro_propietario['datos'];
        $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/reporte_residencia_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("constancia_residencia_" . $registro_propietario["nombre"] . "_" . $registro_propietario["apellido"]);
        break;

    case 'cuadro_pagos':
        $limite = explode("-", $_POST["select_reporte"] ?? '');
        $mes_limite = $limite[0] ?? '';
        $anio_limite = $limite[1] ?? '';
        
        if (!$mes_limite || !$anio_limite) {
            die('Parámetros inválidos');
        }

        // 1. Delegar todo el procesamiento pesado al modelo
        $resultadoCuadro = $mensualidad->generarEstructuraCuadroPagos($mes_limite, $anio_limite);
        if (!$resultadoCuadro['estatus']) {
            die('Error al obtener datos para el cuadro de pagos.');
        }

        $cabecera_tabla = $resultadoCuadro['datos']['cabecera'];
        $cuerpo_tabla = $resultadoCuadro['datos']['cuerpo'];
        $total_mensual = $resultadoCuadro['datos']['totales'];

        // 2. Obtener la tasa de cambio
        $mensualidad->set_mes($mes_limite);
        $mensualidad->set_anio($anio_limite);
        $tasa_resp = $mensualidad->realizar_consulta('consultar_tasa_dolar_mensualidades');
        $tasa_dolar = $tasa_resp['estatus'] ? $tasa_resp['datos'] : ['tasa_dolar' => 1, 'mes' => $mes_limite, 'anio' => $anio_limite];

        // 3. Renderizar PDF
        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/cuadro_pagos_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); // Sugerencia: Los cuadros de pago suelen requerir hoja horizontal
        $dompdf->render();
        $dompdf->stream("Cuadro_Pagos_" . str_pad($mes_limite, 2, '0', STR_PAD_LEFT) . "-" . $anio_limite . ".pdf");
        break;

    case 'gastos_mensual':
        require_once "vista/reportes/reportes_pdf/reporte_gastos_mensual_vista.php";
        break;

    case 'generar_reporte_gastos_mensual':
        $mes = $_POST['mes'] ?? '';
        $anio = $_POST['anio'] ?? '';
        $tasa_dolar = (float)($_POST['tasa_dolar'] ?? 0);

        if (!$mes || !$anio || $tasa_dolar == 0) {
            die("Parámetros incompletos o tasa no válida.");
        }

        // Obtener datos de gastos
        $gastos->set_filtros_reporte(['mes' => $mes, 'anio' => $anio]);
        $respuesta = $gastos->realizar_consulta('obtener_datos_reporte_mensual');

        if (!$respuesta['estatus']) {
            die("Error al obtener datos: " . $respuesta['mensaje']);
        }

        $detalles = $respuesta['datos'];

        // Procesar detalles
        $gastos_fijos = [];
        $gastos_variables = [];
        $total_gas_bs = 0;

        foreach ($detalles as $row) {
            $clasificacion = $row['clasificacion'] ?? '';
            $concepto = $row['concepto'] ?? '';
            $monto = (float)($row['monto'] ?? 0);

            // Identificar gas por nombre (ajusta según tu nomenclatura)
            if (stripos($concepto, 'GAS LARA') !== false) {
                $total_gas_bs += $monto;
            } else {
                if ($clasificacion === 'Fijo') {
                    $gastos_fijos[] = ['descripcion_gasto' => $concepto, 'monto' => $monto];
                } else {
                    $gastos_variables[] = ['descripcion_gasto' => $concepto, 'monto' => $monto];
                }
            }
        }

        // Obtener total de apartamentos activos
        $apartamentoModel = new \haydee\modelo\Apartamento();
        $resp_aptos = $apartamentoModel->realizar_consulta('contar_activos');
        $total_aptos = $resp_aptos['estatus'] ? $resp_aptos['datos'] : 0;

        // Construir array para la vista
        $datos_reporte = [
            'gastos_fijos' => $gastos_fijos,
            'gastos_variables' => $gastos_variables,
            'gasto_gas' => ['monto' => $total_gas_bs],
            'total_aptos' => $total_aptos
        ];

        // Pasar a la vista (además de $tasa_dolar, $anio, $mes)
        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/reporte_gastos_mensual_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("relacion_gastos_".$mes."_".$anio.".pdf");
        break;

    case 'recibo_pago':
        $id_pago = $_POST['select_reporte'] ?? 0;
        $pagos->set_id_pago($id_pago);
        $detalles_recibo = $pagos->realizar_consulta('consultarReciboPago');
        if (!$detalles_recibo['estatus']) {
            die("No se encontraron datos para generar el reporte.");
        }
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
        $dompdf->stream("recibo_pago_". $detalles_recibo['nombre'] ."_" . $detalles_recibo['apellido'] . "_" . $fecha_pago->format('Y-m-d') . ".pdf");
        break;

    case 'reportes_estadisticos':
        Bitacora::registrar(CONSULTAR, GESTIONAR_REPORTES, 'Acceso a reportes estadísticos');
        require_once "vista/reportes/reportes_estadisticos/reportes_estadisticos_vista.php";
        break;

    case 'ingreso_egreso':
        require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingreso_egreso_vista.php";
        break;

    case 'generar_reporte_ingresos_egresos':
        // Recoger datos del POST (vienen del formulario)
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
        // Si no hay acción válida, redirigir a reportes_pdf por defecto
        header("Location: ?pagina=reportes_controlador.php&accion=reportes_pdf");
        break;
}