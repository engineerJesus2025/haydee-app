<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Rol;
use haydee\modelo\Usuario;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

$rol_obj = new Rol();
$roles = $rol_obj->realizar_consulta('consultar_roles');
$rol_obj->cerrar();

$idUsuarioSesion = $_SESSION["id_usuario"] ?? 0;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    
    $_POST['id_usuario'] = $idUsuarioSesion;

    $reglas = Usuario::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $contexto = ['exclude_id' => $idUsuarioSesion];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $usuario = new Usuario();

    $usuario->set_id_usuario($idUsuarioSesion); 
    $usuario->set_apellido($_POST['apellido'] ?? null);
    $usuario->set_nombre($_POST['nombre'] ?? null);
    $usuario->set_correo($_POST['correo'] ?? null);
    $usuario->set_contra($_POST['contra'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($usuario, Modulo::GESTIONAR_USUARIOS);
    $codigoExito = HttpCodigo::OK->value;

    switch ($operacion) {
        case 'consultar_perfil_usuario':
            $respuesta = $usuario->realizar_consulta('consultar_perfil_usuario');
            break;

        case 'consultar_mis_notificaciones':
            $notificaciones = new Notificaciones();
            $notificaciones->set_usuario_id($idUsuarioSesion);
            $respuesta = $notificaciones->realizar_consulta('consultar_mis_notificaciones');
            $notificaciones->cerrar();
            break;

        case 'modificar_perfil':
            $auditor->capturarDatosAnteriores('consultar_usuario');
            $respuesta = $usuario->realizar_consulta('modificar_perfil');
            if ($respuesta['estatus']) { 
                $_SESSION["nombre_completo"] = $usuario->get_nombre() . " " . $usuario->get_apellido();
                $usuario->set_contra(null);
                $auditor->registrarAuditoria(Accion::MODIFICAR);
            }
            break;

        case 'cambiar_contrasenia':
            $respuesta = $usuario->realizar_consulta('cambiar_contrasenia');
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    $usuario->cerrar();
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
        case 'correo':
            $correo = $_POST["correo"] ?? '';
            $existe = !$validadorBD->esUnico('usuarios', 'correo', $correo, 'id_usuario', $idUsuarioSesion);
            $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El correo ya está en uso' : 'Disponible'];
            break;

        case 'contrasenia_actual':
            $usuario = new Usuario();
            $usuario->set_id_usuario($idUsuarioSesion);
            $datosUsuario = $usuario->realizar_consulta('consultar_usuario');
            $contraIngresada = $_POST['contra'] ?? '';
            $usuario->cerrar();

            $coincide = false;
            if ($datosUsuario['estatus'] && isset($datosUsuario['datos']['contrasenia'])) {
                $coincide = password_verify($contraIngresada, $datosUsuario['datos']['contrasenia']);
            }
            http_response_code(HttpCodigo::OK->value);
            echo json_encode($coincide);
            exit;

        case 'validar_clave_foranea':
            $tabla = $_POST['tabla'] ?? '';
            $campo = $_POST['nombre_clave'] ?? '';
            $valor = $_POST['valor'] ?? '';

            if (empty($tabla) || empty($campo) || empty($valor)) {
                throw new HaydeeException('Faltan parámetros de validación', HttpCodigo::BAD_REQUEST->value);
            }

            $tablasPermitidas = ['roles', 'usuarios'];
            if (!in_array($tabla, $tablasPermitidas)) {
                throw new HaydeeException('Tabla no soportada', HttpCodigo::BAD_REQUEST->value);
            }

            $existe = $validadorBD->existe($tabla, $campo, $valor);
            $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'El valor no existe en la base de datos'];
            break;

        default:
            throw new HaydeeException('Validación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }

    http_response_code(HttpCodigo::OK->value);
    echo json_encode($respuesta);
    exit;
}

// CARGA DE VISTAS
if (isset($accion) && $accion == "perfil") {
    $usuario = new Usuario();
    $usuario->set_id_usuario($idUsuarioSesion);
    $usuarioData = $usuario->realizar_consulta('consultar_usuario');
    $usuarioInfo = $usuarioData['estatus'] ? $usuarioData['datos'] : [];
    $usuario->cerrar();

    $placeholder_buscar = "Buscar notificación...";
    require_once "vista/usuarios/usuario_perfil.php";
}