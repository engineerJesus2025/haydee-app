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
use PDOException;

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
        $incidenteId = strtoupper(substr(uniqid('ERR-'), -8));

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
            $datosError['color']  = '#dc2626'; 
            $datosError['icono']  = 'shield-off';
            $datosError['ref']    = $incidenteId;
            
            $ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $metodo  = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
            $uri     = $_SERVER['REQUEST_URI'] ?? 'N/A';
            
            error_log(sprintf("[REF: %s] [%s] Bloqueo de Seguridad: %s | IP: %s | %s %s", 
                $incidenteId, $codigoHttp, $mensajeOriginal, $ipToken, $metodo, $uri));
                
        } elseif ($e instanceof BaseDatosException || $e instanceof \PDOException) {
            $datosError['tipo']   = 'bd';
            $datosError['titulo'] = 'Error de Datos';
            $datosError['color']  = '#991b1b'; 
            $datosError['icono']  = 'database-fail';
            $datosError['ref']    = $incidenteId;
            $datosError['mensaje'] = "Ocurrió un problema interno al procesar los datos. Si el problema persiste, contacte a soporte indicando el código de referencia.";
            
            $causa = $e->getPrevious() ? $e->getPrevious()->getMessage() : $e->getMessage();
            
            error_log("[REF: {$incidenteId}] [BD CRÍTICO] {$mensajeOriginal} | Causa interna: {$causa} en {$e->getFile()}:{$e->getLine()}");
            
        } elseif (!$e instanceof HaydeeException) {
            $codigoHttp = HttpCodigo::ERROR_INTERNO->value;
            $datosError['tipo']   = 'critico';
            $datosError['titulo'] = 'Fallo Crítico';
            $datosError['color']  = '#000000'; 
            $datosError['icono']  = 'alert-octagon';
            $datosError['ref']    = $incidenteId;
            
            $datosError['mensaje'] = "Ocurrió un error inesperado en el servidor.";
            
            // Captura la clase del error y un fragmento del Stack Trace
            $claseError = get_class($e);
            $trazaCorta = substr(str_replace("\n", " ", $e->getTraceAsString()), 0, 150);
            
            error_log(sprintf("[REF: %s] [CRÍTICO] %s: %s en %s:%d | Traza: %s...", 
                $incidenteId, $claseError, $e->getMessage(), $e->getFile(), $e->getLine(), $trazaCorta));
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