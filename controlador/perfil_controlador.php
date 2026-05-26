<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;

use haydee\servicios\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Usuario;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

Sesiones::autorizarAcceso();

$rol_obj = new Rol();
$roles = $rol_obj->realizar_consulta('consultar_roles');

$idUsuarioSesion = $_SESSION["id_usuario"];

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];
    
    // FORZAMOS el ID del usuario en $_POST antes de validar y procesar.
    // Con esto destruimos cualquier intento de manipulación o inyección desde el HTML.
    $_POST['id_usuario'] = $idUsuarioSesion;

    // Obtenemos las reglas centralizadas dictadas por el modelo
    $reglas = Usuario::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        
        $contexto = ['exclude_id' => $idUsuarioSesion];

        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::BAD_REQUEST->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $usuario = new Usuario();

    $usuario->set_id_usuario($idUsuarioSesion); 
    $usuario->set_apellido($_POST['apellido'] ?? null);
    $usuario->set_nombre($_POST['nombre'] ?? null);
    $usuario->set_correo($_POST['correo'] ?? null);
    $usuario->set_contra($_POST['contra'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    $auditor = new GestorAuditoria($usuario, Modulo::GESTIONAR_USUARIOS);

    try {
        switch ($operacion) {
            case 'consultar_perfil_usuario':
                $respuesta = $usuario->realizar_consulta('consultar_perfil_usuario');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'consultar_mis_notificaciones':
                $notificaciones = new Notificaciones();
                $notificaciones->set_usuario_id($idUsuarioSesion);

                $respuesta = $notificaciones->realizar_consulta('consultar_mis_notificaciones');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'modificar_perfil':
                $auditor->capturarDatosAnteriores('consultar_usuario');

                $respuesta = $usuario->realizar_consulta('modificar_perfil');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { 
                    // Si el cambio en la BD tuvo éxito, actualizamos los datos de la sesión web en caliente
                    $_SESSION["nombre_completo"] = $usuario->get_nombre() . " " . $usuario->get_apellido();
                    $usuario->set_contra(null); // Limpieza por seguridad antes de auditar
                    $auditor->registrarAuditoria(Accion::MODIFICAR);
                }
                break;

            case 'cambiar_contrasenia':
                $respuesta = $usuario->realizar_consulta('cambiar_contrasenia');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
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
            if (isset($usuario)) { $usuario->cerrar('seguridad'); }
            if (isset($rol_obj)) { $rol_obj->cerrar('seguridad'); }
            if (isset($notificaciones)) { $notificaciones->cerrar('seguridad'); }
            Bitacora::cerrarConexionBitacora();

            echo json_encode($respuesta);
            exit;
        }
    }
}

// VALIDACIONES AJAX
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];

    $validadorBD = new ValidadorBD();

    try {
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
                
                $coincide = false;
                if ($datosUsuario['estatus'] && isset($datosUsuario['datos']['contrasenia'])) {
                    $coincide = password_verify($contraIngresada, $datosUsuario['datos']['contrasenia']);
                }
                echo json_encode($coincide);
                exit;

            case 'validar_clave_foranea':
                $tabla = $_POST['tabla'] ?? '';
                $campo = $_POST['nombre_clave'] ?? '';
                $valor = $_POST['valor'] ?? '';

                if (empty($tabla) || empty($campo) || empty($valor)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parámetros de Validación'];
                    break;
                }

                $tablasPermitidas = ['roles', 'usuarios'];
                if (!in_array($tabla, $tablasPermitidas)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Tabla no soportada'];
                    break;
                }

                $existe = $validadorBD->existe($tabla, $campo, $valor);
                $respuesta = ['estatus' => $existe, 'mensaje' => $existe ? 'OK' : 'El valor no existe en la base de datos'];
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en Validación AJAX Usuario: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(HttpCodigo::OK->value);
    }

    echo json_encode($respuesta);
    exit;
}

// CARGA DE VISTAS
if ($accion == "perfil") {
    $usuario = new Usuario();
    $usuario->set_id_usuario($idUsuarioSesion);
    $usuarioData = $usuario->realizar_consulta('consultar_usuario');
    $usuario = $usuarioData['estatus'] ? $usuarioData['datos'] : [];
    $placeholder_buscar = "Buscar notificación...";
    require_once "vista/usuarios/usuario_perfil.php";
}