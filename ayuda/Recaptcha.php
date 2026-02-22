<?php
namespace haydee\ayuda;

class Recaptcha
{
    private $claveSecreta;
    private $deshabilitado;

    public function __construct($claveSecreta = null, $deshabilitado = false)
    {
        $this->claveSecreta = $claveSecreta ?? CLAVE_SECRETA_RECAPTCHA;
        $this->deshabilitado = $deshabilitado;
    }

    /**
     * Verifica la respuesta de reCAPTCHA.
     * @param string $respuesta
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function verificar($respuesta)
    {
        if ($this->deshabilitado) {
            return ['success' => true, 'error' => null];
        }
        if (empty($respuesta)) {
            return ['success' => false, 'error' => 'El reCAPTCHA es obligatorio.'];
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $datos = [
            'secret' => $this->claveSecreta,
            'response' => $respuesta,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        $opciones = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($datos),
                'timeout' => 10
            ]
        ];

        $contexto = stream_context_create($opciones);
        $resultado = @file_get_contents($url, false, $contexto);
        if ($resultado === false) {
            return ['success' => false, 'error' => 'No se pudo conectar con el servicio de reCAPTCHA.'];
        }

        $json = json_decode($resultado, true);
        if (!$json || !isset($json['success'])) {
            return ['success' => false, 'error' => 'Respuesta inválida del servicio.'];
        }

        if (!$json['success']) {
            $errores = $json['error-codes'] ?? [];
            $mensaje = in_array('timeout-or-duplicate', $errores)
                ? 'El reCAPTCHA ha expirado. Intente nuevamente.'
                : 'Error de validación del reCAPTCHA.';
            return ['success' => false, 'error' => $mensaje, 'detalles' => $errores];
        }

        return ['success' => true, 'error' => null];
    }
}