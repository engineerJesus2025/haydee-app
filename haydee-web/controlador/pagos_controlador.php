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
use haydee\modelo\CuentasCondominio;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\servicios\GestorTasa;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorAuditoria;
use haydee\servicios\GestorNotificaciones;
use haydee\servicios\EscanerComprobantes;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;

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
            throw new ValidacionException('Datos inválidos.', $validador->obtenerErrores(), $codigoHttp);
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
    $pagos->set_observacion($_POST['observacion'] ?? null);
    $pagos->set_apartamento_id($_POST['apartamento_id'] ?? null);
    $pagos->set_mensualidad_id($_POST['mensualidad_id'] ?? null);

    $operacionesMonetarias = ['registrar_pago', 'modificar_pago'];
    if (in_array($operacion, $operacionesMonetarias)) {
        $tasaDolar = GestorTasa::obtener();
        $pagos->set_tasa_dolar($tasaDolar);
    }
    
    // Si es propietario, forzar su correo por seguridad
    if ($esPropietario) {
        $pagos->set_correo($_SESSION["usuario"] ?? null);
    }

    // Respuesta por defecto
    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida', 'datos' => []];
    $auditor = new GestorAuditoria($pagos, Modulo::GESTIONAR_PAGOS);
    $codigoExito = HttpCodigo::OK->value;
    switch ($operacion) {
        // ==================== CONSULTAS ====================
        case 'consulta':
            if ($esPropietario) {
                $respuesta = $pagos->realizar_consulta('consultar_por_correo');
            } else {
                $respuesta = $pagos->realizar_consulta('consultar');
            }
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::CONSULTAR);
            }
            break;

        case 'consultar_mensualidades':
            $respuesta = $pagos->realizar_consulta('consultarMensualidadPendiente');
            break;

        case 'consultar_pago':
            $respuesta = $pagos->realizar_consulta('consultar_pago');
            http_response_code($respuesta['estatus'] ? HttpCodigo::OK->value : HttpCodigo::NO_ENCONTRADO->value);
            break;

        // ==================== REGISTRO ====================
        case 'registrar_pago':
            $respuesta = $pagos->realizar_consulta('registrar_pago');
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
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        // MODIFICAR
        case 'modificar_pago':
            if ($esPropietario) {
                $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado para modificar'];
                break;
            }

            // Usamos la consulta plana para la bitácora
            $auditor->capturarDatosAnteriores('consultar_cabecera_pago');

            $respuesta = $pagos->realizar_consulta('modificar_pago');
            if ($respuesta['estatus']) {
                // Ocultamos los detalles al auditor
                $pagos->set_detalles(null);
                $auditor->registrarAuditoria(Accion::MODIFICAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        // ELIMINAR
        case 'eliminar_pago':
            if ($esPropietario) {
                $respuesta = ['estatus' => false, 'mensaje' => 'No autorizado'];
                break;
            }

            // Usamos la consulta plana para la bitácora
            $auditor->capturarDatosAnteriores('consultar_cabecera_pago');

            $respuesta = $pagos->realizar_consulta('eliminar_pago');
            if ($respuesta['estatus']) {
                $auditor->registrarAuditoria(Accion::ELIMINAR);
                $codigoExito = HttpCodigo::CREADO->value;
            }
            break;

        default:
            throw new HaydeeException('Operación no implementada', HttpCodigo::BAD_REQUEST->value);
    }

    if (!$respuesta['estatus']) {
        throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
    }

    if (isset($pagos)) {$pagos->cerrar();}
    if (isset($banco)) {$banco->cerrar();}
    if (isset($apartamento)) {$apartamento->cerrar();}
    Bitacora::cerrarConexionBitacora();

    http_response_code($codigoExito);
    echo json_encode($respuesta);
    exit;
}

// VALIDACIONES AJAX
if (isset($_POST["validar"])) {
    header('Content-Type: application/json');
    $validar = $_POST["validar"];

    switch ($validar) {
        case 'referencia':
            $referencia = $_POST["referencia"] ?? '';
            $id_pago = $_POST["id_pago"] ?? null; 
            
            $pagosTemp = new Pagos();
            $existe = $pagosTemp->verificarReferenciaDisponible($referencia, $id_pago);
            $pagosTemp->cerrar();
            
            $respuesta = ['estatus' => true, 'existe' => $existe, 'mensaje' => $existe ? 'La referencia ya está registrada en otro pago' : 'Disponible'];
            break;

        case 'validar_clave_foranea':
            if (isset($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor'])) {
                $validadorBD = new ValidadorBD();
                $existe = $validadorBD->existe($_POST['tabla'], $_POST['nombre_clave'], $_POST['valor']);
                $respuesta = ['estatus' => $existe, 'mensaje' => 'OK'];
            } else {
                throw new HaydeeException('Faltan parámetros de validación', HttpCodigo::BAD_REQUEST->value);
            }
            break;

        case 'escanear_comprobante':
            $respuesta = EscanerComprobantes::procesarPeticion($_FILES['comprobante'] ?? null);
            break;

        default:
            throw new HaydeeException('Validación no reconocida', HttpCodigo::BAD_REQUEST->value);
    }

    http_response_code(HttpCodigo::OK->value);
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
    $cuenta = new CuentasCondominio();
    $apartamento = new Apartamento();

    // Carga de datos para la vista
    $registro_banco = $banco->realizar_consulta('consultar')['datos'] ?? [];
    $registro_cuentas = $cuenta->realizar_consulta('consultar')['datos'] ?? [];
    
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

