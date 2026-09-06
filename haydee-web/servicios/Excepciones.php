<?php
namespace haydee\servicios;

namespace haydee\servicios;

use haydee\enums\HttpCodigo;
use haydee\excepciones\ValidacionException;
use haydee\excepciones\SeguridadException;
use haydee\excepciones\NegocioException;
use haydee\excepciones\BaseDatosException;
use haydee\excepciones\HaydeeException;
use haydee\servicios\GestorTrafico;
use Throwable;

class Excepciones
{
    public static function procesar(Throwable $e, bool $esApi = false)
    {
        $codigoHttp = $e->getCode() ?: HttpCodigo::ERROR_INTERNO->value;
        $mensajeOriginal = $e->getMessage();
        
        // Formato base enriquecido
        $datosError = [
            'estatus'     => false,
            'mensaje'     => $mensajeOriginal,
            'tipo'        => 'sistema',
            'titulo'      => 'Error del Sistema',
            'color'       => '#6b7280', // Gris 
            'icono'       => 'server-crash', 
        ];
        $incidenteId = null;

        // Clasificación del Error Enriquecida
        if ($e instanceof ValidacionException) {
            $datosError['tipo']   = 'validacion';
            $datosError['titulo'] = 'Datos Inválidos';
            $datosError['color']  = '#f59e0b'; // Amarillo/Warning
            $datosError['icono']  = 'alert-triangle';
            $datosError['errores'] = $e->getErrores();
            
        } elseif ($e instanceof NegocioException) {
            $datosError['tipo']   = 'negocio';
            $datosError['titulo'] = 'Operación No Permitida';
            $datosError['color']  = '#3b82f6'; // Azul/Info
            $datosError['icono']  = 'info-circle';
            
        } elseif ($e instanceof SeguridadException) {
            $datosError['tipo']   = 'seguridad';
            $datosError['titulo'] = 'Acceso Denegado';
            $datosError['color']  = '#dc2626'; // Rojo/Danger
            $datosError['icono']  = 'shield-off';
            
            $ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $metodo  = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
            $uri     = $_SERVER['REQUEST_URI'] ?? 'N/A';
            $incidenteId = substr(hash('sha256', $ipToken . date('Y-m-d H:i')), 0, 8);
            $datosError['ref'] = $incidenteId;
            
            error_log(sprintf("[REF: #%s] [%s] Bloqueo de Seguridad: %s | IP: %s | %s %s", 
                $incidenteId, $codigoHttp, $mensajeOriginal, $ipToken, $metodo, $uri));
                
        } elseif ($e instanceof BaseDatosException) {
            $datosError['tipo']   = 'bd';
            $datosError['titulo'] = 'Error de Datos';
            $datosError['color']  = '#991b1b'; // Rojo oscuro
            $datosError['icono']  = 'database-fail';
            
            // la excepción previa (PDOException) si existe para el log
            $causa = $e->getPrevious() ? $e->getPrevious()->getMessage() : 'Desconocida';
            error_log("[BD CRÍTICO] {$mensajeOriginal} | Causa interna: {$causa} en {$e->getFile()}:{$e->getLine()}");
            
        } elseif (!$e instanceof HaydeeException) {
            // Excepciones nativas (ParseError, TypeError)
            $codigoHttp = HttpCodigo::ERROR_INTERNO->value;
            $datosError['tipo']   = 'critico';
            $datosError['titulo'] = 'Fallo Crítico';
            $datosError['color']  = '#000000'; // Negro/Fatal
            $datosError['icono']  = 'alert-octagon';
            $datosError['mensaje'] = "Ocurrió un error interno en el servidor.";
            
            // Captura la clase del error y un fragmento del Stack Trace
            $claseError = get_class($e);
            $trazaCorta = substr(str_replace("\n", " ", $e->getTraceAsString()), 0, 150);
            error_log(sprintf("[CRÍTICO] %s: %s en %s:%d | Traza: %s...", 
                $claseError, $e->getMessage(), $e->getFile(), $e->getLine(), $trazaCorta));
        }

        // Salida
        if ($esApi) {
            // para la App Móvil (cifrada)
            GestorTrafico::abortarConCifrado($datosError, $codigoHttp);
        } else {
            http_response_code((int)$codigoHttp);
            
            $esAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                      (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($esAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode($datosError);
                exit;
            }

            if ($codigoHttp == 401) {
                $_SESSION['error_flash'] = $mensajeOriginal;
                header("Location: index.php?pagina=login");
                exit;
            }
            
            if ($codigoHttp == 403 && isset($_SESSION['rate_limit_expiracion'])) {
                if ($_SESSION['rate_limit_expiracion'] !== 'permanente' && time() > (int)$_SESSION['rate_limit_expiracion']) {
                    unset($_SESSION['rate_limit_expiracion']);
                } else {
                     if (file_exists(ROOT_PATH . "/vista/error/403_firewall_vista.php")) {
                         require_once ROOT_PATH . "/vista/error/403_firewall_vista.php";
                         exit;
                     }
                }
            }

            extract($datosError); 
            
            $vistaEspecifica = ROOT_PATH . "/vista/error/{$codigoHttp}_vista.php";
            if (file_exists($vistaEspecifica)) {
                require_once $vistaEspecifica;
            } else {
                require_once ROOT_PATH . "/vista/error/error.php";
            }
            exit;
        }
    }
}