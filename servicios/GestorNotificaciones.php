<?php
namespace haydee\servicios;

use haydee\modelo\Notificaciones;
use haydee\modelo\SuscripcionPush;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class GestorNotificaciones
{
    /**
     * Envía una notificación a todos los usuarios correspondientes.
     */
    public static function notificarTodos($titulo, $descripcion, $tabla_origen, $id_registro, $tipo_evento)
    {
        return self::enviar($titulo, $descripcion, $tabla_origen, $id_registro, $tipo_evento, 'notificar_todos');
    }

    /**
     * Envía una notificación únicamente a los administradores.
     */
    public static function notificarAdmins($titulo, $descripcion, $tabla_origen, $id_registro, $tipo_evento)
    {
        return self::enviar($titulo, $descripcion, $tabla_origen, $id_registro, $tipo_evento, 'notificar_evento_admins');
    }

    /**
     * Método privado centralizado para evitar repetir la instanciación.
     */
    private static function enviar($titulo, $descripcion, $tabla_origen, $id_registro, $tipo_evento, $accion)
    {
        $notif = new Notificaciones();
        $notif->set_titulo($titulo);
        $notif->set_descripcion($descripcion);
        $notif->set_tabla_origen($tabla_origen);
        $notif->set_id_registro_origen($id_registro);
        $notif->set_tipo_evento($tipo_evento);
        
        $resultado = $notif->realizar_consulta($accion);

        // ==================================================================
        // PUSH
        // ==================================================================
        if (isset($resultado['estatus']) && $resultado['estatus'] === true) {
            self::dispararPush($titulo, $descripcion, $accion);
        }

        return $resultado;
    }

    /**
     * Lógica para encolar y enviar notificaciones Web Push
     */
    private static function dispararPush($titulo, $descripcion, $accion_original)
    {
        // Configurar credenciales VAPID 
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:' . CORREO_CONDOMINIO,
                'publicKey' => VAPID_PUBLIC_KEY ?? '',
                'privateKey' => VAPID_PRIVATE_KEY ?? ''
            ]
        ];

        if (empty($auth['VAPID']['publicKey']) || empty($auth['VAPID']['privateKey'])) {
            error_log("Error Push: Llaves VAPID no configuradas en el archivo .env");
            return false;
        }

        // Delegamos la consulta a la base de datos a nuestro MODELO
        $modeloPush = new SuscripcionPush();
        
        // Mapeamos la acción original a la acción del modelo
        $accionModelo = ($accion_original === 'notificar_todos') ? 'obtener_todos' : 'obtener_admins';
        
        // Ejecutamos la consulta siguiendo tu estándar
        $respuestaModelo = $modeloPush->realizar_consulta($accionModelo);

        // Si falló la consulta o no hay datos (arreglo vacío), detenemos el envío
        if (!$respuestaModelo['estatus'] || empty($respuestaModelo['datos'])) {
            return false; 
        }

        $suscripcionesDB = $respuestaModelo['datos'];

        // Preparar la librería y el mensaje
        $webPush = new WebPush($auth);
        $payload = json_encode([
            'titulo' => $titulo,
            'descripcion' => $descripcion
        ]);

        // Encolar los envíos
        foreach ($suscripcionesDB as $row) {
            $sub = Subscription::create([
                'endpoint' => $row['endpoint'],
                'keys' => [
                    'p256dh' => $row['p256dh'],
                    'auth' => $row['auth']
                ]
            ]);
            $webPush->queueNotification($sub, $payload);
        }

        // Ejecutar el envío a todos los endpoints
        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                error_log("Push fallido a {$endpoint}: {$report->getReason()}");
            }
        }

        return true;
    }
}
