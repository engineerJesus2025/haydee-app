<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\enums\TipoEventoNotificacion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\CajaChica;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorTasa;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorNotificaciones;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    $operacionesEspeciales = [
        'reponer_caja' => Accion::REGISTRAR->value
    ];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_CAJA_CHICA, $operacion, $operacionesEspeciales);

    $reglas = CajaChica::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $caja = new CajaChica();

    $caja->set_id_caja_chica($_POST['id_caja_chica'] ?? $_POST['caja_chica_id'] ?? null);
    $caja->set_descripcion($_POST['descripcion'] ?? null);
    $caja->set_fondo_fijo($_POST['fondo_fijo'] ?? null);
    $caja->set_estado($_POST['estado'] ?? null);
    
    $caja->set_id_movimiento_caja($_POST['id_movimiento_caja'] ?? null);
    $caja->set_concepto($_POST['concepto'] ?? null);
    $caja->set_monto_movimiento($_POST['monto'] ?? null);
    $caja->set_fecha_movimiento($_POST['fecha'] ?? null);

    $operacionesMonetarias = ['registrar_movimiento','modificar_movimiento','reponer_caja'];
    if (in_array($operacion, $operacionesMonetarias)) {
        $tasaDolar = GestorTasa::obtener();
        $caja->set_tasa_dolar($tasaDolar);
    }

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    $auditor = new GestorAuditoria($caja, Modulo::GESTIONAR_CAJA_CHICA);
    $codigoExito = HttpCodigo::OK->value;
    
    switch ($operacion) {
        case 'consultar_cajas_chicas':
            $respuesta = $caja->realizar_consulta('consultar');
            if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
            break;

        case 'registrar_caja_chica':
            $respuesta = $caja->realizar_consulta('registrar_caja_chica');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'modificar_descripcion':
            $respuesta = $caja->realizar_consulta('modificar_descripcion');
            break;

        case 'verificar_caja_mes':
            $respuesta = $caja->realizar_consulta('verificar_caja_mes');
            if ($respuesta['estatus']) Bitacora::registrar(Accion::REGISTRAR, Modulo::GESTIONAR_CAJA_CHICA);
            break;

        case 'reponer_caja':
            $respuesta = $caja->realizar_consulta('reponer_caja');
            if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::REGISTRAR);
            break;

        case 'consultar_movimientos_caja':
            $respuesta = $caja->realizar_consulta('consultar_movimientos');
            break;

        case 'consultar_movimiento':
            $respuesta = $caja->realizar_consulta('consultar_movimiento_unico');
            break;

        case 'registrar_movimiento':
            $respuesta = $caja->realizar_consulta('registrar_movimiento');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                if (isset($respuesta['alerta_saldo']) && $respuesta['alerta_saldo'] !== null) {
                    $id_caja = $_POST['caja_chica_id'] ?? null;
                    GestorNotificaciones::notificarAdmins(
                        $respuesta['alerta']['titulo'], 
                        $respuesta['alerta']['desc'], 
                        "caja_chica", 
                        $id_caja, 
                        TipoEventoNotificacion::BAJO_SALDO->value 
                    );
                }
            }
            break;

        case 'modificar_movimiento':
            $auditor->capturarDatosAnteriores('consultar_movimiento_unico');
            $respuesta = $caja->realizar_consulta('modificar_movimiento');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::MODIFICAR); 
                if (isset($respuesta['alerta_saldo']) && $respuesta['alerta_saldo'] !== null) {
                    GestorNotificaciones::notificarAdmins(
                        $respuesta['alerta_saldo']['titulo'], 
                        $respuesta['alerta_saldo']['desc'], 
                        'caja_chica', 
                        $_POST['caja_chica_id'], 
                        $respuesta['alerta_saldo']['tipo']
                    );
                }
            }
            break;

        case 'eliminar_movimiento':
            $auditor->capturarDatosAnteriores('consultar_movimiento_unico');
            $respuesta = $caja->realizar_consulta('eliminar_movimiento');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::ELIMINAR); 
                if (isset($respuesta['alerta_saldo']) && $respuesta['alerta_saldo'] !== null) {
                    GestorNotificaciones::notificarAdmins(
                        $respuesta['alerta_saldo']['titulo'], 
                        $respuesta['alerta_saldo']['desc'], 
                        'caja_chica', 
                        $_POST['caja_chica_id'], 
                        $respuesta['alerta_saldo']['tipo']
                    );
                }
            }
            break;

        default:
            throw new HaydeeException('Operación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($caja)) {$caja->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

// === VALIDACIONES AJAX ===
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $validadorBD = new ValidadorBD();
    $codigoExito = HttpCodigo::OK->value;
    switch ($validar) {
        case 'validar_clave_foranea':
            $tabla = $_POST['tabla'] ?? '';
            $campo = $_POST['nombre_clave'] ?? '';
            $valor = $_POST['valor'] ?? '';

            if (empty($tabla) || empty($campo) || empty($valor)) {
                throw new HaydeeException('Faltan parámetros de Validación', HttpCodigo::BAD_REQUEST->value);
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