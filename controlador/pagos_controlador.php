<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\enums\TipoEventoNotificacion;
use haydee\enums\MetodoPago;
use haydee\ayuda\ConstructorDetalles;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\modelo\Pagos;
use haydee\modelo\Banco;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorNotificaciones;

// Determinar rol
$esPropietario = (isset($_SESSION["rol"]) && $_SESSION["rol"] == "Propietario");

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_PAGOS, $operacion);

    if (!isset($_POST['estado'])) {
        $_POST['estado'] = $esPropietario ? 'PENDIENTE' : 'PROCESADO';
    }

    // VALIDACION DE LA CABECERA
    $reglasCabecera = Pagos::obtenerReglas($operacion);

    if (!empty($reglasCabecera)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglasCabecera);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // VALIDACION DE DETALLES
    if ($operacion === 'registrar_pago' || $operacion === 'modificar_pago') {
        $esModificacion = ($operacion === 'modificar_pago');
        
        $detalles = ConstructorDetalles::ConstruirDetallesPagos($_POST, $_FILES, $esModificacion);
        if (empty($detalles)) {
            echo json_encode(['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de pago.']);
            exit;
        }

        $reglasDetalle = Pagos::obtenerReglasDetalles();
        $erroresDetalles = [];

        foreach ($detalles as $index => $detalle) {
            $validadorTemp = new Validador();

            // INTERCEPCIÓN DE IMÁGENES FANTASMA
            $metodosBancarios = [MetodoPago::TRANSFERENCIA->value, MetodoPago::PAGO_MOVIL->value];
            
            if (in_array($detalle['tipo_pago'], $metodosBancarios)) {
                $nombreInputFile = "imagen_{$index}";
                
                //  Leer el input como arreglo, tal como lo envía el FormData
                $imagenExistente = $_POST['imagen_existente'][$index] ?? ''; 
                
                // Si NO hay imagen vieja es nuevo pago o borraron la anterior
                if (empty($imagenExistente)) {
                    if (!isset($_FILES[$nombreInputFile]) || $_FILES[$nombreInputFile]['error'] !== UPLOAD_ERR_OK) {
                        $detalle['imagen'] = ''; // Vaciamos para forzar el error
                    }
                }
            }
            // =======================================================

            $validadorTemp->validarConjunto($detalle, $reglasDetalle);
            
            if ($validadorTemp->tieneErrores()) {
                $erroresFila = $validadorTemp->obtenerErrores();
                foreach($erroresFila as $campo => $mensajes) {
                    $erroresDetalles["detalle_" . $index . "_" . $campo] = $mensajes; 
                }
            }
        }

        if (!empty($erroresDetalles)) {
            echo json_encode([
                'estatus' => false, 
                'errores' => $erroresDetalles, 
                'mensaje' => 'Faltan comprobantes o hay errores en los renglones del pago.'
            ]);
            exit;
        }
    }

    $pagos = new Pagos();

    if (isset($detalles)) {
        $pagos->set_detalles($detalles);
    }

    // Asignacion masiva de propiedades esenciales
    $pagos->set_id_pago($_POST['id_pago'] ?? null);
    $pagos->set_id_detalle_pago($_POST['id_detalle_pago'] ?? null);
    $estadoPorDefecto = $esPropietario ? 'PENDIENTE' : 'PROCESADO';
    $pagos->set_estado($_POST['estado'] ?? $estadoPorDefecto);
    $pagos->set_tasa_dolar($_POST['tasa_dolar'] ?? null);
    $pagos->set_observacion($_POST['observacion'] ?? null);
    $pagos->set_apartamento_id($_POST['apartamento_id'] ?? null);
    $pagos->set_mensualidad_id($_POST['mensualidad_id'] ?? null);
    
    // Si es propietario, forzar su correo por seguridad
    if ($esPropietario) {
        $pagos->set_correo($_SESSION["usuario"] ?? null);
    }

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($pagos, Modulo::GESTIONAR_PAGOS);

    try {
        switch ($operacion) {
            // ==================== CONSULTAS ====================
            case 'consulta':
                if ($esPropietario) {
                    $respuesta = $pagos->realizar_consulta('consultar_por_correo');
                } else {
                    $respuesta = $pagos->realizar_consulta('consultar');
                }

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::CONSULTAR);
                }
                break;

            case 'consultar_mensualidades':
                $respuesta = $pagos->realizar_consulta('consultarMensualidadPendiente');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                break;

            case 'consultar_pago':
                $respuesta = $pagos->realizar_consulta('consultar_pago');
                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
                break;

            // ==================== REGISTRO ====================
            case 'registrar_pago':
                $respuesta = $pagos->realizar_consulta('registrar_pago');

                http_response_code($respuesta['estatus'] ? HttpCodigo::CREADO->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    // Ocultamos los detalles al auditor para evitar colapsos
                    $pagos->set_detalles(null);
                    $auditor->registrarAuditoria(Accion::REGISTRAR);

                    $id_nuevo_pago = $respuesta['id'] ?? $respuesta['lastId'] ?? null;
                    
                    if ($id_nuevo_pago) {
                        GestorNotificaciones::notificarAdmins(
                            "Nuevo Pago Registrado", 
                            "Requiere revisión y aprobación.", 
                            "pagos", 
                            $id_nuevo_pago, 
                            TipoEventoNotificacion::PAGO_RECIBIDO->value
                        );
                    }
                }
                break;

            // ==================== MODIFICAR ====================
            case 'modificar_pago':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado para modificar'];
                    break;
                }

                // Usamos la consulta plana para la bitácora
                $auditor->capturarDatosAnteriores('consultar_cabecera_pago');

                $respuesta = $pagos->realizar_consulta('modificar_pago');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    // Ocultamos los detalles al auditor
                    $pagos->set_detalles(null);
                    $auditor->registrarAuditoria(Accion::MODIFICAR);
                }
                break;

            // ==================== ELIMINAR ====================
            case 'eliminar_pago':
                if ($esPropietario) {
                    $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                    break;
                }

                // Usamos la consulta plana para la bitácora
                $auditor->capturarDatosAnteriores('consultar_cabecera_pago');

                $respuesta = $pagos->realizar_consulta('eliminar_pago');

                http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::BAD_REQUEST->value);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria(Accion::ELIMINAR);
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en controlador pagos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno del servidor'];
    } finally {
        if ($respuesta !== null) {
            if (isset($pagos)) { $pagos->cerrar(); }
            if (isset($banco)) { $banco->cerrar(); }
            if (isset($apartamento)) { $apartamento->cerrar(); }
            
            Bitacora::cerrarConexionBitacora();

            echo json_encode($respuesta);
            exit;
        }
    }
}

