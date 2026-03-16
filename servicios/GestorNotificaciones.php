<?php
namespace haydee\servicios;

use haydee\modelo\Notificaciones;

class GestorNotificaciones
{
    /**
     * Envía una notificación a todos los usuarios correspondientes.
     * * @param string $titulo Título de la notificación.
     * @param string $descripcion Descripción detallada.
     * @param string $tabla_origen Nombre de la tabla relacionada.
     * @param int $id_registro ID del registro para resaltar.
     * @param string $tipo_evento Código del evento del sistema.
     * @return array Resultado de la operación.
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
        
        return $notif->realizar_consulta($accion);
    }
}
?>