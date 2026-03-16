<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Pagos;
use haydee\modelo\Banco;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;
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

if (isset($_POST["operacion"])) {
    // Asignación masiva de propiedades esenciales
    $pagos->set_id_pago($_POST['id_pago'] ?? null);
    $pagos->set_id_detalle_pago($_POST['id_detalle_pago'] ?? null);
    $pagos->set_estado($_POST['estado'] ?? null);
    $pagos->set_observacion($_POST['observacion'] ?? null);
    $pagos->set_apartamento_id($_POST['apartamento_id'] ?? null);
    $pagos->set_mensualidad_id($_POST['mensualidad_id'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($pagos, GESTIONAR_PAGOS);

    try {
        switch ($operacion) {
            // ==================== CONSULTAS ====================
            case 'consulta':
                if ($esPropietario) {
                    $respuesta = $pagos->realizar_consulta('consultar_por_correo');
                } else {
                    $respuesta = $pagos->realizar_consulta('consultar_pagos');
                }
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_mensualidades':
                $respuesta = $pagos->realizar_consulta('consultarMensualidadPendiente');
                break;

            case 'consultar_mensualidad_especifica':
                $respuesta = $pagos->realizar_consulta('consultar_mensualidad_especifica');
                break;

            case 'consultar_detalles':
            case 'consulta_especifica':
                $respuesta = $pagos->realizar_consulta('consultar_pago_unico');
                break;

            case 'consulta_especifica_detalles':
                $respuesta = $pagos->realizar_consulta('consultar_detalle_unico');
                break;

            // ==================== REGISTRO ====================
            case 'registrar':
                if ($esPropietario) {
                    $pagos->set_estado('No verificado');
                }

                $configPagos = [
                    'campos' => ['fecha', 'monto', 'tipo_pago', 'monto_dolar'],
                    'bancarios' => ['banco_id', 'referencia'],
                    'imagenes' => 'imagen',
                    'metodo_pago_campo' => 'tipo_pago',
                    'metodos_con_archivo' => ['Transferencia', 'Pago Movil'],
                    'carpeta_imagenes' => 'pagos',
                    'campo_existente' => 'imagen_existente',
                    'indice_archivo_formato' => '/^imagen_(\d+)$/'
                ];
                $detalles = ConstructorDetalles::construirDetalles($_POST, $_FILES, $configPagos, false);
                $pagos->setDetallesTemp($detalles);

                $respuesta = $pagos->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    // Ocultamos los detalles al auditor para evitar colapsos
                    $pagos->setDetallesTemp(null);
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            // ==================== MODIFICAR ====================
            case 'modificar':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado para modificar'];
                    break;
                }

                // Usamos la consulta plana para la bitácora
                $auditor->capturarDatosAnteriores('consultar_cabecera_pago');

                $configPagos = [
                    'campos' => ['fecha', 'monto', 'tipo_pago', 'monto_dolar'],
                    'bancarios' => ['banco_id', 'referencia'],
                    'imagenes' => 'imagen',
                    'metodo_pago_campo' => 'tipo_pago',
                    'metodos_con_archivo' => ['Transferencia', 'Pago Movil'],
                    'carpeta_imagenes' => 'pagos',
                    'campo_existente' => 'imagen_existente',
                    'indice_archivo_formato' => '/^imagen_(\d+)$/'
                ];

                $detalles = ConstructorDetalles::construirDetalles($_POST, $_FILES, $configPagos, true);
                $pagos->setDetallesTemp($detalles);

                $respuesta = $pagos->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    // Ocultamos los detalles al auditor
                    $pagos->setDetallesTemp(null);
                    $auditor->registrarAuditoria('modificar');
                }
                break;

            // ==================== ELIMINAR ====================
            case 'eliminar':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                    break;
                }

                // Usamos la consulta plana para la bitácora
                $auditor->capturarDatosAnteriores('consultar_cabecera_pago');

                $respuesta = $pagos->realizar_consulta('eliminar_pago');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('eliminar');
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador pagos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($pagos)) { $pagos->cerrar(); }
            if (isset($banco)) { $banco->cerrar(); }
            if (isset($apartamento)) { $apartamento->cerrar(); }
            
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
            case 'referencia':
                $pagos->set_referencia($_POST["referencia"] ?? null);
                $respuesta = $pagos->realizar_consulta('validar_referencia');
                break;
            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no implementada'];
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
    $apartamento->set_correo($_SESSION["usuario"]);
    $registro_apartamento = $apartamento->realizar_consulta('obtener_apartamentos_por_correo')['datos'] ?? [];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_PAGOS);
}

require_once "vista/pagos/pagos_vista.php";