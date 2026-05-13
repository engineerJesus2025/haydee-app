<?php
use haydee\enums\Modulo;
use haydee\enums\Accion;

use haydee\servicios\Sesiones;
use haydee\modelo\Rol;
use haydee\modelo\Usuario;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::autorizarAcceso(Modulo::GESTIONAR_USUARIOS, Accion::CONSULTAR);

// Obtener lista de roles para la vista (solo si es necesario)
$rol_obj = new Rol();
$roles = $rol_obj->realizar_consulta('consultar');

// Instancia del modelo principal (usuario)

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_USUARIOS, $operacion);
    
    // 1. Obtenemos las reglas de Validación
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
            $codigoHttp = $validador->tieneError404() ? 404 : 400;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // Instancia del modelo principal
    $usuario = new Usuario();

    // Asignacion masiva (Datos ya validados)
    $usuario->set_id_usuario($_POST['id_usuario'] ?? null);
    $usuario->set_apellido($_POST['apellido'] ?? null);
    $usuario->set_nombre($_POST['nombre'] ?? null);
    $usuario->set_correo($_POST['correo'] ?? null);
    $usuario->set_contra($_POST['contra'] ?? null);
    $usuario->set_rol_id($_POST['rol_id'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($usuario, Modulo::GESTIONAR_USUARIOS);
    
    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $usuario->realizar_consulta('consultar');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::CONSULTAR);
                }
                break;

            case 'consultar_usuario':
                $respuesta = $usuario->realizar_consulta('consultar_usuario');
                http_response_code($respuesta['estatus'] ? 200 : 404);
                break;

            case 'registrar_usuario':
                $respuesta = $usuario->realizar_consulta('registrar_usuario');

                http_response_code($respuesta['estatus'] ? 201 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::REGISTRAR);
                }
                break;

            case 'modificar_usuario':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_usuario');

                $respuesta = $usuario->realizar_consulta('modificar_usuario');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    if ($usuario->get_id_usuario() == $_SESSION["id_usuario"]) {
                        $_SESSION["nombre_completo"] = $usuario->get_nombre() . " " . $usuario->get_apellido();
                        $respuesta["esMismoUsuario"] = true;
                    }
                    $auditor->registrarAuditoria(Accion::MODIFICAR); 
                }
                break;

            case 'eliminar_usuario':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_usuario');

                $respuesta = $usuario->realizar_consulta('eliminar_usuario');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria(Accion::ELIMINAR); 
                }
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explÃ­citamente
            if (isset($usuario)) {
                $usuario->cerrar();
            }
            if (isset($rol_obj)) {
                $rol_obj->cerrar();
            }
            if (isset($usuario)) {
                $usuario->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexiÃ³n de seguridad

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
                
                // Si NO es Ãºnico, significa que YA EXISTE
                $existe = !$validadorBD->esUnico('usuarios', 'correo', $correo, 'id_usuario', $id);
                $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El correo ya estÃ¡ en uso' : 'Disponible'];
                break;

            case 'contrasenia_actual':
                // Esta lÃ³gica de negocio pura sÃ­ la dejamos delegada al modelo
                $usuario = new Usuario();
                $usuario->set_id_usuario($_POST['id_usuario']);
                $datosUsuario = $usuario->realizar_consulta('consultar_usuario');
                $contraIngresada = $_POST['contra'] ?? '';
                
                $coincide = false;
                if ($datosUsuario['estatus'] && isset($datosUsuario['datos']['contrasenia'])) {
                    $coincide = password_verify($contraIngresada, $datosUsuario['datos']['contrasenia']);
                }
                echo json_encode($coincide); // Tu JS espera un booleano directo aquÃ­
                exit;

            case 'validar_clave_foranea':
                $tabla = $_POST['tabla'] ?? '';
                $campo = $_POST['nombre_clave'] ?? '';
                $valor = $_POST['valor'] ?? '';

                if (empty($tabla) || empty($campo) || empty($valor)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parÃ¡metros de Validación'];
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
            http_response_code(400);
            $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en Validación AJAX Usuario: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(200);
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_USUARIOS);
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_USUARIOS);
$btn_nuevo = [
    'target'  => '#modal_usuario',
    'texto'   => 'Nuevo Usuario',
    'tooltip' => 'Registrar Nuevo Usuario'
];
$placeholder_buscar = "Buscar usuario...";

require_once "vista/usuarios/usuario_vista.php";
