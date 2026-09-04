<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\enums\TipoEventoNotificacion;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\ayuda\GestorImagenes;
use haydee\modelo\CarteleraVirtual;
use haydee\modelo\Usuario;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorNotificaciones;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_CARTELERA_VIRTUAL, $operacion);

    // Validamos segun la Operación
    $reglas = CarteleraVirtual::obtenerReglas($operacion);

    if (!isset($_POST['usuario_id'])) {
        $_POST['usuario_id'] = $_SESSION['id_usuario'] ?? null;
    }

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
        }
    }

    $cartelera = new CarteleraVirtual();
    // Asignacion masiva
    $cartelera->set_id_cartelera($_POST['id_cartelera'] ?? null);
    $cartelera->set_titulo($_POST['titulo'] ?? null);
    $cartelera->set_descripcion($_POST['descripcion'] ?? null);
    $cartelera->set_prioridad($_POST['prioridad'] ?? null);
    $cartelera->set_usuario_id($_SESSION['id_usuario'] ?? null);

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];

    // auditor
    $auditor = new GestorAuditoria($cartelera, Modulo::GESTIONAR_CARTELERA_VIRTUAL);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        case 'consulta':
            $respuesta = $cartelera->realizar_consulta('consultar');

            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'registrar_cartelera':
            $nombreImagen = '';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreImagen = GestorImagenes::subir($_FILES['imagen'], 'cartelera_virtual');
                if ($nombreImagen === false) {
                    throw new Exception('Error al procesar la imagen.');
                }
            }
            $cartelera->set_imagen($nombreImagen);
            $respuesta = $cartelera->realizar_consulta('registrar_cartelera');

            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::REGISTRAR);

                $prioridad = (int)($_POST['prioridad'] ?? 3);

                // 1 equivale a 'Aviso' (Alta prioridad), lo que dispara la alerta en el canal urgente
                $eventoPush = ($prioridad === 1) 
                    ? TipoEventoNotificacion::AVISO_IMPORTANTE->value 
                    : TipoEventoNotificacion::NUEVA_PUBLICACION->value;

                GestorNotificaciones::notificarTodos(
                    "Nuevo aviso: " . $_POST['titulo'], 
                    $_POST['descripcion'], 
                    "cartelera_virtual", 
                    $respuesta['lastId'], 
                    $eventoPush
                );

                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        case 'consultar_cartelera':
            $respuesta = $cartelera->realizar_consulta('consultar_cartelera');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        case 'modificar_cartelera':
            // Obtener datos anteriores
            $auditor->capturarDatosAnteriores('consultar_cartelera');

            $imagenActual = $cartelera->obtenerImagenActual();
            $eliminarImagen = isset($_POST["eliminar_imagen"]) && $_POST["eliminar_imagen"] == 1;
            $nuevaImagen = '';

            // (lógica de imagen igual)
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nuevaImagen = GestorImagenes::subir($_FILES['imagen'], 'cartelera_virtual');
                if ($nuevaImagen === false) {
                    throw new Exception('Error al procesar la nueva imagen.');
                }
                if ($imagenActual) {
                    GestorImagenes::eliminar($imagenActual, 'cartelera_virtual');
                }
            } elseif ($eliminarImagen) {
                if ($imagenActual) {
                    GestorImagenes::eliminar($imagenActual, 'cartelera_virtual');
                }
                $nuevaImagen = '';
            } else {
                $nuevaImagen = $imagenActual;
            }

            $cartelera->set_imagen($nuevaImagen);
            $respuesta = $cartelera->realizar_consulta('modificar_cartelera');

            
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::MODIFICAR); 
            }
            break;

        case 'eliminar_cartelera':
            // Obtener datos anteriores
            $auditor->capturarDatosAnteriores('consultar_cartelera');

            $respuesta = $cartelera->realizar_consulta('eliminar_cartelera');
            
            if ($respuesta['estatus']) { 
                $auditor->registrarAuditoria(Accion::ELIMINAR); 
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($cartelera)) {$cartelera->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_CARTELERA_VIRTUAL);

    $usuario = new Usuario();
    $usuarios = $usuario->realizar_consulta('consultar')['datos'] ?? [];
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_CARTELERA_VIRTUAL);
$btn_nuevo = [
    'target'  => '#modal_cartelera',
    'texto'   => 'Nueva Publicación',
    'tooltip' => 'Registrar Nueva Publicación'
];
$placeholder_buscar = "Buscar Publicación...";

require_once "vista/cartelera_virtual/cartelera_virtual_vista.php";
