<?php
namespace haydee\servicios;

use haydee\enums\HttpCodigo;
use haydee\excepciones\ValidacionException;
use haydee\excepciones\SeguridadException;
use haydee\excepciones\HaydeeException;
use haydee\servicios\GestorTrafico;
use Throwable;

class Excepciones
{
    public static function procesar(Throwable $e, bool $esApi = false)
    {
        $codigoHttp = $e->getCode() ?: HttpCodigo::ERROR_INTERNO->value;
        $mensajeOriginal = $e->getMessage();
        $datosError = ['estatus' => false, 'mensaje' => $mensajeOriginal];
        $incidenteId = null;

        // Clasificación del Error
        if ($e instanceof ValidacionException) {
            $datosError['errores'] = $e->getErrores();
        } elseif ($e instanceof SeguridadException) {
            $ipToken = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $incidenteId = substr(hash('sha256', $ipToken . date('Y-m-d H:i')), 0, 8);
            $datosError['ref'] = $incidenteId;
            error_log("[REF: #{$incidenteId}] [{$codigoHttp}] Bloqueo de Seguridad: {$mensajeOriginal} | IP: {$ipToken}");
        } elseif (!$e instanceof HaydeeException) {
            // PDOException, ParseError, etc.
            $codigoHttp = HttpCodigo::ERROR_INTERNO->value;
            $datosError['mensaje'] = "Ocurrió un error interno en el servidor.";
            error_log("Fallo Crítico: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }

        // Salida
        if ($esApi) {
            // para la App Móvil (cifrada)
            GestorTrafico::abortarConCifrado($datosError, $codigoHttp);
        } else {
            // Salida para la Web
            http_response_code((int)$codigoHttp);
            
            // AJAX Web (Fetch/jQuery)
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