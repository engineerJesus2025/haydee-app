<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\AnioFiscal;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_ANIO_FISCAL, CONSULTAR);

// Instancia del modelo
$anioFiscal = new AnioFiscal();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

    // Asignación masiva de campos que pueden llegar
    $anioFiscal->set_id_anio_fiscal($_POST['id_anio_fiscal'] ?? null);
    $anioFiscal->set_fecha_inicio($_POST['fecha_inicio'] ?? null);
    $anioFiscal->set_fecha_cierre($_POST['fecha_cierre'] ?? null);
    $anioFiscal->set_estado($_POST['estado'] ?? null);
    $anioFiscal->set_descripcion($_POST['descripcion'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try{
        switch ($operacion) {
            case 'consultar_anios_fiscales':
                $respuesta = $anioFiscal->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_ANIO_FISCAL, 'Consulta general de años fiscales');
                }
                break;

            case 'registrar':
                $respuesta = $anioFiscal->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_ANIO_FISCAL,
                        $anioFiscal->get_fecha_inicio() . ' - ' . $anioFiscal->get_estado()
                    );
                }
                break;

            case 'consulta_especifica':
                $respuesta = $anioFiscal->realizar_consulta('consultar_anio_fiscal');
                // Bitácora opcional (se puede omitir)
                break;

            case 'modificar':
                $respuesta = $anioFiscal->realizar_consulta('editar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_ANIO_FISCAL,
                        $anioFiscal->get_fecha_inicio() . ' - ' . $anioFiscal->get_estado()
                    );
                }
                break;

            case 'eliminar':
                // Obtener datos para la bitácora antes de eliminar
                $copia = clone $anioFiscal;
                $datosAnio = $copia->realizar_consulta('consultar_anio_fiscal');
                $infoAnio = '';
                if ($datosAnio['estatus']) {
                    $datos = $datosAnio['datos'];
                    $infoAnio = ($datos['fecha_inicio'] ?? '') . ' - ' . ($datos['estado'] ?? '');
                }

                $respuesta = $anioFiscal->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_ANIO_FISCAL, $infoAnio);
                }
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

// Cargar la vista
require_once "vista/anio_fiscal/anio_fiscal_vista.php";