<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\ayuda\ConstructorDetalles;
use haydee\modelo\TipoGasto;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_TIPO_GASTO, $operacion);
    
    $reglas = TipoGasto::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $contexto = ['exclude_id' => $_POST['id_tipo_gasto'] ?? null];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    // CONSTRUCCIÓN Y VALIDACIÓN DE CONCEPTOS (Renglones)
    if ($operacion === 'registrar_tipo_gasto' || $operacion === 'modificar_tipo_gasto') {    
        $conceptos = ConstructorDetalles::ConstruirConceptosGastos($_POST);

        if (empty($conceptos)) {
            echo json_encode(['estatus' => false, 'mensaje' => 'Debe registrar al menos un concepto para esta partida.']);
            exit;
        }

        $reglasConceptos = TipoGasto::obtenerReglasConceptos();
        $erroresConceptos = [];

        foreach ($conceptos as $index => $concepto) {
            $validadorTemp = new Validador();
            $validadorTemp->validarConjunto($concepto, $reglasConceptos);
            
            if ($validadorTemp->tieneErrores()) {
                $erroresFila = $validadorTemp->obtenerErrores();
                foreach($erroresFila as $campo => $mensajes) {
                    $erroresConceptos["concepto_" . $index . "_" . $campo] = $mensajes; 
                }
            }
        }

        if (!empty($erroresConceptos)) {
            echo json_encode([
                'estatus' => false, 
                'errores' => $erroresConceptos, 
                'mensaje' => 'Existen caracteres inválidos en los conceptos ingresados.'
            ]);
            exit;
        }
    }

    $tipoGasto = new TipoGasto();
    $tipoGasto->set_id_tipo_gasto($_POST['id_tipo_gasto'] ?? null);
    $tipoGasto->set_nombre_tipo_gasto($_POST['nombre_tipo_gasto'] ?? null);
    
    // Inyectamos el arreglo de conceptos si existe
    if (isset($conceptos)) {
        $tipoGasto->setConceptosTemp($conceptos);
    }

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($tipoGasto, Modulo::GESTIONAR_TIPO_GASTO);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consultar':
            $respuesta = $tipoGasto->realizar_consulta('consultar');
            
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
            break;

        case 'consultar_tipo_gasto':
            $respuesta = $tipoGasto->realizar_consulta('consultar_tipo_gasto');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'registrar_tipo_gasto':
            $respuesta = $tipoGasto->realizar_consulta('registrar_tipo_gasto');
            
            if ($respuesta['estatus']) { 
                $tipoGasto->setConceptosTemp(null); // Limpiar arreglo grande antes de auditar
                $auditor->registrarAuditoria(Accion::REGISTRAR); 
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'modificar_tipo_gasto':
            $auditor->capturarDatosAnteriores('consultar_tipo_gasto');
            $respuesta = $tipoGasto->realizar_consulta('modificar_tipo_gasto');
            
            if ($respuesta['estatus']) { 
                $tipoGasto->setConceptosTemp(null);
                $auditor->registrarAuditoria(Accion::MODIFICAR); 
            }
            break;

        case 'eliminar_tipo_gasto':
            $auditor->capturarDatosAnteriores('consultar_tipo_gasto');
            $respuesta = $tipoGasto->realizar_consulta('eliminar_tipo_gasto');
            
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($tipoGasto)) {$tipoGasto->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_TIPO_GASTO);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_TIPO_GASTO);
$btn_nuevo = [
    'target'  => '#modal_tipo_gasto',
    'texto'   => 'Nuevo Tipo de Gasto',
    'tooltip' => 'Registrar Nuevo Tipo de Gasto'
];
$placeholder_buscar = "Buscar tipo de gasto...";

require_once "vista/tipo_gasto/tipo_gasto_vista.php";