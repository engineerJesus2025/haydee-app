<?php
//Nota: quitar seccion de perfil, ya tiene su controlador aparte
use haydee\ayuda\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Usuario;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_USUARIOS, CONSULTAR);

// Obtener lista de roles para la vista (solo si es necesario)
$rol_obj = new Rol();
$roles = $rol_obj->realizar_consulta('consultar');

// Instancia del modelo principal (usuario)
$usuario = new Usuario();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

    // Asignación masiva de campos que pueden llegar (usuario)
    $usuario->set_id_usuario($_POST['id_usuario'] ?? null);
    $usuario->set_apellido($_POST['apellido'] ?? null);
    $usuario->set_nombre($_POST['nombre'] ?? null);
    $usuario->set_correo($_POST['correo'] ?? null);
    $usuario->set_contra($_POST['contra'] ?? null);
    $usuario->set_rol_id($_POST['rol'] ?? null);
    // También podría llegar 'rol_nombre' para actualizar sesión, pero no se asigna al modelo

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $usuario->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_USUARIOS, 'Consulta general de usuarios');
                }
                break;

            case 'consulta_especifica':
                $respuesta = $usuario->realizar_consulta('consultar_usuario');
                break;

            case 'registrar':
                $respuesta = $usuario->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $nuevos = [
                        'nombre' => $usuario->get_nombre(),
                        'apellido' => $usuario->get_apellido(),
                        'correo' => $usuario->get_correo(),
                        'rol_id' => $usuario->get_rol_id()
                    ];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_USUARIOS, '', null, null, $nuevos);
                }
                break;

            case 'editar_usuario':
                // Obtener datos anteriores
                $tempUsuario = new Usuario();
                $tempUsuario->set_id_usuario($usuario->get_id_usuario());
                $datosAnteriores = $tempUsuario->realizar_consulta('consultar_usuario');
                $anterior = $datosAnteriores['estatus'] ? [
                    'nombre' => $datosAnteriores['datos']['nombre_usuario'] ?? '',
                    'apellido' => $datosAnteriores['datos']['apellido'] ?? '',
                    'correo' => $datosAnteriores['datos']['correo'] ?? '',
                    'rol_id' => $datosAnteriores['datos']['rol_id'] ?? ''
                ] : [];

                $respuesta = $usuario->realizar_consulta('editar_usuario');
                if ($respuesta['estatus']) {
                    $nuevo = [
                        'nombre' => $usuario->get_nombre(),
                        'apellido' => $usuario->get_apellido(),
                        'correo' => $usuario->get_correo(),
                        'rol_id' => $usuario->get_rol_id()
                    ];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_USUARIOS, '', null, $anterior, $nuevo);
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $tempUsuario = new Usuario();
                $tempUsuario->set_id_usuario($usuario->get_id_usuario());
                $datosUsuario = $tempUsuario->realizar_consulta('consultar_usuario');
                $anterior = $datosUsuario['estatus'] ? [
                    'nombre' => $datosUsuario['datos']['nombre_usuario'] ?? '',
                    'apellido' => $datosUsuario['datos']['apellido'] ?? '',
                    'correo' => $datosUsuario['datos']['correo'] ?? '',
                    'rol_id' => $datosUsuario['datos']['rol_id'] ?? ''
                ] : [];

                $respuesta = $usuario->realizar_consulta('eliminar_usuario');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_USUARIOS, '', null, $anterior, null);
                }
                break;

            case 'ultimo_id':
                $respuesta = $usuario->realizar_consulta('lastId');
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
            // Adaptar al formato esperado por el frontend original
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
if ($accion == "inicio") {
    require_once "vista/usuarios/usuario_vista.php";
}