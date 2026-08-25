<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\ayuda\Validador;
use haydee\modelo\Notificaciones;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_ANIO_FISCAL, $operacion);

    // El frontend envia 'id', a 'id_notificacion' para que coincida con la regla
    if (isset($_POST['id'])) {
        $_POST['id_notificacion'] = $_POST['id'];
    }

    $reglas = Notificaciones::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $notificaciones = new Notificaciones();

    // Asignacion de campos seguros
    $notificaciones->set_id_notificacion($_POST['id_notificacion'] ?? null); 
    $notificaciones->set_usuario_id($_SESSION['id_usuario'] ?? null);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consultar':
            $respuesta = $notificaciones->realizar_consulta('consultar_mis_notificaciones');
            break;

        case 'marcar_como_leido':
            $respuesta = $notificaciones->realizar_consulta('marcar_leida');
            if ($respuesta['estatus']) {
                // Eliminar la notificación de la sesion
                if (isset($_SESSION['notificaciones']) && is_array($_SESSION['notificaciones'])) {
                    $id_marcado = $notificaciones->get_id_notificacion();
                    foreach ($_SESSION['notificaciones'] as $index => $notif) {
                        if ($notif['id_notificacion'] == $id_marcado) {
                            unset($_SESSION['notificaciones'][$index]);
                            break;
                        }
                    }
                    // Reindexar array
                    $_SESSION['notificaciones'] = array_values($_SESSION['notificaciones']);
                }
            }
            break;

        case 'marcar_todas_leidas':
            $respuesta = $notificaciones->realizar_consulta('marcar_todas_leidas');

            
            if ($respuesta['estatus']) {
                $_SESSION['notificaciones'] = [];
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($notificaciones)) {$notificaciones->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

$placeholder_buscar = "Buscar notificación...";

// Carga de vistas
if (isset($accion) && $accion == "inicio") {
    require_once "vista/notificaciones/notificaciones_vista.php";
}

