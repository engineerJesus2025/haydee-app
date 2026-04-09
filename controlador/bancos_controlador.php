<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Banco;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_BANCOS, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(GESTIONAR_BANCOS, $operacion);
    
    // 1. Obtenemos las reglas centralizadas
    $reglas = Banco::obtenerReglas($operacion);

    // 2. Ejecutamos la validación si aplica
    if (!empty($reglas)) {
        $validador = new Validador();
        
        $contexto = [];
        // Permitimos que al modificar, se excluya el ID actual de la regla Unique de la cuenta
        if (strpos($operacion, 'modificar') !== false) {
            $contexto['exclude_id'] = $_POST['id_banco'] ?? null;
        }

        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // --- DATOS PUROS Y SEGUROS ---

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
    $auditor = new GestorAuditoria($banco, GESTIONAR_BANCOS);

    try{
        switch ($operacion) {
            case 'consulta':
                $respuesta = $banco->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'registrar_banco':
                $respuesta = $banco->realizar_consulta('registrar_banco');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'consultar_banco':
                $respuesta = $banco->realizar_consulta('consultar_banco');
                break;

            case 'modificar_banco':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_banco');

                $respuesta = $banco->realizar_consulta('modificar_banco');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar_banco':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_banco');

                $respuesta = $banco->realizar_consulta('eliminar_banco');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($banco)) {
                $banco->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
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
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parámetros de validación'];
                    break;
                }

                if ($tabla !== 'bancos') {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Tabla no soportada'];
                    break;
                }

                $existe = $validadorBD->existe($tabla, $campo, $valor);
                $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'No existe'];
                break;
        }
    } catch (Exception $e) {
        error_log("Error en validación AJAX Bancos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_BANCOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_BANCOS);
// Cargar la vista
require_once "vista/bancos/bancos_vista.php";