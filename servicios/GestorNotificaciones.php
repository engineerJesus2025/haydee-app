<?php
namespace haydee\servicios;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use haydee\enums\TipoEventoNotificacion;
use haydee\modelo\Notificaciones;
use haydee\modelo\SuscripcionPush;
use haydee\modelo\SuscripcionPushMovil;

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

        // PUSH
        if (isset($resultado['estatus']) && $resultado['estatus'] === true) {
            self::dispararPush($titulo, $descripcion, $accion, $tabla_origen, $id_registro);
            self::dispararPushMovil($titulo, $descripcion, $accion, $tabla_origen, $id_registro, $tipo_evento);
        }

        return $resultado;
    }

    /**
     * Lógica para encolar y enviar notificaciones Web Push
     */
    private static function dispararPush($titulo, $descripcion, $accion_original, $tabla_origen, $id_registro)
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
        
        // Construimos la ruta dinámica idéntica a la que usa tu JS
        $urlDestino = "?pagina={$tabla_origen}&buscar={$id_registro}";

        // Añadimos el objeto 'data' al payload
        $payload = json_encode([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'data' => [
                'url' => $urlDestino
            ]
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

    /**
     * Lógica para consultar y enviar notificaciones a React Native (Expo)
     */
    private static function dispararPushMovil($titulo, $descripcion, $accion_original, $tabla_origen, $id_registro, $tipo_evento)
    {
        $modeloPushMovil = new SuscripcionPushMovil();
        $accionModelo = ($accion_original === 'notificar_todos') ? 'obtener_todos' : 'obtener_admins';
        $respuestaModelo = $modeloPushMovil->realizar_consulta($accionModelo);

        if (!$respuestaModelo['estatus'] || empty($respuestaModelo['datos'])) {
            return false; 
        }

        $suscripcionesDB = $respuestaModelo['datos'];
        $mensajes = [];

        $configEvento = self::obtenerConfiguracionEvento($tipo_evento, $tabla_origen);

        foreach ($suscripcionesDB as $row) {
            $mensajes[] = [
                "to" => $row['expo_token'],
                "sound" => ($configEvento['canal'] === 'haydee-silencioso') ? null : "default",
                "title" => $titulo,
                "body" => $descripcion,
                "channelId" => ($row['plataforma'] === 'ANDROID') ? $configEvento['canal'] : null,
                "data" => [
                    "ruta" => $configEvento['ruta'],
                    "id_registro" => $id_registro,
                    "tabla_origen" => $tabla_origen
                ]
            ];
        }

        // Hacer la petición HTTP a los servidores de Expo mediante cURL
        $ch = curl_init('https://exp.host/--/api/v2/push/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Accept-encoding: gzip, deflate',
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensajes));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // IMPRIMIR EL RECIBO DE EXPO EN EL LOG DE PHP -- QUITAR DESPUESSS
        error_log("Recibo de Expo: " . $respuesta);

        // Manejo básico de errores
        if ($httpCode !== 200) {
            error_log("Push Móvil fallido. Código HTTP: {$httpCode}. Respuesta: {$respuesta}");
            return false;
        }

        return true;
    }

    private static function obtenerConfiguracionEvento($tipo_evento, $tabla_origen) 
    {
        $mapaEventos = [
            TipoEventoNotificacion::NUEVA_PUBLICACION->value => ['canal' => 'haydee-urgente', 'ruta' => 'DetalleCartelera'],
            TipoEventoNotificacion::EMERGENCIA->value        => ['canal' => 'haydee-urgente', 'ruta' => 'Inicio'],
            TipoEventoNotificacion::PAGO_RECIBIDO->value     => ['canal' => 'haydee-silencioso', 'ruta' => 'DetallePago'],
            TipoEventoNotificacion::BAJO_SALDO->value        => ['canal' => 'haydee-silencioso', 'ruta' => 'Inicio'],
            TipoEventoNotificacion::NUEVA_MENSUALIDAD->value => ['canal' => 'haydee-default', 'ruta' => 'Mensualidad'], 
        ];

        return $mapaEventos[$tipo_evento] ?? ['canal' => 'haydee-default', 'ruta' => 'Inicio'];
    }
}
