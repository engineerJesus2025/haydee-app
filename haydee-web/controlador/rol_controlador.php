<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Rol;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_ROLES, $operacion);
    
    $reglas = Rol::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        // ID para evitar choques del campo UNIQUE al modificar
        $contexto = ['exclude_id' => $_POST['id_rol'] ?? null];
        $validador->validarConjunto($_POST, $reglas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $rol = new Rol();
    $rol->set_id_rol($_POST['id_rol'] ?? null);
    $rol->set_nombre($_POST['nombre'] ?? null);
    
    // Decodificamos el JSON de permisos
    $permisosJson = $_POST['permisos'] ?? '[]';
    $rol->set_permisos_asignados(json_decode($permisosJson, true) ?: []);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($rol, Modulo::GESTIONAR_ROLES);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consultar':
            $respuesta = $rol->realizar_consulta('consultar');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::CONSULTAR); }
            break;

        case 'consultar_rol':
            $respuesta = $rol->realizar_consulta('consultar_rol');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'registrar_rol':
            $respuesta = $rol->realizar_consulta('registrar_rol');
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::REGISTRAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'modificar_rol':
            $auditor->capturarDatosAnteriores('consultar_rol');
            $respuesta = $rol->realizar_consulta('modificar_rol');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::MODIFICAR); }
            break;

        case 'eliminar_rol':
            $auditor->capturarDatosAnteriores('consultar_rol');
            $respuesta = $rol->realizar_consulta('eliminar_rol');
            if ($respuesta['estatus']) { $auditor->registrarAuditoria(Accion::ELIMINAR); }
            break;
        case 'consultar_permisos_rol':
            $respuesta = $rol->realizar_consulta('consultar_permisos_asignados');
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($rol)) {$rol->cerrar();}
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
            $id = !empty($_POST["id_usuario"]) ? $_POST["id_usuario"] : null;
            
            $existe = !$validadorBD->esUnico('usuarios', 'correo', $correo, 'id_usuario', $id);
            $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El correo ya está en uso' : 'Disponible'];
            break;

        case 'contrasenia_actual':
            $usuario = new Usuario();
            $usuario->set_id_usuario($_POST['id_usuario']);
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

// CARGA DE DATOS PARA LA VISTA
$registros_modulos = [];
$registros_permisos_usuarios = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_ROLES);
    
    $rol = new Rol();
    $matriz = $rol->realizar_consulta('consultar_matriz_permisos');
    if ($matriz['estatus']) {
        $registros_modulos = $matriz['datos']['modulos'];
        $registros_permisos_usuarios = $matriz['datos']['permisos'];
    }
    $rol->cerrar();
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_ROLES);
$btn_nuevo = [
    'target'  => '#modal_roles',
    'texto'   => 'Nuevo Rol',
    'tooltip' => 'Registrar Nuevo Rol'
];
$placeholder_buscar = "Buscar rol...";

require_once "vista/roles/roles_vista.php";

