<?php
use haydee\ayuda\Sesiones;
use haydee\modelo\CarteleraVirtual;
use haydee\modelo\Usuario;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorAuditoria;
use haydee\ayuda\GestorImagenes;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_CARTELERA_VIRTUAL, CONSULTAR);

$cartelera = new CarteleraVirtual();
$usuario = new Usuario();
$usuarios = $usuario->realizar_consulta('consultar')['datos'] ?? [];

if (isset($_POST["operacion"])) {
    // Asignación masiva
    $cartelera->set_id_cartelera($_POST['id_cartelera'] ?? null);
    $cartelera->set_titulo($_POST['titulo'] ?? null);
    $cartelera->set_descripcion($_POST['descripcion'] ?? null);
    $cartelera->set_fecha($_POST['fecha'] ?? null);
    $cartelera->set_prioridad($_POST['prioridad'] ?? null);
    $cartelera->set_usuario_id($_SESSION['id_usuario'] ?? null);

    $operacion = $_POST["operacion"];
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

            case 'registrar':
                $nombreImagen = '';
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $nombreImagen = GestorImagenes::subir($_FILES['imagen'], 'cartelera');
                    if ($nombreImagen === false) {
                        throw new Exception('Error al procesar la imagen.');
                    }
                }
                $cartelera->set_imagen($nombreImagen);
                $respuesta = $cartelera->realizar_consulta('registrar');
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('registrar');
                }
                break;

            case 'consulta_especifica':
                $respuesta = $cartelera->realizar_consulta('consultar_cartelera_id');
                break;

            case 'modificar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_cartelera_id');

                $imagenActual = $cartelera->obtenerImagenActual();
                $eliminarImagen = isset($_POST["eliminar_imagen"]) && $_POST["eliminar_imagen"] == 1;
                $nuevaImagen = '';

                // (lógica de imagen igual)
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $nuevaImagen = GestorImagenes::subir($_FILES['imagen'], 'cartelera');
                    if ($nuevaImagen === false) {
                        throw new Exception('Error al procesar la nueva imagen.');
                    }
                    if ($imagenActual) {
                        GestorImagenes::eliminar($imagenActual, 'cartelera');
                    }
                } elseif ($eliminarImagen) {
                    if ($imagenActual) {
                        GestorImagenes::eliminar($imagenActual, 'cartelera');
                    }
                    $nuevaImagen = '';
                } else {
                    $nuevaImagen = $imagenActual;
                }

                $cartelera->set_imagen($nuevaImagen);
                $respuesta = $cartelera->realizar_consulta('modificar_publicacion');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('modificar'); 
                }
                break;

            case 'eliminar':
                // Obtener datos anteriores
                $auditor->capturarDatosAnteriores('consultar_cartelera_id');

                $respuesta = $cartelera->realizar_consulta('eliminar_publicacion');
                if ($respuesta['estatus']) { 
                    $auditor->registrarAuditoria('eliminar'); 
                }
                break;

            case 'ultimo_id':
                $respuesta = $cartelera->realizar_consulta('lastId');
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
}

require_once "vista/cartelera_virtual/cartelera_virtual_vista.php";
?>