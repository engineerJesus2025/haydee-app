<?php
use haydee\enums\HttpCodigo;
use haydee\enums\Modulo;
use haydee\enums\Accion;
use haydee\enums\TipoEventoNotificacion;
use haydee\ayuda\Validador;
use haydee\ayuda\GestorImagenes;
use haydee\modelo\CarteleraVirtual;
use haydee\servicios\Sesiones;
use haydee\servicios\GestorNotificaciones;
use haydee\servicios\GestorAuditoria;
use haydee\excepciones\HaydeeException;
use haydee\excepciones\ValidacionException;
use haydee\excepciones\SeguridadException;

$operacion = $operacion ?? '';
if (empty($operacion)) {
    throw new HaydeeException('No se especificó la operación.', HttpCodigo::BAD_REQUEST->value);
}

Sesiones::verificarPermisoAccion(Modulo::GESTIONAR_CARTELERA_VIRTUAL, $operacion, [], true);

$reglas = CarteleraVirtual::obtenerReglas($operacion);
$validador = new Validador();

if (!$validador->validarMetodoHTTP($metodoHttp, $reglas)) {
    throw new HaydeeException('Protocolo HTTP denegado para esta operación.', HttpCodigo::METODO_NO_PERMITIDO->value);
}

if (!empty($reglas)) {
    $validador->validarConjunto($datosPeticion, $reglas);
    if ($validador->tieneErrores()) {
        $codigoHttp = $validador->tieneError404() ? HttpCodigo::NO_ENCONTRADO->value : HttpCodigo::NO_PROCESABLE->value;
        throw new ValidacionException('Datos de formulario inválidos o incompletos.', $validador->obtenerErrores(), $codigoHttp);
    }
}

$cartelera = new CarteleraVirtual();
$auditor = new GestorAuditoria($cartelera, Modulo::GESTIONAR_CARTELERA_VIRTUAL);

if ($metodoHttp === 'POST' || $metodoHttp === 'PUT') {
    $cartelera->set_id_cartelera($datosPeticion['id_cartelera'] ?? null);
    $cartelera->set_titulo($datosPeticion['titulo'] ?? null);
    $cartelera->set_descripcion($datosPeticion['descripcion'] ?? null);
    $cartelera->set_prioridad($datosPeticion['prioridad'] ?? null);
    $cartelera->set_usuario_id($datosPeticion['usuario_id'] ?? 1); 
}

switch ($operacion) {
    case 'consulta':
        if (isset($datosPeticion['pagina'], $datosPeticion['limite'])) {
            $pagina = (int)$datosPeticion['pagina'];
            $limite = (int)$datosPeticion['limite'];
            $offset = ($pagina - 1) * $limite;
            $cartelera->set_limite_paginacion($limite);
            $cartelera->set_offset_paginacion($offset);
            $respuesta = $cartelera->realizar_consulta('consultar_paginada');
        } else {
            $respuesta = $cartelera->realizar_consulta('consultar');
        }

        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'consultar_cartelera':
        $cartelera->set_id_cartelera($datosPeticion['id_cartelera'] ?? null);
        $respuesta = $cartelera->realizar_consulta('consultar_cartelera');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::CONSULTAR);
        break;

    case 'registrar_cartelera':
        $nombreImagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreImagen = GestorImagenes::subir($_FILES['imagen'], 'cartelera_virtual');
            if ($nombreImagen === false) {
                throw new HaydeeException('Error al procesar la imagen enviada.', HttpCodigo::ERROR_INTERNO->value);
            }
        }
        $cartelera->set_imagen($nombreImagen);
        $respuesta = $cartelera->realizar_consulta('registrar_cartelera');
        
        if ($respuesta['estatus']) {
            $respuesta['nombre_imagen'] = $nombreImagen;
            $auditor->registrarAuditoria(Accion::REGISTRAR);

            $prioridad = (int)($datosPeticion['prioridad'] ?? 3);
            $eventoPush = ($prioridad === 1) ? TipoEventoNotificacion::AVISO_IMPORTANTE->value : TipoEventoNotificacion::NUEVA_PUBLICACION->value;

            GestorNotificaciones::notificarTodos(
                "Nuevo aviso: " . ($datosPeticion['titulo'] ?? 'Importante'), 
                $datosPeticion['descripcion'] ?? '', 
                "cartelera_virtual", 
                $respuesta['lastId'] ?? null, 
                $eventoPush
            );
        }
        break;

    case 'actualizar_cartelera':
        if (!$esAdministrador) throw new SeguridadException('Acción no autorizada.', HttpCodigo::PROHIBIDO->value);
        
        $auditor->capturarDatosAnteriores('consultar_cartelera');
        $respuesta = $cartelera->realizar_consulta('actualizar_cartelera');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::MODIFICAR);
        break;

    case 'eliminar_cartelera':
        if (!$esAdministrador) throw new SeguridadException('Acción no autorizada.', HttpCodigo::PROHIBIDO->value);
        
        $auditor->capturarDatosAnteriores('consultar_cartelera');
        $respuesta = $cartelera->realizar_consulta('eliminar_cartelera');
        if ($respuesta['estatus']) $auditor->registrarAuditoria(Accion::ELIMINAR);
        break;

    default:
        throw new HaydeeException('Operación no reconocida o implementada.', HttpCodigo::BAD_REQUEST->value);
}

if (!$respuesta['estatus']) {
    throw new HaydeeException($respuesta['mensaje'], HttpCodigo::BAD_REQUEST->value);
}

$codigoExito = ($operacion === 'registrar_cartelera') ? HttpCodigo::CREADO->value : HttpCodigo::OK->value;
http_response_code($codigoExito);
echo json_encode($respuesta);