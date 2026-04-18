<?php
use haydee\servicios\Sesiones;
use haydee\modelo\Mantenimiento;
use haydee\modelo\Bitacora;
use haydee\ayuda\Validador;

// Verificaciones de seguridad
Sesiones::verificarSesion();
Sesiones::verificarPermiso(GESTIONAR_MANTENIMIENTO, CONSULTAR);

if (isset($_POST["operacion"])) {
    $operacion = $_POST["operacion"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];

    // =========================================================
    // VALIDACIÓN CENTRALIZADA (Protección contra Inyección de Comandos)
    // =========================================================
    $reglas = Mantenimiento::obtenerReglas($operacion);

    if (!empty($reglas)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglas);

        if ($validador->tieneErrores()) {
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // Instancia de la clase Mantenimiento
    $mantenimiento = new Mantenimiento();

    try{
        switch ($operacion) {
            case 'obtener_copias':
                $respuesta = $mantenimiento->obtenerCopias();
                break;

            case 'generar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                if (in_array($db, ['negocio', 'seguridad'])) {
                    $respuesta = $mantenimiento->generarCopiaSeguridad($db);
                    if ($respuesta['estatus']) {
                        
                        $detalles = [
                            'accion' => 'Generó copia de seguridad',
                            'base_datos' => strtoupper($db)
                        ];
                        Bitacora::registrar(RESPALDAR, GESTIONAR_MANTENIMIENTO, null, null, $detalles);

                    }
                } else {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Base de datos no válida'];
                }
                break;

            case 'descargar_copia_seguridad':
                $db = $_POST['db'] ?? '';
                
                // Anulamos la respuesta para evitar que el bloque 'finally' imprima el JSON en pantalla blanca
                $respuesta = null; 

                if (in_array($db, ['negocio', 'seguridad'])) {
                    $resultado = $mantenimiento->descargarCopiaSeguridad($db);
                    
                    // Si el método nos devuelve un arreglo con estatus false, falló
                    if (is_array($resultado) && !$resultado['estatus']) {
                        $mensaje = urlencode($resultado['mensaje']);
                        header("Location: ?pagina=mantenimiento&accion=inicio&e=1&msg=$mensaje");
                        exit;
                    }
                    
                    // Si fue exitoso, el método descargarCopiaSeguridad ya envió el archivo e hizo exit.
                    exit; 
                } else {
                    header("Location: ?pagina=mantenimiento&accion=inicio&e=1&msg=" . urlencode("Base de datos no válida"));
                    exit;
                }
                break;

            case 'importar_copia_seguridad':
                // LÓGICA PARA RESTAURAR DESDE EL SERVIDOR
                $db = $_POST['db'] ?? '';
                $fichero = $_POST['fichero'] ?? '';

                if (!in_array($db, ['negocio', 'seguridad']) || empty($fichero)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Datos inválidos para la restauración.'];
                    break;
                }

                $respuesta = $mantenimiento->importarCopiaSeguridad($db, $fichero);
                if ($respuesta['estatus']) {
                    $detalles = [
                        'accion' => 'Restauró base de datos desde el servidor',
                        'base_datos' => strtoupper($db),
                        'archivo' => $fichero
                    ];
                    Bitacora::registrar(RESTAURAR, GESTIONAR_MANTENIMIENTO, null, null, $detalles);
                }
                break;

            case 'importar_archivo_sql':
                // LÓGICA PARA RESTAURAR DESDE LA PC (ARCHIVO SUBIDO)
                $db = $_POST['db'] ?? '';
                if (!in_array($db, ['negocio', 'seguridad'])) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Base de datos no válida'];
                    break;
                }

                // JS envía el archivo como 'fichero'
                if (!isset($_FILES['fichero']) || $_FILES['fichero']['error'] !== UPLOAD_ERR_OK) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No se subió ningún archivo o hubo un error en la subida.'];
                    break;
                }

                $archivo_tmp = $_FILES['fichero']['tmp_name'];
                $nombre_archivo = $_FILES['fichero']['name'];
                
                $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
                if ($extension !== 'sql') {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Solo se permiten archivos SQL.'];
                    break;
                }

                $contenido_sql = file_get_contents($archivo_tmp);
                if (empty(trim($contenido_sql))) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo SQL está vacío.'];
                    break;
                }

                // Verificación de seguridad cruzada
                $es_seguridad = stripos($contenido_sql, "Database: seguridad_haydee_db") !== false;
                if (($db === 'seguridad' && !$es_seguridad) || ($db === 'negocio' && $es_seguridad)) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'El archivo no corresponde a la base de datos destino.'];
                    break;
                }

                $respuesta = $mantenimiento->importarSQL($contenido_sql, $db);
                if ($respuesta['estatus']) {
                    $detalles = [
                        'accion' => 'Restauró base de datos desde archivo local',
                        'base_datos' => strtoupper($db),
                        'archivo_subido' => $nombre_archivo
                    ];
                    Bitacora::registrar(RESTAURAR, GESTIONAR_MANTENIMIENTO, null, null, $detalles);
                }
                break;

            default:
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        error_log("Error en controlador mantenimiento: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($mantenimiento)) {
                $mantenimiento->cerrar();
            }
            Bitacora::cerrarConexionBitacora(); //  Bitacora, que cierra su conexión de seguridad

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;
        }
    }
}

// Bitácora de acceso al módulo (solo al cargar la vista por GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Bitacora::registrar(CONSULTAR, GESTIONAR_MANTENIMIENTO);
}

// Cargar la vista
require_once "vista/mantenimiento/mantenimiento_vista.php";