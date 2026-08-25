<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Rol;
use haydee\modelo\Usuario;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;

// Obtener lista de roles para la vista (solo si es necesario)
$rol_obj = new Rol();
$roles = $rol_obj->realizar_consulta('consultar');

// Instancia del modelo principal (usuario)

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_USUARIOS, $operacion);
    
    // Obtenemos las reglas de Validación
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
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
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

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($usuario, Modulo::GESTIONAR_USUARIOS);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consulta':
            $respuesta = $usuario->realizar_consulta('consultar');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'consultar_usuario':
            $respuesta = $usuario->realizar_consulta('consultar_usuario');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'registrar_usuario':
            $respuesta = $usuario->realizar_consulta('registrar_usuario');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
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
                $auditor->registrarAuditoria(Accion::MODIFICAR); 
            }
            break;

        case 'eliminar_usuario':
            // Obtener datos anteriores
            $auditor->capturarDatosAnteriores('consultar_usuario');

            $respuesta = $usuario->realizar_consulta('eliminar_usuario');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::ELIMINAR); 
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($usuario)) {$usuario->cerrar();}
    if (isset($rol_obj)) {$rol_obj->cerrar();}
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
        case 'correo':
            $correo = $_POST["correo"] ?? '';
            $id = !empty($_POST["id_usuario"]) ? $_POST["id_usuario"] : null;
            
            // Si NO es único, significa que YA EXISTE
            $existe = !$validadorBD->esUnico('usuarios', 'correo', $correo, 'id_usuario', $id);
            $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El correo ya está en uso' : 'Disponible'];
            break;

        case 'contrasenia_actual':
            // lógica de negocio la dejamos al modelo
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
                throw new HaydeeException('Faltan parámetros de Validación', HttpCodigo::BAD_REQUEST->value);
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

