<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Usuario;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;

// Verificación de sesión (sin permiso específico porque es el perfil del propio usuario)
Sesiones::verificarSesion();

// Obtener lista de roles para la vista (recordar borrar)
$rol_obj = new Rol();
$roles = $rol_obj->realizar_consulta('consultar_roles');

// Instancia del modelo Usuario
$usuario = new Usuario();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

    // Asignación masiva de campos que pueden llegar (usuario)
    $usuario->set_id_usuario($_SESSION["id_usuario"] ?? null); // El ID siempre es el de sesión para perfil
    $usuario->set_apellido($_POST['apellido'] ?? null);
    $usuario->set_nombre($_POST['nombre'] ?? null);
    $usuario->set_correo($_POST['correo'] ?? null);
    $usuario->set_contra($_POST['contra'] ?? null);
    // No se asigna rol_id porque en perfil no se edita el rol

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try{
        switch ($operacion) {
            case 'consultar_perfil_usuario':
                $respuesta = $usuario->realizar_consulta('consultar_perfil_usuario');
                break;

            case 'consultar_mis_notificaciones':
                $notificaciones = new Notificaciones();
                $notificaciones->set_usuario_id($_SESSION["id_usuario"]);
                $respuesta = $notificaciones->realizar_consulta('consultar_mis_notificaciones');
                break;

            case 'editar_perfil':
                $respuesta = $usuario->realizar_consulta('editar_perfil');
                if ($respuesta['estatus']) {
                    $_SESSION["nombre_completo"] = $usuario->get_nombre();
                    Bitacora::registrar(MODIFICAR, GESTIONAR_USUARIOS, 'Perfil propio actualizado');
                }
                break;

            case 'cambiar_contrasenia':
                $respuesta = $usuario->realizar_consulta('cambiar_contrasenia');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_USUARIOS, 'Cambio de contraseña');
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    }

    echo json_encode($respuesta);
    exit;
}

// Validaciones AJAX
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');

    $validar = $_POST["validar"];

    switch ($validar) {
        case 'correo':
            $usuario->set_correo($_POST['correo'] ?? null);
            $resultado = $usuario->realizar_consulta('verificar_correo');
            if ($resultado['estatus']) {
                echo json_encode([
                    'estatus' => true,
                    'busqueda' => $resultado['existe'] ? 'correo' : null
                ]);
            } else {
                echo json_encode($resultado);
            }
            break;

        case 'contra':
        case 'contra_perfil':
            // Validar contraseña actual
            $id = ($validar == 'contra_perfil') ? $_SESSION["id_usuario"] : ($_POST['id_usuario'] ?? null);
            if (!$id) {
                echo json_encode(['estatus' => false, 'mensaje' => 'ID de usuario no proporcionado']);
                break;
            }
            $usuario->set_id_usuario($id);
            $datosUsuario = $usuario->realizar_consulta('consultar_usuario');
            $contraIngresada = $_POST['contra'] ?? '';
            $coincide = false;
            if ($datosUsuario['estatus'] && isset($datosUsuario['datos']['contrasenia'])) {
                $coincide = password_verify($contraIngresada, $datosUsuario['datos']['contrasenia']);
            }
            echo json_encode($coincide);
            break;

        case 'validar_clave_foranea':
            if (isset($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor'])) {
                        
                $existe = $usuario->validarExistenciaExterna($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor']);
                
                if ($existe) {
                     echo json_encode(['estatus' => true, 'mensaje' => 'El valor existe']);
                } else {
                     echo json_encode(['estatus' => false, 'mensaje' => 'El valor seleccionado no existe en la base de datos']);
                }

            } else {
                echo json_encode(['estatus' => false, 'mensaje' => 'Faltan parámetros de validación']);
            }
            break;

        default:
            echo json_encode(['estatus' => false, 'mensaje' => 'Validación no reconocida']);
    }
    exit;
}

// Carga de vistas según acción
if ($accion == "perfil") {
    $usuario->set_id_usuario($_SESSION["id_usuario"]);
    $usuarioData = $usuario->realizar_consulta('consultar_usuario');
    $usuario = $usuarioData['estatus'] ? $usuarioData['datos'] : [];
    require_once "vista/usuarios/usuario_perfil.php";
} elseif ($accion == "inicio") {
    require_once "vista/usuarios/usuario_vista.php";
}