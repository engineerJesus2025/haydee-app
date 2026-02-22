<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\Banco;
use haydee\modelo\Bitacora;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_BANCOS, CONSULTAR);

// Instancia del modelo
$banco = new Banco();

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');

    // Asignación masiva de campos que pueden llegar
    $banco->set_id_banco($_POST['id_banco'] ?? null);
    $banco->set_nombre_banco($_POST['nombre_banco'] ?? null);
    $banco->set_codigo($_POST['codigo'] ?? null);
    $banco->set_numero_cuenta($_POST['numero_cuenta'] ?? null);
    $banco->set_telefono_afiliado($_POST['telefono_afiliado'] ?? null);
    $banco->set_rif($_POST['rif'] ?? null);

    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    try{
        switch ($operacion) {
            case 'consulta':
                $respuesta = $banco->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(CONSULTAR, GESTIONAR_BANCOS, 'Consulta general de bancos');
                    echo json_encode(['datos' => $respuesta['datos']]);
                } else {
                    echo json_encode(['datos' => [], 'error' => $respuesta['mensaje']]);
                }
                exit; // Salir para no ejecutar el echo final

            case 'registrar':
                $respuesta = $banco->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(REGISTRAR, GESTIONAR_BANCOS,
                        $banco->get_nombre_banco() . ' (' . $banco->get_numero_cuenta() . ')'
                    );
                }
                break;

            case 'consulta_especifica':
                $respuesta = $banco->realizar_consulta('consultar_banco');
                break;

            case 'editar':
                $respuesta = $banco->realizar_consulta('editar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(MODIFICAR, GESTIONAR_BANCOS,
                        $banco->get_nombre_banco() . ' (' . $banco->get_numero_cuenta() . ')'
                    );
                }
                break;

            case 'eliminar':
                // Obtener datos para la bitácora antes de eliminar
                $copia = clone $banco;
                $datosBanco = $copia->realizar_consulta('consultar_banco');
                $infoBanco = '';
                if ($datosBanco['estatus']) {
                    $datos = $datosBanco['datos'];
                    $infoBanco = ($datos['nombre_banco'] ?? '') . ' (' . ($datos['numero_cuenta'] ?? '') . ')';
                }

                $respuesta = $banco->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_BANCOS, $infoBanco);
                }
                break;

            case 'ultimo_id':
                $respuesta = $banco->realizar_consulta('lastId');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }

        // Para las acciones que no son 'consulta', enviamos JSON aquí
        echo json_encode($respuesta);

    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        echo json_encode(['estatus' => false, 'mensaje' => 'Error interno del servidor']);
    }
    exit;
}

// Validaciones AJAX (para verificar número de cuenta)
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');

    $validar = $_POST["validar"];
    if ($validar == "numero_cuenta") {
        $banco->set_numero_cuenta($_POST["numero_cuenta"] ?? null);
        $resultado = $banco->realizar_consulta('validar');

        if ($resultado['estatus']) {
            $existe = $resultado['existe'] ?? false;
            echo json_encode([
                'estatus' => true,
                'busqueda' => $existe ? 'numero_cuenta' : null
            ]);
        } else {
            echo json_encode($resultado); // En caso de error interno
        }
    } else {
        echo json_encode(['estatus' => false, 'mensaje' => 'Validación no reconocida']);
    }
    exit;
}

// Cargar la vista
require_once "vista/bancos/bancos_vista.php";