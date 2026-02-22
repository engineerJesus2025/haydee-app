<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Pagos;
use haydee\modelo\Banco;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\ayuda\ConstructorDetalles;

// Verificar sesión
Sesiones::verificarSesion();

// Determinar rol
$esPropietario = (isset($_SESSION["rol"]) && $_SESSION["rol"] == "Propietario");

// Si no es propietario, verificar permiso
if (!$esPropietario) {
    Sesiones::verificarPermiso(GESTIONAR_PAGOS, CONSULTAR);
}

// Instancia del modelo principal
$pagos = new Pagos();

// Modelos auxiliares para selects
$banco = new Banco();
$apartamento = new Apartamento();

// Datos para la vista (se cargan al final)
$registro_banco = [];
$registro_apartamento = [];

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

    // Asignación masiva de propiedades de Pagos (todas las que puedan llegar)
    $pagos->set_id_pago($_POST['id_pago'] ?? null);
    $pagos->set_id_detalle_pago($_POST['id_detalle_pago'] ?? null);
    $pagos->set_estado($_POST['estado'] ?? null);
    $pagos->set_observacion($_POST['observacion'] ?? null);
    $pagos->set_fecha($_POST['fecha'] ?? null); // puede ser array
    $pagos->set_monto($_POST['monto'] ?? null); // puede ser array
    $pagos->set_monto_dolar($_POST['monto_dolar'] ?? null); // puede ser array
    $pagos->set_tipo_pago($_POST['tipo_pago'] ?? null); // puede ser array
    $pagos->set_referencia($_POST['referencia'] ?? null); // puede ser array
    $pagos->set_banco_id($_POST['banco_id'] ?? null); // puede ser array
    $pagos->set_mensualidad_id($_POST['mensualidad_id'] ?? null); // puede ser array o escalar

    // Procesar imágenes si se subieron
    $imagenes = [];
    if (isset($_FILES['imagen'])) {
        $archivos = $_FILES['imagen'];
        // Si es un solo archivo, convertirlo a array para uniformidad
        if (!is_array($archivos['name'])) {
            $archivos = [
                'name' => [$archivos['name']],
                'type' => [$archivos['type']],
                'tmp_name' => [$archivos['tmp_name']],
                'error' => [$archivos['error']],
                'size' => [$archivos['size']]
            ];
        }
        foreach ($archivos['name'] as $i => $nombre) {
            $nombreImagen = GestorImagenes::subir($archivoIndividual, 'pagos');
            $imagenes[] = $nombreImagen ?: null;
        }
    }
    $pagos->set_imagen($imagenes);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try {
        switch ($operacion) {
            // ==================== CONSULTAS GENERALES ====================
            case 'consulta':
                if ($esPropietario) {
                    $respuesta = $pagos->realizar_consulta('consultar_por_correo');
                } else {
                    $respuesta = $pagos->realizar_consulta('consultar_pagos');
                }
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_PAGOS, 'Consulta de pagos');
                }
                break;

            case 'consultar_mensualidades':
                $apartamento_id = $_POST['apartamento_id'] ?? null;
                if ($apartamento_id) {
                    $respuesta = $pagos->realizar_consulta('consultar_mensualidades_pendientes_por_apartamento', $apartamento_id);
                } else {
                    $respuesta = ['estatus' => false, 'mensaje' => 'ID de apartamento no proporcionado'];
                }
                break;

            case 'consultar_mensualidad_especifica':
                $mensualidad_id = $_POST['mensualidad_id'] ?? null;
                $pagos->set_mensualidad_id($mensualidad_id);
                $respuesta = $pagos->realizar_consulta('consultar_mensualidad_especifica');
                break;

            case 'consultar_detalles':
                $pagos->set_id_pago($_POST['id_pago'] ?? null);
                $result = $pagos->realizar_consulta('consultar_pago_unico');
                if ($result['estatus']) {
                    // En el modelo, consultar_pago_unico devuelve un solo registro; los detalles están en 'datos'.
                    // Si hay múltiples detalles, habría que agrupar, pero por ahora asumimos uno.
                    $respuesta = ['estatus' => true, 'datos' => $result['datos'] ?? []];
                } else {
                    $respuesta = $result;
                }
                break;

            case 'consulta_especifica':
                $pagos->set_id_pago($_POST['id_pago'] ?? null);
                $respuesta = $pagos->realizar_consulta('consultar_pago_unico');
                break;

            case 'consulta_especifica_detalles':
                $pagos->set_id_detalle_pago($_POST['id_detalle_pago'] ?? null);
                $respuesta = $pagos->realizar_consulta('consultar_detalle_unico');
                break;

            // ==================== OPERACIONES DE PAGO (ADMIN) ====================
            case 'registrar_old':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                    break;
                }
                $respuesta = $pagos->realizar_consulta('registrar_pago_con_detalles');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_PAGOS, 'Pago registrado ID: ' . ($respuesta['id'] ?? ''));
                }
                break;

            case 'modificar_old':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                    break;
                }
                $respuesta = $pagos->realizar_consulta('editar_pago_con_detalles');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_PAGOS, 'Pago modificado ID: ' . $pagos->get_id_pago());
                }
                break;

            case 'eliminar':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                    break;
                }
                // Obtener datos para bitácora
                $copia = clone $pagos;
                $datosPago = $copia->realizar_consulta('consultar_pago_unico');
                $info = $datosPago['estatus'] ? ('Pago ID: ' . $pagos->get_id_pago()) : '';

                $respuesta = $pagos->realizar_consulta('eliminar_pago');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_PAGOS, $info);
                }
                break;

            // ==================== OPERACIONES DE DETALLES (ADMIN) ====================
            case 'registrar_detalles':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                    break;
                }
                $respuesta = $pagos->realizar_consulta('registrar_detalle');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_PAGOS, 'Detalle agregado a pago ID: ' . $pagos->get_id_pago());
                }
                break;

            case 'modificar_detalles':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                    break;
                }
                $respuesta = $pagos->realizar_consulta('editar_detalle');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_PAGOS, 'Detalle modificado ID: ' . $pagos->get_id_detalle_pago());
                }
                break;

            case 'eliminar_detalles':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                    break;
                }
                $respuesta = $pagos->realizar_consulta('eliminar_detalle');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_PAGOS, 'Detalle eliminado ID: ' . $pagos->get_id_detalle_pago());
                }
                break;

            // ==================== ÚLTIMO ID ====================
            case 'ultimo_id':
                $respuesta = $pagos->realizar_consulta('lastId');
                break;

            case 'ultimo_id_detalle':
                $respuesta = $pagos->realizar_consulta('lastIdDetalle');
                break;

            // NUEVOS:
            case 'registrar':
            case 'modificar':
                // Los propietarios SI pueden registrar (reportar), pero NO pueden modificar pagos ya hechos
                if ($esPropietario && $operacion === 'modificar') {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado para modificar'];
                    break;
                }

                // Asignar propiedades de cabecera
                $pagos->set_apartamento_id($_POST['apartamento_id'] ?? null);
                $pagos->set_mensualidad_id($_POST['mensualidad_id'] ?? null);
                
                // Si es propietario, forzamos el estado para que un Admin lo apruebe luego
                if ($esPropietario) {
                    $pagos->set_estado('No verificado');
                } else {
                    $pagos->set_estado($_POST['estado'] ?? 'PENDIENTE');
                }
                
                $pagos->set_observacion($_POST['observacion'] ?? '');
                $pagos->set_monto_mensualidad($_POST['monto_mensualidad'] ?? 0);

                // Determinar si es edición (para el Helper de imágenes)
                $esEdicion = ($operacion === 'modificar');
                if ($esEdicion) {
                    $pagos->set_id_pago($_POST['id_pago']);
                }

                // Usamoshelper (Revisar que ConstructorDetalles::ConstruirDetallesPagos exista y esté configurado para Pagos)
                // Si no tienes ese método específico en el Helper, puedes usar el constructor base así:
                $configPagos = [
                    'campos' => ['fecha', 'monto', 'tipo_pago', 'monto_dolar'],
                    'bancarios' => ['banco_id', 'referencia'],
                    'imagenes' => 'imagen',
                    'metodo_pago_campo' => 'tipo_pago',
                    'metodos_con_archivo' => ['Transferencia', 'Pago Movil'],
                    'carpeta_imagenes' => 'pagos',
                    'campo_existente' => 'imagen_existente',
                    'indice_archivo_formato' => '/^imagen_(\d+)$/' // Requerirá que el JS envíe 'imagen_0', 'imagen_1'...
                ];
                $detalles = ConstructorDetalles::construirDetalles($_POST, $_FILES, $configPagos, $esEdicion);
                
                $pagos->setDetallesTemp($detalles);

                if ($operacion === 'registrar') {
                    $respuesta = $pagos->realizar_consulta('registrar');
                    $accionBitacora = REGISTRAR;
                } else {
                    $respuesta = $pagos->realizar_consulta('editar');
                    $accionBitacora = MODIFICAR;
                }

                if ($respuesta['estatus']) {
                    Bitacora::registrar($accionBitacora, GESTIONAR_PAGOS, "Procesamiento atómico de Pago ID: " . ($respuesta['id'] ?? $pagos->get_id_pago()));
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador pagos: " . $e->getMessage());
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
            case 'referencia':
                $pagos->set_referencia($_POST["referencia"] ?? null);
                $respuesta = $pagos->realizar_consulta('validar_referencia');
                break;
            case 'validar_clave_foranea':
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no implementada'];
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

// Carga de datos para la vista
$registro_banco = $banco->realizar_consulta('consultar')['datos'] ?? [];
if (!$esPropietario) {
    $registro_apartamento = $apartamento->realizar_consulta('consultar_listado')['datos'] ?? [];
} else {
    $registro_apartamento = $apartamento->consultar_propietario($_SESSION["usuario"])['datos'] ?? [];
}

// Incluir la vista correspondiente
if (!$esPropietario) {
    require_once "vista/pagos/pagos_vista.php";
} else {
    require_once "vista/pagos/pagos_propietarios_vista.php";
}
