<?php
use haydee\servicios\Sesiones;
use haydee\modelo\CajaChica;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorNotificaciones;

Sesiones::validarMetodoHTTP(['GET', 'POST']);
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_CAJA_CHICA, CONSULTAR);

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    // Mapeamos las operaciones que no contengan las palabras clave estándar
    $operacionesEspeciales = [
        'reponer_caja' => REGISTRAR
    ];

    Sesiones::verificarPermisoAccion(GESTIONAR_CAJA_CHICA, $operacion, $operacionesEspeciales);

    // 1. Validamos según la operación
    $reglas = CajaChica::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? 404 : 400;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $caja = new CajaChica();

    // Asignación masiva (Manejando tanto el id primario como el foráneo)
    $caja->set_id_caja_chica($_POST['id_caja_chica'] ?? $_POST['caja_chica_id'] ?? null);
    $caja->set_descripcion($_POST['descripcion'] ?? null);
    $caja->set_fondo_fijo($_POST['fondo_fijo'] ?? null);
    $caja->set_estado($_POST['estado'] ?? null);
    
    // Asignaciones de Movimientos
    $caja->set_id_movimiento_caja($_POST['id_movimiento_caja'] ?? null);
    $caja->set_concepto($_POST['concepto'] ?? null);
    $caja->set_monto_movimiento($_POST['monto'] ?? null);
    $caja->set_fecha_movimiento($_POST['fecha'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($caja, GESTIONAR_CAJA_CHICA);

    try {
        switch ($operacion) {
            case 'consultar_cajas_chicas':
                $respuesta = $caja->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'modificar_descripcion':
                // Obtener datos anteriores
                // $auditor->capturarDatosAnteriores('consulta_caja_chica');

                $respuesta = $caja->realizar_consulta('modificar_descripcion');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                // if ($respuesta['estatus']) { 
                //     $auditor->registrarAuditoria('modificar'); 
                // }
                break;

            case 'verificar_caja_mes':
                $respuesta = $caja->realizar_consulta('verificar_caja_mes');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_CAJA_CHICA);
                }
                break;

            case 'reponer_caja':
                $respuesta = $caja->realizar_consulta('reponer_caja');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            // === OPERACIONES MOVIMIENTOS ===
            case 'consultar_movimientos_caja':
                $respuesta = $caja->realizar_consulta('consultar_movimientos');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                break;

            case 'consultar_movimiento':
                $respuesta = $caja->realizar_consulta('consultar_movimiento_unico');
                http_response_code($respuesta['estatus'] ? 200 : 404);
                break;

            case 'registrar_movimiento':
                $respuesta = $caja->realizar_consulta('registrar_movimiento');

                http_response_code($respuesta['estatus'] ? 201 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');

                    // Verificamos si el modelo nos mandó un aviso sobre el saldo
                    if (isset($respuesta['alerta_saldo']) && $respuesta['alerta_saldo'] !== null) {
                        $alerta = $respuesta['alerta_saldo'];
                        GestorNotificaciones::notificarAdmins(
                            $alerta['titulo'], 
                            $alerta['desc'], 
                            'caja_chica', 
                            $_POST['caja_chica_id'], 
                            $alerta['tipo']
                        );
                    }
                }
                break;

            case 'modificar_movimiento':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_movimiento_unico');

                $respuesta = $caja->realizar_consulta('modificar_movimiento');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 

                    // Verificamos si el modelo nos mandó un aviso sobre el saldo
                    if (isset($respuesta['alerta_saldo']) && $respuesta['alerta_saldo'] !== null) {
                        $alerta = $respuesta['alerta_saldo'];
                        GestorNotificaciones::notificarAdmins(
                            $alerta['titulo'], 
                            $alerta['desc'], 
                            'caja_chica', 
                            $_POST['caja_chica_id'], 
                            $alerta['tipo']
                        );
                    }
                }
                break;

            case 'eliminar_movimiento':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_movimiento_unico');

                $respuesta = $caja->realizar_consulta('eliminar_movimiento');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 

                    // Verificamos si el modelo nos mandó un aviso sobre el saldo
                    if (isset($respuesta['alerta_saldo']) && $respuesta['alerta_saldo'] !== null) {
                        $alerta = $respuesta['alerta_saldo'];
                        GestorNotificaciones::notificarAdmins(
                            $alerta['titulo'], 
                            $alerta['desc'], 
                            'caja_chica', 
                            $_POST['caja_chica_id'], 
                            $alerta['tipo']
                        );
                    }
                }
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($caja)) {
                $caja->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            echo json_encode($respuesta);
            exit;
        }
    }
}

// === VALIDACIONES AJAX ===
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];

    $validadorBD = new ValidadorBD();

    try {
        switch ($validar) {
            case 'validar_clave_foranea':
                $tabla = $_POST['tabla'] ?? '';
                $campo = $_POST['nombre_clave'] ?? '';
                $valor = $_POST['valor'] ?? '';

                if (empty($tabla) || empty($campo) || empty($valor)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parámetros de validación'];
                    break;
                }

                $existe = $validadorBD->existe($tabla, $campo, $valor);
                $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'No existe'];
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en validación AJAX Bancos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(200);
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_CAJA_CHICA);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_CAJA_CHICA);
$btn_nuevo = [
    'target'  => '#modal_registro_gastos',
    'texto'   => 'Nuevo Gasto',
    'tooltip' => 'Registrar Nuevo Gasto de Caja'
];
$placeholder_buscar = "Buscar movimiento...";

require_once "vista/caja_chica/caja_chica_vista.php";
?>