// =========================================================
// VALIDACIONES AJAX
// =========================================================
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];
    $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];

    try {
        switch ($validar) {
            case 'referencia':
                $referencia = $_POST["referencia"] ?? '';
                $id_pago = $_POST["id_pago"] ?? null; // Recibimos el ID si estamos modificando
                
                // Instanciamos el modelo y usamos la nueva función experta
                $pagosTemp = new Pagos();
                $existe = $pagosTemp->verificarReferenciaDisponible($referencia, $id_pago);
                
                // Respondemos estatus TRUE (la petición fue exitosa) y enviamos si existe o no
                $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'La referencia ya está registrada en otro pago' : 'Disponible'];
                break;

            case 'validar_clave_foranea':
                if (isset($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor'])) {
                    $validadorBD = new ValidadorBD();
                    $existe = $validadorBD->existe($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor']);
                    $respuesta = ['estatus' => $existe, 'mensaje' => 'OK'];
                } else {
                    $respuesta = ['estatus' => false, 'mensaje' => 'Faltan parámetros'];
                }
                break;

            default:
                http_response_code(HttpCodigo::BAD_REQUEST->value);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(HttpCodigo::ERROR_INTERNO->value);
        error_log("Error en Validación AJAX Pagos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(HttpCodigo::OK->value);
    }
    
    echo json_encode($respuesta);
    exit;
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA (Solo al cargar la página)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(Modulo::GESTIONAR_PAGOS);

    // Modelos auxiliares para selects
    $banco = new Banco();
    $apartamento = new Apartamento();

    // Carga de datos para la vista
    $registro_banco = $banco->realizar_consulta('consultar')['datos'] ?? [];
    
    if (!$esPropietario) {
        $registro_apartamento = $apartamento->realizar_consulta('consultar_listado')['datos'] ?? [];
    } else {
        $apartamento->set_correo($_SESSION["usuario"]);
        $registro_apartamento = $apartamento->realizar_consulta('obtener_apartamentos_por_correo')['datos'] ?? [];
    }
}
$permisosVista = Sesiones::obtenerPermisosVista(Modulo::GESTIONAR_PAGOS);
$btn_nuevo = [
    'target'  => '#modal_pagos',
    'texto'   => 'Nuevo Pago',
    'tooltip' => 'Registrar Nuevo Pago'
];
$placeholder_buscar = "Buscar pago...";

// Renderizamos el HTML
require_once "vista/pagos/pagos_vista.php";

