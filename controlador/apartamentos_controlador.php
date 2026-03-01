<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Apartamento;
use haydee\modelo\Habitantes;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_APARTAMENTOS, CONSULTAR);

// Instancia de modelos
$apartamento = new Apartamento();
$habitante = new Habitantes();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

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

    $tipo = $_POST['tipo_cedula'] ?? '';
    $numero = $_POST['cedula'] ?? '';

    // Solo llamamos al método si tenemos datos
    if ($tipo !== '' && $numero !== '') {
        $habitante->set_cedula($tipo . $numero);
    }

    // Datos de la nueva relación (si cambia)
    $habitante->set_nuevo_apartamento_id($_POST['apartamento_id'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            // ================= APARTAMENTOS =================
            case 'consulta':
                $respuesta = $apartamento->realizar_consulta('consultar_listado');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_APARTAMENTOS, 'Consulta general de apartamentos');
                }
                break;

            // case 'consulta_select':
            //     $respuesta = $apartamento->realizar_consulta('consultar_listado');
            //     break;

            case 'registrar':
                $respuesta = $apartamento->realizar_consulta('registrar_apartamento');
                if ($respuesta['estatus']) {
                    $nuevos = [
                        'nro_apartamento' => $apartamento->get_nro_apartamento(),
                        'porcentaje_participacion' => $apartamento->get_porcentaje_participacion(),
                        'gas' => $apartamento->get_gas(),
                        'agua' => $apartamento->get_agua(),
                        'alquilado' => $apartamento->get_alquilado()
                    ];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_APARTAMENTOS,
                        '',
                        null, null, $nuevos);
                }
                break;

            case 'consulta_especifica':
                $respuesta = $apartamento->realizar_consulta('consultar_detalle_completo');
                if ($respuesta['estatus']) {
                    // Reestructurar para mantener compatibilidad con el frontend
                    $datos = $respuesta['datos'];
                    $respuesta = [
                        'estatus' => true,
                        'apartamento' => $datos,
                        'detalles' => $datos['habitantes'] ?? []
                    ];
                }
                break;

            case 'modificar':
                // Obtener datos anteriores del apartamento
                $tempApart = new Apartamento();
                $tempApart->set_id_apartamento($apartamento->get_id_apartamento());
                $datosAnteriores = $tempApart->realizar_consulta('consultar_detalle_completo');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $apartamento->realizar_consulta('modificar_apartamento');
                if ($respuesta['estatus']) {
                    $nuevo = [
                        'nro_apartamento' => $apartamento->get_nro_apartamento(),
                        'porcentaje_participacion' => $apartamento->get_porcentaje_participacion(),
                        'gas' => $apartamento->get_gas(),
                        'agua' => $apartamento->get_agua(),
                        'alquilado' => $apartamento->get_alquilado()
                    ];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_APARTAMENTOS,
                        '',
                        null, $anterior, $nuevo);
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $tempApart = new Apartamento();
                $tempApart->set_id_apartamento($apartamento->get_id_apartamento());
                $datosAnteriores = $tempApart->realizar_consulta('consultar_detalle_completo');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $apartamento->realizar_consulta('eliminar_apartamento');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_APARTAMENTOS,
                       '',
                        null, $anterior, null);
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
                // Registrar habitante
                $respuesta = $habitante->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $idHabitante = $respuesta['lastId'];
                    // Asignar al apartamento
                    $apartamento->set_habitante_id($idHabitante);
                    $apartamento->set_id_apartamento($_POST['apartamento_id'] ?? null);
                    $apartamento->set_tipo_vinculo($_POST['tipo_vinculo'] ?? null);
                    $resAsignar = $apartamento->realizar_consulta('asignar_habitante');
                    if ($resAsignar['estatus']) {
                        $respuesta = ['estatus' => true, 'mensaje' => 'Habitante y relación registrados correctamente'];
                        Bitacora::registrar(REGISTRAR, GESTIONAR_HABITANTES,
                            $habitante->get_nombre() . ' ' . $habitante->get_apellido()
                        );
                    } else {
                        // Si falla la asignación, podríamos eliminar el habitante, pero por simplicidad avisamos
                        $respuesta = ['estatus' => false, 'mensaje' => 'Habitante registrado, pero error al asignar al apartamento: ' . $resAsignar['mensaje']];
                    }
                }
                break;

            case 'consulta_especifica_habitante':
                $respuesta = $habitante->realizar_consulta('consultar_habitante');
                break;

            case 'modificar_habitantes':
                $respuesta = $habitante->realizar_consulta('modificar_con_relacion');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_HABITANTES,
                        $habitante->get_nombre() . ' ' . $habitante->get_apellido()
                    );
                }
                break;

            case 'eliminar_habitantes':
                // Obtener datos para bitácora
                $copia = clone $habitante;
                $datosHab = $copia->realizar_consulta('consultar_habitante');
                $info = $datosHab['estatus'] ? ($datosHab['datos']['nombre'] . ' ' . $datosHab['datos']['apellido']) : '';

                $respuesta = $habitante->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_HABITANTES, $info);
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
    }

    echo json_encode($respuesta);
    exit;
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

// Cargar la vista
require_once "vista/apartamentos/apartamentos_vista.php";