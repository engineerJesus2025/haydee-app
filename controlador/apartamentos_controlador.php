<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Apartamento;
use haydee\modelo\Habitantes;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_APARTAMENTOS, CONSULTAR);

// Instancia de modelos
$apartamento = new Apartamento();
$habitante = new Habitantes();

if (isset($_POST["operacion"])) {
    // Asignación masiva para Apartamento
    $apartamento->set_id_apartamento($_POST['id_apartamento'] ?? null);
    $apartamento->set_nro_apartamento($_POST['nro_apartamento'] ?? null);
    $apartamento->set_porcentaje_participacion($_POST['porcentaje_participacion'] ?? null);
    $apartamento->set_gas($_POST['gas'] ?? null);
    $apartamento->set_agua($_POST['agua'] ?? null);
    $apartamento->set_alquilado($_POST['alquilado'] ?? null);
    $apartamento->set_habitante_id($_POST['habitante_id'] ?? null);
    $apartamento->set_tipo_vinculo($_POST['tipo_vinculo'] ?? null);

    // Asignación masiva para Habitantes
    $habitante->set_id_habitante($_POST['id_habitante'] ?? null);
    $habitante->set_nombre($_POST['nombre'] ?? null);
    $habitante->set_apellido($_POST['apellido'] ?? null);
    $habitante->set_telefono($_POST['telefono'] ?? null);
    $habitante->set_correo($_POST['correo'] ?? null);
    $habitante->set_fecha_nacimiento($_POST['fecha_nacimiento'] ?? null);
    $habitante->set_sexo($_POST['sexo'] ?? null);
    $habitante->set_nuevo_apartamento_id($_POST['apartamento_id'] ?? null);
    $habitante->set_nuevo_tipo_vinculo($_POST['tipo_vinculo'] ?? null);

    $tipo = $_POST['tipo_cedula'] ?? '';
    $numero = $_POST['cedula'] ?? '';

    // Solo llamamos al método si tenemos datos
    if ($tipo !== '' && $numero !== '') {
        $habitante->set_cedula($tipo . $numero);
    }

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // INSTANCIAMOS DOS AUDITORES (Uno para cada modelo)
    $auditorApartamento = new GestorAuditoria($apartamento, GESTIONAR_APARTAMENTOS);
    $auditorHabitante = new GestorAuditoria($habitante, GESTIONAR_HABITANTES);

    try {
        switch ($operacion) {
            // ================= APARTAMENTOS =================
            case 'consulta':
                $respuesta = $apartamento->realizar_consulta('consultar_listado');
                if ($respuesta['estatus']) {
                    $auditorApartamento->registrarAuditoria('consultar');
                }
                break;

            case 'registrar':
                $respuesta = $apartamento->realizar_consulta('registrar_apartamento');
                if ($respuesta['estatus']) {
                    $auditorApartamento->registrarAuditoria('registrar');
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

            case 'modificar':
                $auditorApartamento->capturarDatosAnteriores('consultar_detalle_completo');
                $respuesta = $apartamento->realizar_consulta('modificar_apartamento');
                if ($respuesta['estatus']) { 
                    $auditorApartamento->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar':
                $auditorApartamento->capturarDatosAnteriores('consultar_detalle_completo');
                $respuesta = $apartamento->realizar_consulta('eliminar_apartamento');
                if ($respuesta['estatus']) { 
                    $auditorApartamento->registrarAuditoria('eliminar'); 
                }
                break;

            case 'ultimo_id':
                $respuesta = $apartamento->realizar_consulta('lastId');
                break;

            // ================= HABITANTES =================
            case 'consultar_habitantes':
                $result = $apartamento->realizar_consulta('consultar_detalle_completo');
                if ($result['estatus']) {
                    $respuesta = ['estatus' => true, 'datos' => $result['datos']['habitantes'] ?? []];
                } else {
                    $respuesta = $result;
                }
                break;

            case 'registrar_habitantes':
                $respuesta = $habitante->realizar_consulta('registrar_con_relacion');
                if ($respuesta['estatus']) {
                    $auditorHabitante->registrarAuditoria('registrar');
                }
                break;

            case 'consulta_especifica_habitante':
                $respuesta = $habitante->realizar_consulta('consultar_habitante');
                break;

            case 'modificar_habitantes':
                // Capturar, Ejecutar, Auditar
                $auditorHabitante->capturarDatosAnteriores('consultar_habitante');
                $respuesta = $habitante->realizar_consulta('modificar_con_relacion');
                
                if ($respuesta['estatus']) {
                    $auditorHabitante->registrarAuditoria('modificar');
                }
                break;

            case 'eliminar_habitantes':
                $auditorHabitante->capturarDatosAnteriores('consultar_habitante');
                $respuesta = $habitante->realizar_consulta('eliminar');
                
                if ($respuesta['estatus']) {
                    $auditorHabitante->registrarAuditoria('eliminar');
                }
                break;

            case 'ultimo_id_habitante':
                $respuesta = $habitante->realizar_consulta('lastId');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador apartamentos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($apartamento)) $apartamento->cerrar();
            if (isset($habitante)) $habitante->cerrar();
            
            Bitacora::cerrarConexionBitacora(); 
            
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

    try {
        switch ($validar) {
            case 'nro_apartamento':
                $apartamento->set_nro_apartamento($_POST["nro_apartamento"] ?? null);
                $respuesta = $apartamento->realizar_consulta('validar');
                break;

            case 'cedula':
                $habitante->set_cedula($_POST["cedula"] ?? null);
                $respuesta = $habitante->realizar_consulta('validar');
                break;

            case 'tipo_vinculo':
                $apartamento->set_id_apartamento($_POST["apartamento_id"] ?? null);
                $apartamento->set_tipo_vinculo($_POST["tipo_vinculo"] ?? null);
                $respuesta = $apartamento->realizar_consulta('verificar_vinculo');
                break;

            case 'correo':
                $habitante->set_correo($_POST["correo"] ?? null);
                $respuesta = $habitante->realizar_consulta('verificar_correo');
                break;

            case 'validar_clave_foranea':
                $tabla = $_POST["tabla"] ?? '';
                $campo = $_POST["nombre_clave"] ?? '';
                $valor = $_POST["valor"] ?? '';

                if (empty($tabla) || empty($campo) || empty($valor)) {
                    echo json_encode(['estatus' => false, 'mensaje' => 'Faltan parámetros']);
                    break;
                }

                $existe = false;
                if ($tabla === 'apartamentos') {
                    $existe = $apartamento->validarExistenciaExterna($tabla, $campo, $valor);
                } elseif ($tabla === 'habitantes') {
                    $existe = $habitante->validarExistenciaExterna($tabla, $campo, $valor);
                } else {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Tabla no soportada'];
                    break;
                }
                $respuesta = ['estatus' => $existe, 'mensaje' => 'OK'];
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        error_log("Error en validación AJAX: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_APARTAMENTOS);
}

// Cargar la vista
require_once "vista/apartamentos/apartamentos_vista.php";