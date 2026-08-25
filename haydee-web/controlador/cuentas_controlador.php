<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\CuentasCondominio;
use haydee\modelo\Banco;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_CUENTAS, $operacion);
    
    $reglas = CuentasCondominio::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $contexto = [];
        if (strpos($operacion, 'modificar') !== false) {
            $contexto['exclude_id'] = $_POST['id_cuenta'] ?? null;
        }

        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $cuenta = new CuentasCondominio();

    $cuenta->set_id_cuenta($_POST['id_cuenta'] ?? null);
    $cuenta->set_banco_id($_POST['banco_id'] ?? null);
    $cuenta->set_numero_cuenta($_POST['numero_cuenta'] ?? null);
    $cuenta->set_tipo_cuenta($_POST['tipo_cuenta'] ?? null);
    $cuenta->set_telefono_afiliado($_POST['telefono_afiliado'] ?? null);
    $cuenta->set_rif($_POST['rif'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($cuenta, Modulo::GESTIONAR_CUENTAS);
    $codigoExito = HttpCodigo::OK->value;

    switch ($operacion) {
        case 'consulta':
            $respuesta = $cuenta->realizar_consulta('consultar');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'registrar_cuenta':
            $respuesta = $cuenta->realizar_consulta('registrar_cuenta');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'consultar_cuenta':
            $respuesta = $cuenta->realizar_consulta('consultar_cuenta');
            break;

        case 'modificar_cuenta':
            $auditor->capturarDatosAnteriores('consultar_cuenta');
            $respuesta = $cuenta->realizar_consulta('modificar_cuenta');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::MODIFICAR); 
            }
            break;

        case 'eliminar_cuenta':
            $auditor->capturarDatosAnteriores('consultar_cuenta');
            $respuesta = $cuenta->realizar_consulta('eliminar_cuenta');
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

    $cuenta->cerrar();
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
        case 'numero_cuenta':
            $numero_cuenta = $_POST["numero_cuenta"] ?? '';
            $id = !empty($_POST["id_cuenta"]) ? $_POST["id_cuenta"] : null;
            
            $cuentaTemp = new CuentasCondominio();
            $existe = $cuentaTemp->verificarNumeroEnUso($numero_cuenta, $id);
            $cuentaTemp->cerrar();

            $respuesta = [
                'estatus' => true, 
                'existe' => $existe, 
                'mensaje' => $existe ? 'El número de cuenta ya está asignado o tiene historial.' : 'Disponible'
            ];
            break;

        case 'validar_clave_foranea':
            $tabla = $_POST['tabla'] ?? '';
            $campo = $_POST['nombre_clave'] ?? '';
            $valor = $_POST['valor'] ?? '';

            if (empty($tabla) || empty($campo) || empty($valor)) {
                throw new HaydeeException('Faltan parámetros de validación', HttpCodigo::BAD_REQUEST->value);
            }

            if ($tabla !== 'cuentas_condominio') {
                throw new HaydeeException('Tabla no soportada', HttpCodigo::BAD_REQUEST->value);
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_CUENTAS);

    $bancoModel = new Banco();
    $registro_bancos = $bancoModel->realizar_consulta('consultar')['datos'] ?? [];
    $bancoModel->cerrar();
}

$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_CUENTAS);
$btn_nuevo = [
    'target'  => '#modal_cuenta',
    'texto'   => 'Nueva Cuenta',
    'tooltip' => 'Registrar Nueva Cuenta'
];
$placeholder_buscar = "Buscar cuenta...";

require_once "vista/cuentas_condominio/cuentas_vista.php";