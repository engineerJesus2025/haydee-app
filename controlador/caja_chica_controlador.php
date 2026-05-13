<?php
use haydee\enums\Modulo;
use haydee\enums\Accion;

use haydee\servicios\Sesiones;
use haydee\modelo\CajaChica;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorNotificaciones;

// Verificaciones de seguridad
Sesiones::autorizarAcceso(Modulo::GESTIONAR_CAJA_CHICA, Accion::CONSULTAR);

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    // Mapeamos las operaciones que no contengan las palabras clave estÃ¡ndar
    $operacionesEspeciales = [
        'reponer_caja' => Accion::REGISTRAR->value
    ];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_CAJA_CHICA, $operacion, $operacionesEspeciales);

    // 1. Validamos segÃºn la Operación
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

    // Asignacion masiva (Manejando tanto el id primario como el forÃ¡neo)
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
    $auditor = new GestorAuditoria($caja, Modulo::GESTIONAR_CAJA_CHICA);

    try {
        switch ($operacion) {
            case 'consultar_cajas_chicas':
                $respuesta = $caja->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::CONSULTAR);
                }
                break;

            case 'modificar_descripcion':
                // Obtener datos anteriores
                // $auditor->capturarDatosAnteriores('consulta_caja_chica');

                $respuesta = $caja->realizar_consulta('modificar_descripcion');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                // if ($respuesta['estatus']) { 
                //     $auditor->registrarAuditoria(Accion::MODIFICAR); 
                // }
                break;

            case 'verificar_caja_mes':
                $respuesta = $caja->realizar_consulta('verificar_caja_mes');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    Bitacora::registrar(Accion::REGISTRAR, Modulo::GESTIONAR_CAJA_CHICA);
                }
                break;

            case 'reponer_caja':
                $respuesta = $caja->realizar_consulta('reponer_caja');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::REGISTRAR);
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
                    $auditor->registrarAuditoria(Accion::REGISTRAR);

                    // Verificamos si el modelo nos mandÃ³ un aviso sobre el saldo
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
                    $auditor->registrarAuditoria(Accion::MODIFICAR); 

                    // Verificamos si el modelo nos mandÃ³ un aviso sobre el saldo
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
                    $auditor->registrarAuditoria(Accion::ELIMINAR); 

                    // Verificamos si el modelo nos mandÃ³ un aviso sobre el saldo
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
            // Cerrar conexiones explÃ­citamente
            if (isset($caja)) {
                $caja->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexiÃ³n de seguridad

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
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parÃ¡metros de Validación'];
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
        error_log("Error en Validación AJAX Bancos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(200);
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_CAJA_CHICA);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_CAJA_CHICA);
$btn_nuevo = [
    'target'  => '#modal_registro_gastos',
    'texto'   => 'Nuevo Gasto',
    'tooltip' => 'Registrar Nuevo Gasto de Caja'
];
$placeholder_buscar = "Buscar movimiento...";

require_once "vista/caja_chica/caja_chica_vista.php";
?>
