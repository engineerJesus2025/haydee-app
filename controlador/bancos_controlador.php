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
                    Bitacora::registrar(CONSULTAR, GESTIONAR_BANCOS);
                }
                break;

            case 'registrar':
                $respuesta = $banco->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $nuevos = [
                        'nombre_banco' => $banco->get_nombre_banco(),
                        'numero_cuenta' => $banco->get_numero_cuenta(),
                        'codigo' => $banco->get_codigo(),
                        'telefono_afiliado' => $banco->get_telefono_afiliado(),
                        'rif' => $banco->get_rif()
                    ];
                    Bitacora::registrar(REGISTRAR, GESTIONAR_BANCOS,
                        null, null, $nuevos);
                }
                break;

            case 'consulta_especifica':
                $respuesta = $banco->realizar_consulta('consultar_banco');
                break;

            case 'modificar':
                // Obtener datos anteriores
                $tempBanco = new Banco();
                $tempBanco->set_id_banco($banco->get_id_banco());
                $datosAnteriores = $tempBanco->realizar_consulta('consultar_banco');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $banco->realizar_consulta('modificar');
                if ($respuesta['estatus']) {
                    $nuevo = [
                        'nombre_banco' => $banco->get_nombre_banco(),
                        'numero_cuenta' => $banco->get_numero_cuenta(),
                        'codigo' => $banco->get_codigo(),
                        'telefono_afiliado' => $banco->get_telefono_afiliado(),
                        'rif' => $banco->get_rif()
                    ];
                    Bitacora::registrar(MODIFICAR, GESTIONAR_BANCOS, null, $anterior, $nuevo);
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $tempBanco = new Banco();
                $tempBanco->set_id_banco($banco->get_id_banco());
                $datosAnteriores = $tempBanco->realizar_consulta('consultar_banco');
                $anterior = $datosAnteriores['estatus'] ? $datosAnteriores['datos'] : [];

                $respuesta = $banco->realizar_consulta('eliminar');
                if ($respuesta['estatus']) {
                    Bitacora::registrar(ELIMINAR, GESTIONAR_BANCOS, null, $anterior, null);
                }
                break;

            case 'ultimo_id':
                $respuesta = $banco->realizar_consulta('lastId');
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($banco)) {
                $banco->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
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