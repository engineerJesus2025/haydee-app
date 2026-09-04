<?php
use Dompdf\Dompdf;
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\GestorPDF;
use haydee\modelo\Habitantes;
use haydee\modelo\Gastos;
use haydee\modelo\Mensualidad;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\Reportes; 
use haydee\ayuda\ManejadorVistas;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

$habitantesModel = new Habitantes();
$mensualidadModel = new Mensualidad();
$gastosModel = new Gastos();

// Manejo de peticiones AJAX (POST)
if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    // (Solo para operaciones del servicio Reportes)
    $reglas = Reportes::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    
    // INSTANCIAR SERVICIO DE REPORTES
    $reportesServicio = new Reportes();
    
    // ASIGNACIÓN MASIVA MEDIANTE SETTERS
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
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        // ---- Operaciones Delegadas al Servicio Reportes ----
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

        // ---- Utilitarias Simples ----
        case 'consultar_meses_mensualidad':
            $respuesta = $mensualidadModel->realizar_consulta('consultar_meses_mensualidad');
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($reportesServicio)) {$reportesServicio->cerrar();}
    if (isset($mensualidadModel)) {$mensualidadModel->cerrar();}
    if (isset($gastosModel)) {$gastosModel->cerrar();}
    if (isset($habitantesModel)) {$habitantesModel->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

// Validaciones AJAX
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
                $salida = $respuesta['estatus'] ? ['estatus' => $respuesta['existe']] : ['estatus' => false, 'mensaje' => $respuesta['mensaje']];
            } else {
                throw new HaydeeException('Validación no soportada o faltan parámetros.', HttpCodigo::BAD_REQUEST->value);
            }
            break;
            
        default:
            throw new HaydeeException('Validación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }
    
    http_response_code(HttpCodigo::OK->value);
    echo json_encode($salida);
    exit;
}

// vistas y generación de PDF
$accion = $_GET['accion'] ?? 'reportes_pdf'; 

