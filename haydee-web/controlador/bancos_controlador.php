<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Banco;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_BANCOS, $operacion);
    
    $reglas = Banco::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $contexto = [];
        
        if (strpos($operacion, 'modificar') !== false) {
            $contexto['exclude_id'] = $_POST['id_banco'] ?? null;
        }

        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos de formulario inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $banco = new Banco();
    $banco->set_id_banco($_POST['id_banco'] ?? null);
    $banco->set_nombre_banco($_POST['nombre_banco'] ?? null);
    $banco->set_codigo($_POST['codigo'] ?? null);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($banco, Modulo::GESTIONAR_BANCOS);

    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consulta':
            $respuesta = $banco->realizar_consulta('consultar');
            if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
            break;

        case 'registrar_banco':
            $respuesta = $banco->realizar_consulta('registrar_banco');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'consultar_banco':
            $respuesta = $banco->realizar_consulta('consultar_banco');
            break;

        case 'modificar_banco':
            $auditor->capturarDatosAnteriores('consultar_banco');
            $respuesta = $banco->realizar_consulta('modificar_banco');
            if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::MODIFICAR); 
            break;

        case 'eliminar_banco':
            $auditor->capturarDatosAnteriores('consultar_banco');
            $respuesta = $banco->realizar_consulta('eliminar_banco');
            if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::ELIMINAR); 
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($banco)) {$banco->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

// Validaciones AJAX
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $validadorBD = new ValidadorBD();

    switch ($validar) {
        case 'validar_clave_foranea':
            $tabla = $_POST['tabla'] ?? '';
            $campo = $_POST['nombre_clave'] ?? '';
            $valor = $_POST['valor'] ?? '';

            if (empty($tabla) || empty($campo) || empty($valor)) {
                throw new HaydeeException('Faltan parámetros de Validación', HttpCodigo::BAD_REQUEST->value);
            }
            if ($tabla !== 'bancos') {
                throw new HaydeeException('Tabla no soportada', HttpCodigo::BAD_REQUEST->value);
            }

            $existe = $validadorBD->existe($tabla, $campo, $valor);
            $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'No existe'];
            break;

        case 'codigo_banco':
            $codigo = $_POST["codigo"] ?? '';
            $id_banco = $_POST["id_banco"] ?? null; 

            $bancoTemp = new Banco();
            $existe = $bancoTemp->verificarCodigoEnUso($codigo, $id_banco);
            $bancoTemp->cerrar();

            $respuesta = [
                'estatus' => true, 
                'existe' => $existe, 
                'mensaje' => $existe ? 'El código ya pertenece a una entidad activa.' : 'Disponible'
            ];
            break;

        default:
            throw new HaydeeException('Validación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }

    http_response_code(HttpCodigo::OK->value);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_BANCOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_BANCOS);
$btn_nuevo = [
    'target'  => '#modal_banco',
    'texto'   => 'Nuevo Banco',
    'tooltip' => 'Registrar Nuevo Banco'
];
$placeholder_buscar = "Buscar banco...";

require_once "vista/bancos/bancos_vista.php";