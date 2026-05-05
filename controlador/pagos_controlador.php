<?php
use haydee\servicios\Sesiones;
use haydee\modelo\Pagos;
use haydee\modelo\Banco;
use haydee\modelo\Apartamento;
use haydee\modelo\Bitacora;
use haydee\ayuda\ConstructorDetalles;
use haydee\ayuda\Validador;
use haydee\ayuda\ValidadorBD;
use haydee\servicios\GestorAuditoria;

// Verificar sesión
Sesiones::validarMetodoHTTP(['GET', 'POST']);
Sesiones::verificarSesion();

// Determinar rol
$esPropietario = (isset($_SESSION["rol"]) && $_SESSION["rol"] == "Propietario");

// Si no es propietario, verificar permiso
if (!$esPropietario) {
    Sesiones::verificarPermiso(GESTIONAR_PAGOS, CONSULTAR);
}

if (isset($_POST["operacion"])) {
    header('Content-Type: application/json');
    $operacion = $_POST["operacion"];

    Sesiones::verificarPermisoAccion(GESTIONAR_PAGOS, $operacion);

    // =========================================================
    // VALIDACIÓN DE LA CABECERA
    // =========================================================
    $reglasCabecera = Pagos::obtenerReglas($operacion);

    if (!empty($reglasCabecera)) {
        $validador = new Validador();
        $validador->validarConjunto($_POST, $reglasCabecera);

        if ($validador->tieneErrores()) {
            $codigoHttp = $validador->tieneError404() ? 404 : 400;
            http_response_code($codigoHttp);
            echo json_encode(['estatus' => false, 'errores' => $validador->obtenerErrores()]);
            exit;
        }
    }

    // =========================================================
    // CONSTRUCCIÓN Y VALIDACIÓN DE DETALLES
    // =========================================================
    if ($operacion === 'registrar_pago' || $operacion === 'modificar_pago') {
        $esModificacion = ($operacion === 'modificar_pago');
        
        $detalles = ConstructorDetalles::ConstruirDetallesPagos($_POST, $_FILES, $esModificacion);
        // var_dump($detalles);
        if (empty($detalles)) {
            echo json_encode(['estatus' => false, 'mensaje' => 'Debe proporcionar al menos un detalle de pago.']);
            exit;
        }

        $reglasDetalle = Pagos::obtenerReglasDetalles();
        $erroresDetalles = [];

        foreach ($detalles as $index => $detalle) {
            $validadorTemp = new Validador();
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
                'mensaje' => 'Hay errores en los renglones del pago. Por favor, revíselos.'
            ]);
            exit;
        }
    }

    $pagos = new Pagos();

    if (isset($detalles)) {
        $pagos->set_detalles($detalles);
    }

    // Asignación masiva de propiedades esenciales
    $pagos->set_id_pago($_POST['id_pago'] ?? null);
    $pagos->set_id_detalle_pago($_POST['id_detalle_pago'] ?? null);
    $pagos->set_estado($_POST['estado'] ?? null);
    $pagos->set_observacion($_POST['observacion'] ?? null);
    $pagos->set_apartamento_id($_POST['apartamento_id'] ?? null);
    $pagos->set_mensualidad_id($_POST['mensualidad_id'] ?? null);
    
    // Si es propietario, forzar su correo por seguridad
    if ($esPropietario) {
        $pagos->set_correo($_SESSION["usuario"] ?? null);
    }

    $respuesta = ['estatus' => false, 'mensaje' => 'Operación no válida'];
    $auditor = new GestorAuditoria($pagos, GESTIONAR_PAGOS);

    try {
        switch ($operacion) {
            // ==================== CONSULTAS ====================
            case 'consulta':
                if ($esPropietario) {
                    $respuesta = $pagos->realizar_consulta('consultar_por_correo');
                } else {
                    $respuesta = $pagos->realizar_consulta('consultar');
                }

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('consultar');
                }
                break;

            case 'consultar_mensualidades':
                $respuesta = $pagos->realizar_consulta('consultarMensualidadPendiente');
                http_response_code($respuesta['estatus'] ? 200 : 400);
                break;

            case 'consultar_pago':
                $respuesta = $pagos->realizar_consulta('consultar_pago');
                http_response_code($respuesta['estatus'] ? 200 : 404);
                break;

            // ==================== REGISTRO ====================
            case 'registrar_pago':
                if ($esPropietario) {
                    $pagos->set_estado('No verificado');
                }

                $respuesta = $pagos->realizar_consulta('registrar_pago');

                http_response_code($respuesta['estatus'] ? 201 : 400);
                if ($respuesta['estatus']) {
                    // Ocultamos los detalles al auditor para evitar colapsos
                    $pagos->set_detalles(null);
                    $auditor->registrarAuditoria('registrar');
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

                $configPagos = [
                    'campos' => ['fecha', 'monto', 'tipo_pago', 'monto_dolar'],
                    'bancarios' => ['banco_id', 'referencia'],
                    'imagenes' => 'imagen',
                    'metodo_pago_campo' => 'tipo_pago',
                    'metodos_con_archivo' => ['Transferencia', 'Pago Movil'],
                    'carpeta_imagenes' => 'pagos',
                    'campo_existente' => 'imagen_existente',
                    'indice_archivo_formato' => '/^imagen_(\d+)$/'
                ];

                $respuesta = $pagos->realizar_consulta('modificar_pago');

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    // Ocultamos los detalles al auditor
                    $pagos->set_detalles(null);
                    $auditor->registrarAuditoria('modificar');
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

                http_response_code($respuesta['estatus'] ? 200 : 400);
                if ($respuesta['estatus']) {
                    $auditor->registrarAuditoria('eliminar');
                }
                break;

            default:
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Operación no implementada'];
        }
    } catch (Exception $e) {
        http_response_code(500);
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
                http_response_code(400);
                $respuesta = ['estatus' => false, 'mensaje' => 'Validación no reconocida'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en validación AJAX Pagos: " . $e->getMessage());
        $respuesta = ['estatus' => false, 'mensaje' => 'Error interno'];
    }

    if ($respuesta['estatus'] === true || isset($respuesta['existe'])) {
        http_response_code(200);
    }
    
    echo json_encode($respuesta);
    exit;
}

// =========================================================
// CARGA DE DATOS PARA LA VISTA (Solo al cargar la página)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    GestorAuditoria::inicializarBanderaConsulta(GESTIONAR_PAGOS);

    // Modelos auxiliares para selects
    $banco = new Banco();
    $apartamento = new Apartamento();

    // Carga de datos para la vista
    $registro_banco = $banco->realizar_consulta('consultar')['datos'] ?? [];
    
    if (!$esPropietario) {
        $registro_apartamento = $apartamento->realizar_consulta('consultar_listado')['datos'] ?? [];
    } else {
        $apartamento->set_correo($_SESSION["usuario"]);
        $registro_apartamento = $apartamento->realizar_consulta('consultar_por_propietario')['datos'] ?? [];
    }
}
$permisosVista = Sesiones::obtenerPermisosVista(GESTIONAR_PAGOS);
$btn_nuevo = [
    'target'  => '#modal_pagos',
    'texto'   => 'Nuevo Pago',
    'tooltip' => 'Registrar Nuevo Pago'
];
$placeholder_buscar = "Buscar pago...";

// Renderizamos el HTML
require_once "vista/pagos/pagos_vista.php";