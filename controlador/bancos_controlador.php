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

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_BANCOS, $operacion);
    
    // 1. Obtenemos las reglas centralizadas
    $reglas = Banco::obtenerReglas($operacion);

    // 2. Ejecutamos la Validación si aplica
    if (!empty($reglas)) {
        $validador = new Validador();
        
        $contexto = [];
        // Permitimos que al modificar, se excluya el ID actual de la regla Unique de la cuenta
        if (strpos($operacion, 'modificar') !== false) {
            $contexto['exclude_id'] = $_POST['id_banco'] ?? null;
        }

        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }
    // Instancia del modelo
    $banco = new Banco();

    // Asignación masiva 
    $banco->set_id_banco($_POST['id_banco'] ?? null);
    $banco->set_nombre_banco($_POST['nombre_banco'] ?? null);
    $banco->set_codigo($_POST['codigo'] ?? null);
    $banco->set_numero_cuenta($_POST['numero_cuenta'] ?? null);
    $banco->set_tipo_cuenta($_POST['tipo_cuenta'] ?? null);
    $banco->set_telefono_afiliado($_POST['telefono_afiliado'] ?? null);
    $banco->set_rif($_POST['rif'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($banco, Modulo::GESTIONAR_BANCOS);

    try{
        switch ($operacion) {
            case 'consulta':
                $respuesta = $banco->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::CONSULTAR);
                }
                break;

            case 'registrar_banco':
                $respuesta = $banco->realizar_consulta('registrar_banco');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::REGISTRAR);
                }
                break;

            case 'consultar_banco':
                $respuesta = $banco->realizar_consulta('consultar_banco');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
                break;

            case 'modificar_banco':
                $auditor->capturarDatosAnteriores('consultar_banco');
                $respuesta = $banco->realizar_consulta('modificar_banco');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria(Accion::MODIFICAR); 
                }
                break;

            case 'eliminar_banco':
                $auditor->capturarDatosAnteriores('consultar_banco');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                $respuesta = $banco->realizar_consulta('eliminar_banco');
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
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explicitamente
            if (isset($banco)) {
                $banco->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexion de seguridad

            echo json_encode($respuesta);
            exit;
        }
    }
}

// Validaciones AJAX
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];

    $validadorBD = new ValidadorBD();

    try {
        switch ($validar) {
            case 'numero_cuenta':
                $numero_cuenta = $_POST["numero_cuenta"] ?? '';
                $id = !empty($_POST["id_banco"]) ? $_POST["id_banco"] : null;
                
                $existe = !$validadorBD->esUnico('bancos', 'numero_cuenta', $numero_cuenta, 'id_banco', $id);
                $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El número de cuenta ya está registrado' : 'Disponible'];
                break;

            case 'validar_clave_foranea':
                $tabla = $_POST['tabla'] ?? '';
                $campo = $_POST['nombre_clave'] ?? '';
                $valor = $_POST['valor'] ?? '';

                if (empty($tabla) || empty($campo) || empty($valor)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parámetros de Validación'];
                    break;
                }

                if ($tabla !== 'bancos') {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Tabla no soportada'];
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

// Cargar la vista
require_once "vista/bancos/bancos_vista.php";

