<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Apartamento;
use haydee\modelo\Habitantes;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    $moduloAfectado = strpos($operacion, 'habitante') !== false ? Modulo::GESTIONAR_HABITANTES : Modulo::GESTIONAR_APARTAMENTOS;
    Sesiones::verificarPermisoAccion($moduloAfectado, $operacion);

    $reglasApartamento = Apartamento::obtenerReglas($operacion);
    $reglasHabitante = Habitantes::obtenerReglas($operacion);
    
    if (isset($_POST['tipo_cedula']) && isset($_POST['cedula'])) {
        $_POST['cedula'] = $_POST['tipo_cedula'] . $_POST['cedula'];
    }

    $reglasCompletas = array_merge($reglasApartamento, $reglasHabitante);

    if (!empty($reglasCompletas)) {
        $validador = new Validador();
        $contexto = [];
        if (strpos($operacion, 'modificar') !== false) {
            $contexto['exclude_id'] = $_POST['id_apartamento'] ?? $_POST['id_habitante'] ?? null;
        }

        $validador->validarConjunto($_POST, $reglasCompletas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $apartamento = new Apartamento();
    $habitante = new Habitantes();

    $apartamento->set_id_apartamento($_POST['id_apartamento'] ?? null);
    $apartamento->set_nro_apartamento($_POST['nro_apartamento'] ?? null);
    $apartamento->set_porcentaje_participacion($_POST['porcentaje_participacion'] ?? null);
    $apartamento->set_gas($_POST['gas'] ?? null);
    $apartamento->set_agua($_POST['agua'] ?? null);
    $apartamento->set_alquilado($_POST['alquilado'] ?? null);

    $habitante->set_id_habitante($_POST['id_habitante'] ?? null);
    $habitante->set_nombre($_POST['nombre'] ?? null);
    $habitante->set_apellido($_POST['apellido'] ?? null);
    $habitante->set_telefono($_POST['telefono'] ?? null);
    $habitante->set_correo($_POST['correo'] ?? null);
    $habitante->set_fecha_nacimiento($_POST['fecha_nacimiento'] ?? null);
    $habitante->set_sexo($_POST['sexo'] ?? null);
    $habitante->set_nuevo_apartamento_id($_POST['apartamento_id'] ?? null);
    $habitante->set_nuevo_tipo_vinculo($_POST['tipo_vinculo'] ?? null);
    $habitante->set_cedula($_POST['cedula'] ?? null);

    $auditorApartamento = new GestorAuditoria($apartamento, Modulo::GESTIONAR_APARTAMENTOS);
    $auditorHabitante = new GestorAuditoria($habitante, Modulo::GESTIONAR_HABITANTES);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        // ================= APARTAMENTOS =================
        case 'consulta':
            $respuesta = $apartamento->realizar_consulta('consultar_listado');
            if ($respuesta['estatus']) $auditorApartamento->registrarAuditoria(Accion::CONSULTAR);
            break;

        case 'registrar_apartamento':
            $respuesta = $apartamento->realizar_consulta('registrar_apartamento');
            if ($respuesta['estatus']) {
                $auditorApartamento->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'consulta_especifica':
            $respuesta = $apartamento->realizar_consulta('consultar_detalle_completo');
            if ($respuesta['estatus']) {
                $datos = $respuesta['datos'];
                $respuesta = [
                    'estatus' => true,
                    'apartamento' => $datos,
                    'detalles' => $datos['habitantes'] ?? []
                ];
            }
            break;

        case 'modificar_apartamento':
            $auditorApartamento->capturarDatosAnteriores('consultar_detalle_completo');
            $respuesta = $apartamento->realizar_consulta('modificar_apartamento');
            if ($respuesta['estatus']) $auditorApartamento->registrarAuditoria(Accion::MODIFICAR); 
            break;

        case 'eliminar':
            $auditorApartamento->capturarDatosAnteriores('consultar_detalle_completo');
            $respuesta = $apartamento->realizar_consulta('eliminar_apartamento');
            if ($respuesta['estatus']) $auditorApartamento->registrarAuditoria(Accion::ELIMINAR); 
            break;

        // ================= HABITANTES =================
        case 'consultar_habitantes':
            $respuesta = $apartamento->realizar_consulta('consultar_detalle_completo');
            if ($respuesta['estatus']) {
                $respuesta = ['estatus' => true, 'datos' => $respuesta['datos']['habitantes'] ?? []];
            }
            break;

        case 'registrar_habitantes':
            $respuesta = $habitante->realizar_consulta('registrar_habitantes');
            if ($respuesta['estatus']) {
                $auditorHabitante->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'consulta_especifica_habitante':
            $respuesta = $habitante->realizar_consulta('consultar_habitante');
            break;

        case 'modificar_habitantes':
            $auditorHabitante->capturarDatosAnteriores('consultar_habitante');
            $respuesta = $habitante->realizar_consulta('modificar_habitantes');
            if ($respuesta['estatus']) $auditorHabitante->registrarAuditoria(Accion::MODIFICAR);
            break;

        case 'eliminar_habitantes':
            $auditorHabitante->capturarDatosAnteriores('consultar_habitante');
            $respuesta = $habitante->realizar_consulta('eliminar_habitantes');
            if ($respuesta['estatus']) $auditorHabitante->registrarAuditoria(Accion::ELIMINAR);
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($apartamento)) {$apartamento->cerrar();}
    if (isset($habitante)) {$habitante->cerrar();}
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
        case 'nro_apartamento':
            $nro = $_POST["nro_apartamento"] ?? '';
            $id = !empty($_POST["id_apartamento"]) ? $_POST["id_apartamento"] : null;
            $existe = !$validadorBD->esUnico('apartamentos', 'nro_apartamento', $nro, 'id_apartamento', $id);
            $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El número ya existe' : 'Disponible'];
            break;

        case 'cedula':
            $cedula = $_POST["cedula"] ?? '';
            if (isset($_POST['tipo_cedula']) && isset($_POST['cedula'])) {
                $cedula = $_POST['tipo_cedula'] . $_POST['cedula'];
            }
            $id = !empty($_POST["id_habitante"]) ? $_POST["id_habitante"] : null;
            $existe = !$validadorBD->esUnico('habitantes', 'cedula', $cedula, 'id_habitante', $id);
            $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'La cédula ya está registrada' : 'Disponible'];
            break;

        case 'correo':
            $correo = $_POST["correo"] ?? '';
            $id = !empty($_POST["id_habitante"]) ? $_POST["id_habitante"] : null;
            $existe = !$validadorBD->esUnico('habitantes', 'correo', $correo, 'id_habitante', $id);
            $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El correo ya está en uso' : 'Disponible'];
            break;

        case 'tipo_vinculo':
            $apartamento_id = $_POST["apartamento_id"] ?? '';
            $tipo_vinculo = $_POST["tipo_vinculo"] ?? '';
            $existe = false;
            
            if ($tipo_vinculo === 'Propietario' && !empty($apartamento_id)) {
                $existe = $validadorBD->existeConCondicion('habitantes_apartamentos', [
                    'apartamento_id' => $apartamento_id,
                    'tipo_vinculo' => 'Propietario'
                ]);
            }
            $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'Ya existe un propietario' : 'Disponible'];
            break;

        case 'validar_clave_foranea':
            $tabla = $_POST["tabla"] ?? '';
            $campo = $_POST["nombre_clave"] ?? '';
            $valor = $_POST["valor"] ?? '';

            if (empty($tabla) || empty($campo) || empty($valor)) {
                throw new HaydeeException('Faltan parámetros', HttpCodigo::BAD_REQUEST->value);
            }

            $tablasPermitidas = ['apartamentos', 'habitantes'];
            if (!in_array($tabla, $tablasPermitidas)) {
                throw new HaydeeException('Tabla no soportada', HttpCodigo::BAD_REQUEST->value);
            }

            $existe = $validadorBD->existe($tabla, $campo, $valor);
            $respuesta = ['estatus' => $existe, 'mensaje' => 'OK'];
            break;

        default:
            throw new HaydeeException('Validación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }

    http_response_code(HttpCodigo::OK->value);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_APARTAMENTOS);
}
$permisosVista = [
    'apartamentos' => Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_APARTAMENTOS),
    'habitantes'   => Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_HABITANTES)
];

$placeholder_buscar = "Buscar apartamento...";

require_once "vista/apartamentos/apartamentos_vista.php";