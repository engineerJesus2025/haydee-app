<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\AnioFiscal;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\servicios\GestorAuditoria; 
// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_ANIO_FISCAL, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    $reglas = AnioFiscal::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    // Instancia del modelo
    $anioFiscal = new AnioFiscal();
    // Asignación masiva
    $anioFiscal->set_id_anio_fiscal($_POST['id_anio_fiscal'] ?? null);
    $anioFiscal->set_fecha_inicio($_POST['fecha_inicio'] ?? null);
    $anioFiscal->set_fecha_cierre($_POST['fecha_cierre'] ?? null);
    $anioFiscal->set_estado($_POST['estado'] ?? null);
    $anioFiscal->set_descripcion($_POST['descripcion'] ?? null);

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($anioFiscal, GESTIONAR_ANIO_FISCAL);

    try {
        switch ($operacion) {
            case 'consultar_anios_fiscales':
                $respuesta = $anioFiscal->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'registrar':
                $respuesta = $anioFiscal->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'consulta_especifica':
                $respuesta = $anioFiscal->realizar_consulta('consultar_anio_fiscal');
                break;

            case 'modificar':
                // 1. El auditor toma una foto de cómo está el registro antes de tocarlo
                $auditor->capturarDatosAnteriores('consultar_anio_fiscal');
                
                // 2. El controlador manda a modificar
                $respuesta = $anioFiscal->realizar_consulta('modificar');
                
                // 3. Si todo salió bien, el auditor registra el cambio
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('modificar');
                }
                break;
            case 'eliminar':
                // 1. Tomamos foto previa
                $auditor->capturarDatosAnteriores('consultar_anio_fiscal');

                // 2. Ejecutamos eliminación lógica
                $respuesta = $anioFiscal->realizar_consulta('eliminar');
                
                // 3. Registramos en bitácora
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
            $anioFiscal->cerrar();
            Bitacora::cerrarConexionBitacora(); 
            
            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Si la petición NO es por POST (es decir, el usuario entró al módulo desde el menú o presionó F5)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Le avisamos al gestor que permita auditar la próxima consulta de este módulo
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_ANIO_FISCAL);
}

// Cargar la vista
require_once "vista/anio_fiscal/anio_fiscal_vista.php";