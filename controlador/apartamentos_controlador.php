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

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    // Si la cadena tiene "habitante" (ej: registrar_habitantes, eliminar_habitantes), asignamos ese módulo
    $moduloAfectado = strpos($operacion, 'habitante') !== false ? Modulo::GESTIONAR_HABITANTES : Modulo::GESTIONAR_APARTAMENTOS;

    Sesiones::verificarPermisoAccion($moduloAfectado, $operacion);

    // Obtenemos las reglas de ambos modelos para la Operación actual
    $reglasApartamento = Apartamento::obtenerReglas($operacion);
    $reglasHabitante = Habitantes::obtenerReglas($operacion);
    
    // Unimos el tipo y el numero ANTES de validar para que el regex '/^[VE][0-9]+$/' funcione
    if (isset($_POST['tipo_cedula']) && isset($_POST['cedula'])) {
        $_POST['cedula'] = $_POST['tipo_cedula'] . $_POST['cedula'];
    }

    $reglasCompletas = array_merge($reglasApartamento, $reglasHabitante);

    if (!empty($reglasCompletas)) {
        $validador = new Validador();
        
        // Preparamos el contexto para las reglas UNIQUE (Evitar falsos positivos al modificar)
        $contexto = [];
        if (strpos($operacion, 'modificar') !== false) {
            // Buscamos si viene el ID de apartamento o el de habitante para excluirlo de la regla unique
            $contexto['exclude_id'] = $_POST['id_apartamento'] ?? $_POST['id_habitante'] ?? null;
        }

        $validador->validarConjunto($_POST, $reglasCompletas, $contexto);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // Instancia de modelos
    $apartamento = new Apartamento();
    $habitante = new Habitantes();

    // Asignacion masiva para Apartamento
    $apartamento->set_id_apartamento($_POST['id_apartamento'] ?? null);
    $apartamento->set_nro_apartamento($_POST['nro_apartamento'] ?? null);
    $apartamento->set_porcentaje_participacion($_POST['porcentaje_participacion'] ?? null);
    $apartamento->set_gas($_POST['gas'] ?? null);
    $apartamento->set_agua($_POST['agua'] ?? null);
    $apartamento->set_alquilado($_POST['alquilado'] ?? null);

    // Asignacion masiva para Habitantes
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

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // INSTANCIAMOS DOS AUDITORES (Uno para cada modelo)
    $auditorApartamento = new GestorAuditoria($apartamento, Modulo::GESTIONAR_APARTAMENTOS);
    $auditorHabitante = new GestorAuditoria($habitante, Modulo::GESTIONAR_HABITANTES);

    try {
        switch ($operacion) {
            // ================= APARTAMENTOS =================
            case 'consulta':
                $respuesta = $apartamento->realizar_consulta('consultar_listado');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditorApartamento->registrarAuditoria('consultar');
                }
                break;

            case 'registrar_apartamento':
                $respuesta = $apartamento->realizar_consulta('registrar_apartamento');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditorApartamento->registrarAuditoria('registrar');
                }
                break;

            case 'consulta_especifica':
                $respuesta = $apartamento->realizar_consulta('consultar_detalle_completo');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
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

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { 
                    $auditorApartamento->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar':
                $auditorApartamento->capturarDatosAnteriores('consultar_detalle_completo');
                $respuesta = $apartamento->realizar_consulta('eliminar_apartamento');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) { 
                    $auditorApartamento->registrarAuditoria('eliminar'); 
                }
                break;

            // ================= HABITANTES =================
            case 'consultar_habitantes':
                $respuesta = $apartamento->realizar_consulta('consultar_detalle_completo');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $respuesta = ['estatus' => true, 'datos' => $respuesta['datos']['habitantes'] ?? []];
                }
                break;

            case 'registrar_habitantes':
                $respuesta = $habitante->realizar_consulta('registrar_habitantes');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditorHabitante->registrarAuditoria('registrar');
                }
                break;

            case 'consulta_especifica_habitante':
                $respuesta = $habitante->realizar_consulta('consultar_habitante');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
                break;

            case 'modificar_habitantes':
                // Capturar, Ejecutar, Auditar
                $auditorHabitante->capturarDatosAnteriores('consultar_habitante');
                $respuesta = $habitante->realizar_consulta('modificar_habitantes');
                
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditorHabitante->registrarAuditoria('modificar');
                }
                break;

            case 'eliminar_habitantes':
                $auditorHabitante->capturarDatosAnteriores('consultar_habitante');
                $respuesta = $habitante->realizar_consulta('eliminar_habitantes');
                
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditorHabitante->registrarAuditoria('eliminar');
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador apartamentos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($apartamento)) $apartamento->cerrar();
            if (isset($habitante)) $habitante->cerrar();
            
            Bitacora::cerrarConexionBitacora(); 
            
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

    // Instanciamos al validador que se conecta a la BD
    $validadorBD = new ValidadorBD();

    try {
        switch ($validar) {
            case 'nro_apartamento':
                $nro = $_POST["nro_apartamento"] ?? '';
                $id = !empty($_POST["id_apartamento"]) ? $_POST["id_apartamento"] : null;
                
                // Si NO es unico, significa que YA EXISTE
                $existe = !$validadorBD->esUnico('apartamentos', 'nro_apartamento', $nro, 'id_apartamento', $id);
                $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'El número ya existe' : 'Disponible'];
                break;

            case 'cedula':
                $cedula = $_POST["cedula"] ?? '';
                // Union de la ceula para la Validación AJAX
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
                
                // Solo nos importa si intentan asignar un Propietario nuevo
                if ($tipo_vinculo === 'Propietario' && !empty($apartamento_id)) {
                    // Usamos nuestro nuevo metodo multi-condicional
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
                    echo json_encode(['estatus' => false, 'mensaje' => 'Faltan parámetros']);
                    break;
                }

                $tablasPermitidas = ['apartamentos', 'habitantes'];
                if (!in_array($tabla, $tablasPermitidas)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Tabla no soportada'];
                    break;
                }

                $existe = $validadorBD->existe($tabla, $campo, $valor);
                $respuesta = ['estatus' => $existe, 'mensaje' => 'OK'];
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en Validación AJAX: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(HttpCodigo::OK->value);
    }

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

// Cargar la vista
require_once "vista/apartamentos/apartamentos_vista.php";