switch ($accion) {
    case 'reportes_pdf':
        Bitacora::registrar(Accion::CONSULTAR, Modulo::GESTIONAR_REPORTES);
        require_once "vista/reportes/reportes_pdf/reportes_pdf_vista.php";
        break;

    case 'solvencia':
        $habitantesModel->set_id_habitante($_POST['select_reporte'] ?? 0);
        $res = $habitantesModel->realizar_consulta('consultar_habitante');

        if (!$res['estatus']) {
            ManejadorVistas::renderizarErrorReporte(
                'El habitante seleccionado no fue encontrado o no posee registros de pagos suficientes para calcular un estado de cuenta.',
                'Datos Insuficientes'
            );
        }

        $datos = [
            'registro_propietario' => $res['datos'],
            'meses' => ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            'fecha_fin' => (new DateTime())->modify("+1 month")
        ];

        GestorPDF::generar("vista/reportes/reportes_pdf/pdf/reporte_solvencia_pdf.php", $datos, "solvencia_" . $res['datos']['nombre']);
        break;

    case 'residencia':
        $id_habitante = $_POST['select_reporte'] ?? 0;
        $habitantesModel->set_id_habitante($id_habitante);
        $registro_propietario = $habitantesModel->realizar_consulta('consultar_habitante');

        if (!$registro_propietario['estatus']) {
            ManejadorVistas::renderizarErrorReporte('No se pudo encontrar al habitante en la base de datos para generar la constancia.');
        }

        $registro_propietario = $registro_propietario['datos'];
        
        $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        ob_start();
        require_once "vista/reportes/reportes_pdf/pdf/reporte_residencia_pdf.php";
        $html = ob_get_clean();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->render();

        $detallesReporte = ['tipo_reporte' => 'Constancia de Residencia', 'habitante' => $registro_propietario["nombre"] . " " . $registro_propietario["apellido"]];
        Bitacora::registrar(Accion::DESCARGAR, Modulo::GESTIONAR_REPORTES, null, null, $detallesReporte);
        $dompdf->stream("constancia_residencia_" . $registro_propietario["nombre"] . "_" . $registro_propietario["apellido"]);
        break;

    case 'cuadro_pagos':
        $limite = explode("-", $_POST["select_reporte"] ?? '');
        $reportesServicio = new Reportes();
        $reportesServicio->set_mes_limite($limite[0] ?? '');
        $reportesServicio->set_anio_limite($limite[1] ?? '');

        $res = $reportesServicio->realizar_consulta('cuadro_pagos');

        // Si la consulta falló por error de SQL o validación
        if (!$res['estatus']) {
            ManejadorVistas::renderizarErrorReporte($res['mensaje'] ?? 'Ocurrió un error al consultar la base de datos para el cuadro de pagos.');
        }
        
        if (empty($res['datos']['cuerpo'])) {
            ManejadorVistas::renderizarErrorReporte(
                'No hay deudas ni pagos registrados hasta la fecha seleccionada para construir el cuadro. Seleccione un rango diferente.', 
                'Cuadro en Blanco'
            );
        }

        $mensualidadModel->set_mes($limite[0]);
        $mensualidadModel->set_anio($limite[1]);
        $tasa = $mensualidadModel->realizar_consulta('consultar_tasa_dolar_mensualidades');

        GestorPDF::generar("vista/reportes/reportes_pdf/pdf/cuadro_pagos_pdf.php", [
            'cabecera_tabla' => $res['datos']['cabecera'],
            'cuerpo_tabla' => $res['datos']['cuerpo'],
            'total_mensual' => $res['datos']['totales'],
            'tasa_dolar' => $tasa['datos'] ?? ['tasa_dolar' => 1],
            'mes_limite' => $limite[0],
            'anio_limite' => $limite[1]
        ], "Cuadro_Pagos", 'landscape');
        break;

    case 'gastos_mensual':
        require_once "vista/reportes/reportes_pdf/reporte_gastos_mensual_vista.php";
        break;

    case 'generar_reporte_gastos_mensual':
        $formato = $_POST['formato'] ?? 'pdf'; // Recuperamos la lógica del formato
        
        $reportesServicio = new Reportes();
        $reportesServicio->set_mes($_POST['mes'] ?? '');
        $reportesServicio->set_anio($_POST['anio'] ?? '');
        
        $res = $reportesServicio->realizar_consulta('generar_data_reporte_gastos_mensual'); 
        if (!$res['estatus']) {
            ManejadorVistas::renderizarErrorReporte($res['mensaje'], "Cuadro en Blanco");
        }

        $aptosModel = new Apartamento();
        $aptos = $aptosModel->realizar_consulta('contar_activos');

        $datosVista = [
            'gastos_fijos' => $res['datos']['gastos_fijos'],
            'total_aptos' => $aptos['datos'] ?? 0,
            'total_gas_bs' => $res['datos']['gasto_gas']['monto'],
            'gastos_variables' => $res['datos']['gastos_variables'],
            'mes' => $_POST['mes'],
            'anio' => $_POST['anio'],
            'tasa_dolar' => (float)($_POST['tasa_dolar'] ?? 0)
        ];

        if ($formato === 'excel') {
            // Si el botón presionado fue el de Excel, extraemos las variables y cargamos el script de PhpSpreadsheet
            extract($datosVista);
            require_once "vista/reportes/reportes_excel/reporte_gastos_mensual_excel.php";
        } else {
            // Si fue PDF, usamos el Gestor
            GestorPDF::generar("vista/reportes/reportes_pdf/pdf/reporte_gastos_mensual_pdf.php", $datosVista, "relacion_gastos_" . $_POST['mes']);
        }
        break;

    case 'recibo_pago':
        $reportesServicio = new Reportes();
        $reportesServicio->set_id_pago($_POST['select_reporte'] ?? 0);
        $res = $reportesServicio->realizar_consulta('consultar_recibo_pago');
        
        if (!$res['estatus']) {
            ManejadorVistas::renderizarErrorReporte(
                'La base de datos no devolvió registros válidos. Esto ocurre si la transacción no ha sido aprobada o si se eliminó el mes de la deuda.',
                'Recibo no disponible'
            );
        }

        GestorPDF::generar("vista/reportes/reportes_pdf/pdf/recibo_pago_pdf.php", [
            'detalles_recibo' => $res['datos'],
            'meses_nombres' => ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"]
        ], "recibo_pago_" . $res['datos']['id_pago']);
        break;

    case 'reportes_estadisticos':
        Bitacora::registrar(Accion::CONSULTAR, Modulo::GESTIONAR_REPORTES);
        require_once "vista/reportes/reportes_estadisticos/reportes_estadisticos_vista.php";
        break;

    case 'ingreso_egreso':
        require_once "vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingreso_egreso_vista.php";
        break;

    case 'generar_reporte_ingresos_egresos':
        $datosVista = [
            'selecion' => $_POST["mostrar_datos_input"] ?? '',
            'barra' => $_POST["barra"] ?? '',
            'fecha' => $_POST["fecha_grafico_input"] ?? '',
            'total_pagos' => $_POST["total_pagos_input"] ?? 0,
            'total_gastos' => $_POST["total_gastos_input"] ?? 0,
            'gastos_efectivo' => $_POST["gastos_efectivo_input"] ?? 0,
            'gastos_transferencia' => $_POST["gastos_transferencia_input"] ?? 0,
            'gastos_pago_movil' => $_POST["gastos_pago_movil_input"] ?? 0,
            'pagos_efectivo' => $_POST["pagos_efectivo_input"] ?? 0,
            'pagos_transferencia' => $_POST["pagos_transferencia_input"] ?? 0,
            'pagos_pago_movil' => $_POST["pagos_pago_movil_input"] ?? 0,
            'fecha_pagos' => $_POST["fecha_pagos_input"] ?? '',
            'fecha_gastos' => $_POST["fecha_gastos_input"] ?? ''
        ];

        GestorPDF::generar("vista/reportes/reportes_estadisticos/reporte_ingresos_egresos/reporte_ingresos_egreso_pdf.php", $datosVista, "reporte_ingreso_egreso");
        break;

    case 'habitantes':
        require_once "vista/reportes/reportes_estadisticos/reporte_habitantes/reporte_habitantes_vista.php";
        break;

    default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
        header("Location: ?pagina=reportes&accion=reportes_pdf");
        break;
}

