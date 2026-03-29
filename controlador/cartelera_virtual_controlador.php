<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\CarteleraVirtual;
use haydee\modelo\Usuario;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\ayuda\GestorImagenes;
use haydee\servicios\GestorAuditoria;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_CARTELERA_VIRTUAL, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];

    // 1. Validamos según la operación
    $reglas = CarteleraVirtual::obtenerReglas($operacion);

    if (!isset($_POST['usuario_id'])) {
        $_POST['usuario_id'] = $_SESSION['id_usuario'] ?? null;
    }

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    $cartelera = new CarteleraVirtual();
    // Asignación masiva
    $cartelera->set_id_cartelera($_POST['id_cartelera'] ?? null);
    $cartelera->set_titulo($_POST['titulo'] ?? null);
    $cartelera->set_descripcion($_POST['descripcion'] ?? null);
    $cartelera->set_prioridad($_POST['prioridad'] ?? null);
    $cartelera->set_usuario_id($_SESSION['id_usuario'] ?? null);

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // Instanciamos el auditor
    $auditor = new GestorAuditoria($cartelera, GESTIONAR_CARTELERA_VIRTUAL);

    try {
        switch ($operacion) {
            case 'consulta':
                $respuesta = $cartelera->realizar_consulta('consultar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
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
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'consultar_cartelera':
                $respuesta = $cartelera->realizar_consulta('consultar_cartelera');
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
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar_cartelera':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_cartelera');

                $respuesta = $cartelera->realizar_consulta('eliminar_cartelera');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador cartelera virtual: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            // Cerrar conexiones explícitamente
            if (isset($cartelera)) {
                $cartelera->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_CARTELERA_VIRTUAL);

    $usuario = new Usuario();
    $usuarios = $usuario->realizar_consulta('consultar')['datos'] ?? [];
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_CARTELERA_VIRTUAL);
require_once "vista/cartelera_virtual/cartelera_virtual_vista.php";
?>