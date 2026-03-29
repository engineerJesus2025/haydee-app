<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Usuario;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_USUARIOS, CONSULTAR);

// Obtener lista de roles para la vista (solo si es necesario)
$rol_obj = new Rol();
$roles = $rol_obj->realizar_consulta('consultar');

// Instancia del modelo principal (usuario)

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    
    // 1. Obtenemos las reglas de validación
    $reglas = Usuario::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        
        // Contexto para ignorar el ID actual al modificar correo (Unique)
        $contexto = [];
        if ($operacion === 'modificar_usuario') {
            $contexto['exclude_id'] = $_POST['id_usuario'] ?? $_SESSION['id_usuario'] ?? null;
        }

        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // Instancia del modelo principal
    $usuario = new Usuario();

    // Asignación masiva (Datos ya validados)
    $usuario->set_id_usuario($_POST['id_usuario'] ?? null);
    $usuario->set_apellido($_POST['apellido'] ?? null);
    $usuario->set_nombre($_POST['nombre'] ?? null);
    $usuario->set_correo($_POST['correo'] ?? null);
    $usuario->set_contra($_POST['contra'] ?? null);
    $usuario->set_rol_id($_POST['rol_id'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($usuario, GESTIONAR_USUARIOS);
    
    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $usuario->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_usuario':
                $respuesta = $usuario->realizar_consulta('consultar_usuario');
                break;

            case 'registrar_usuario':
                $respuesta = $usuario->realizar_consulta('registrar_usuario');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'modificar_usuario':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_usuario');

                $respuesta = $usuario->realizar_consulta('modificar_usuario');
                if ($respuesta['estatus']) {
                    if ($usuario->get_id_usuario() == $_SESSION["id_usuario"]) {
                        $_SESSION["nombre_completo"] = $usuario->get_nombre() . " " . $usuario->get_apellido();
                        $respuesta["esMismoUsuario"] = true;
                    }
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar_usuario':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_usuario');

                $respuesta = $usuario->realizar_consulta('eliminar_usuario');
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
            if (isset($usuario)) {
                $usuario->cerrar();
            }
            if (isset($rol_obj)) {
                $rol_obj->cerrar();
            }
            if (isset($usuario)) {
                $usuario->cerrar();
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
            case 'correo':
                $correo = $_POST["correo"] ?? '';
                $id = !empty($_POST["id_usuario"]) ? $_POST["id_usuario"] : null;
                
                // Si NO es único, significa que YA EXISTE
                $existe = !$validadorBD->esUnico('usuarios', 'correo', $correo, 'id_usuario', $id);
                $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El correo ya está en uso' : 'Disponible'];
                break;

            case 'contrasenia_actual':
                // Esta lógica de negocio pura sí la dejamos delegada al modelo
                $usuario = new Usuario();
                $usuario->set_id_usuario($_POST['id_usuario']);
                $datosUsuario = $usuario->realizar_consulta('consultar_usuario');
                $contraIngresada = $_POST['contra'] ?? '';
                
                $coincide = false;
                if ($datosUsuario['estatus'] && isset($datosUsuario['datos']['contrasenia'])) {
                    $coincide = password_verify($contraIngresada, $datosUsuario['datos']['contrasenia']);
                }
                echo json_encode($coincide); // Tu JS espera un booleano directo aquí
                exit;

            case 'validar_clave_foranea':
                $tabla = $_POST['tabla'] ?? '';
                $campo = $_POST['nombre_clave'] ?? '';
                $valor = $_POST['valor'] ?? '';

                if (empty($tabla) || empty($campo) || empty($valor)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parámetros de validación'];
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
        }
    } catch (Exception $e) {
        error_log("Error en validación AJAX Usuario: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_USUARIOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_USUARIOS);
require_once "vista/usuarios/usuario_vista.php";