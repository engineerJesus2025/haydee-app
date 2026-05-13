<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\TipoEventoNotificacion;
use haydee\enums\TablaOrigen;
use haydee\enums\TipoBaseDatos;

class Notificaciones extends Conexion
{
    // Tabla: notificaciones
    private $id_notificacion;
    private $titulo;
    private $descripcion;
    private $fecha;
    private $leido;      // 0 o 1
    private $usuario_id; // Destinatario

    // Propiedades Virtuales para Eventos (Opcionales)
    private $tipo_evento;
    private $tabla_origen;
    private $id_registro_origen;

    private $rol_nombre;
    private $usuario_excluir;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $eventosValidos = implode('|', array_column(TipoEventoNotificacion::cases(), 'value'));
        $tablasValidas = implode('|', array_column(TablaOrigen::cases(), 'value'));

        $reglasGenerales = [
            'id_notificacion' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'notificaciones', 'campo' => 'id_notificacion']
            ],
            'titulo' => [
                'regex' => '/^[A-Za-z0-9 áéíóúÁÉÍÓÚñÑ\.,-]{3,100}$/'
            ],
            'descripcion' => [
                'regex' => '/^.{3,255}$/'
            ],
            'usuario_id' => [
                'regex' => '/^\d+$/'
            ],
            'fecha' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/',
                'opcional' => true
            ],
            'id_registro_origen' => [
                'regex' => '/^\d+$/',
                'opcional' => true
            ],
            'tipo_evento' => [
                'regex' => "/^($eventosValidos)$/"
            ],
            'tabla_origen' => [
                'regex' => "/^($tablasValidas)$/"
            ]
        ];

        $camposPorOperacion = [
            'marcar_leida' => ['id_notificacion'],
            'registrar_notificacion' => ['titulo', 'descripcion', 'tipo_evento', 'tabla_origen']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // ====================================================================
    // GETTERS Y SETTERS
    // ====================================================================
    public function set_id_notificacion($id) { $this->id_notificacion = $id; }
    public function get_id_notificacion() { return $this->id_notificacion; }

    public function set_titulo($t) { $this->titulo = $t; }
    public function get_titulo() { return $this->titulo; }

    public function set_descripcion($d) { $this->descripcion = $d; }
    public function get_descripcion() { return $this->descripcion; }

    public function set_fecha($f) { $this->fecha = $f; }
    public function get_fecha() { return $this->fecha; }

    public function set_usuario_id($id) { $this->usuario_id = $id; }
    public function get_usuario_id() { return $this->usuario_id; }

    public function set_leido($l) { $this->leido = $l; }
    public function get_leido() { return $this->leido; }

    // Setters Eventos
    public function set_tipo_evento($t) { $this->tipo_evento = $t; }
    public function get_tipo_evento() { return $this->tipo_evento; }
    public function set_tabla_origen($t) { $this->tabla_origen = $t; }
    public function get_tabla_origen() { return $this->tabla_origen; }
    public function set_id_registro_origen($id) { $this->id_registro_origen = $id; }
    public function get_id_registro_origen() { return $this->id_registro_origen; }

    public function set_rol_nombre($r) { $this->rol_nombre = $r; }
    public function get_rol_nombre() { return $this->rol_nombre; }
    public function set_usuario_excluir($u) { $this->usuario_excluir = $u; }
    public function get_usuario_excluir() { return $this->usuario_excluir; }

    // ====================================================================
    // ENRUTADOR CON MANEJO DE EXCEPCIONES
    // ====================================================================
    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            return ['estatus' => false, 'mensaje' => "La acción '$accion' no está implementada."];
        }

        try {
            return $this->$metodo();
        } catch (\Exception $e) {
            error_log("Error en realizar_consulta ($accion): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error interno: ' . $e->getMessage()];
        }
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO
    // ====================================================================

    /**
     * REGISTRO SIMPLE (1 a 1)
     */
    private function _registrar_simple()
    {
        // Asignar fecha si no se ha establecido
        if ($this->fecha === null) {
            $this->fecha = date('Y-m-d');
        }

        try {
            $sql = "INSERT INTO notificaciones (titulo, descripcion, fecha, usuario_id, leido) 
                    VALUES (:tit, :desc, :fecha, :uid, 0)";
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':tit'   => $this->titulo,
                ':desc'  => $this->descripcion,
                ':fecha' => $this->fecha,
                ':uid'   => $this->usuario_id
            ]);
            $lastId = $this->get_conex(TipoBaseDatos::SEGURIDAD)->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Notificación enviada', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar_simple: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al enviar notificación: ' . $e->getMessage()];
        }
    }

    /**
     * Notificar a todos los usuarios activos, opcionalmente asociado a un evento.
     * Requiere: titulo, descripcion.
     * Si se desea asociar un evento, deben establecerse además:
     *   - tabla_origen
     *   - id_registro_origen
     *   - tipo_evento
     * @return array ['estatus' => bool, 'mensaje' => string]
     */
    private function _notificar_todos()
    {
        // Determinar si se incluirá evento (deben estar los tres campos)
        $incluirEvento = !empty($this->tabla_origen) && !empty($this->id_registro_origen) && !empty($this->tipo_evento);

        $con = $this->get_conex(TipoBaseDatos::SEGURIDAD);

        try {
            // Si hay evento, trabajamos con transacción
            if ($incluirEvento) {
                $con->beginTransaction();

                // 1. Insertar evento
                $sqlEvento = "INSERT INTO eventos_sistema (tipo_evento, tabla_origen, id_registro_origen, fecha_evento)
                              VALUES (:tipo, :tabla, :id_reg, NOW())";
                $stmtEvento = $con->prepare($sqlEvento);
                $stmtEvento->execute([
                    ':tipo'   => $this->tipo_evento,
                    ':tabla'  => $this->tabla_origen,
                    ':id_reg' => $this->id_registro_origen
                ]);
                $idEvento = $con->lastInsertId();

                // 2. Obtener todos los usuarios activos
                $sqlUsuarios = "SELECT id_usuario FROM usuarios WHERE activo = 1";
                $stmtUsu = $con->query($sqlUsuarios);
                $usuarios = $stmtUsu->fetchAll(PDO::FETCH_COLUMN);

                if (empty($usuarios)) {
                    $con->commit();
                    return ['estatus' => true, 'mensaje' => 'No hay usuarios activos para notificar'];
                }

                // 3. Insertar notificaciones y relaciones
                $sqlNotif = "INSERT INTO notificaciones (titulo, descripcion, fecha, usuario_id, leido)
                             VALUES (:tit, :desc, NOW(), :uid, 0)";
                $stmtNotif = $con->prepare($sqlNotif);

                $sqlRel = "INSERT INTO notificacion_evento (notificacion_id, evento_id) VALUES (:nid, :eid)";
                $stmtRel = $con->prepare($sqlRel);

                $contador = 0;
                foreach ($usuarios as $uid) {
                    $stmtNotif->execute([
                        ':tit'   => $this->titulo,
                        ':desc'  => $this->descripcion,
                        ':uid'   => $uid
                    ]);
                    $idNotif = $con->lastInsertId();

                    $stmtRel->execute([
                        ':nid' => $idNotif,
                        ':eid' => $idEvento
                    ]);
                    $contador++;
                }

                $con->commit();
                return [
                    'estatus' => true,
                    'mensaje' => "Notificaciones enviadas a $contador usuarios (evento asociado)"
                ];
            } else {
                // Sin evento: inserción masiva directa (más rápida)
                $sql = "INSERT INTO notificaciones (titulo, descripcion, fecha, usuario_id, leido)
                        SELECT :tit, :desc, NOW(), id_usuario, 0
                        FROM usuarios
                        WHERE activo = 1";
                $stmt = $con->prepare($sql);
                $stmt->execute([
                    ':tit'   => $this->titulo,
                    ':desc'  => $this->descripcion
                ]);
                $filas = $stmt->rowCount();
                return [
                    'estatus' => true,
                    'mensaje' => "Notificaciones enviadas a $filas usuarios (sin evento)"
                ];
            }
        } catch (PDOException $e) {
            if ($incluirEvento && $con->inTransaction()) {
                $con->rollBack();
            }
            error_log("Error en _notificar_todos: " . $e->getMessage());
            return [
                'estatus' => false,
                'mensaje' => 'Error al notificar a todos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * NOTIFICAR PAGO (Masivo a Admins)
     */
    private function _notificar_pago()
    {
        try {
            $sql = "INSERT INTO notificaciones (titulo, descripcion, fecha, leido, usuario_id)
                    SELECT :tit, :desc, NOW(), 0, id_usuario
                    FROM usuarios 
                    WHERE rol_id IN (1, 2) AND activo = 1";
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':tit'   => $this->titulo,
                ':desc'  => $this->descripcion
            ]);
            return ['estatus' => true, 'mensaje' => 'Administradores notificados del pago'];
        } catch (PDOException $e) {
            error_log("Error en _notificar_pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al notificar pago: ' . $e->getMessage()];
        }
    }

    /**
     *  (1 a Muchos con Enlace)
     */
    private function _notificar_evento_admins()
    {
        try {
            $sql = "CALL sp_notificar_administradores(:tit, :desc, :tabla, :id_reg, :tipo)";
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':tit'    => $this->titulo,
                ':desc'   => $this->descripcion,
                ':tabla'  => $this->tabla_origen,
                ':id_reg' => $this->id_registro_origen,
                ':tipo'   => $this->tipo_evento ?? 'Sistema'
            ]);
            return ['estatus' => true, 'mensaje' => 'Evento registrado y admins notificados'];
        } catch (PDOException $e) {
            error_log("Error en _notificar_evento_admins: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en evento: ' . $e->getMessage()];
        }
    }

    /**
     * Notifica a todos los usuarios de un rol específico.
     * Requiere: titulo, descripcion, tabla_origen, id_registro_origen, tipo_evento, rol_nombre.
     * Opcional: usuario_excluir (no notificar a ese usuario).
     * @return array ['estatus' => bool, 'mensaje' => string]
     */
    private function _notificar_por_rol()
    {
        $con = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        try {
            $con->beginTransaction();

            // Insertar evento
            $sqlEvento = "INSERT INTO eventos_sistema (tipo_evento, tabla_origen, id_registro_origen, fecha_evento) 
                          VALUES (:tipo, :tabla, :id_reg, NOW())";
            $stmtEvento = $con->prepare($sqlEvento);
            $stmtEvento->execute([
                ':tipo'  => $this->tipo_evento,
                ':tabla' => $this->tabla_origen,
                ':id_reg'=> $this->id_registro_origen
            ]);
            $idEvento = $con->lastInsertId();

            // Obtener usuarios del rol
            $sqlUsuarios = "SELECT id_usuario FROM usuarios 
                            WHERE rol_id IN (SELECT id_rol FROM roles WHERE nombre = :rol) 
                            AND activo = 1";
            if (!empty($this->usuario_excluir)) {
                $sqlUsuarios .= " AND id_usuario != :excluir";
            }
            $stmtUsu = $con->prepare($sqlUsuarios);
            $params = [':rol' => $this->rol_nombre];
            if (!empty($this->usuario_excluir)) {
                $params[':excluir'] = $this->usuario_excluir;
            }
            $stmtUsu->execute($params);
            $usuarios = $stmtUsu->fetchAll(PDO::FETCH_COLUMN);

            if (empty($usuarios)) {
                $con->commit();
                return ['estatus' => true, 'mensaje' => 'No hay usuarios de ese rol para notificar'];
            }

            // Insertar notificaciones
            $sqlNotif = "INSERT INTO notificaciones (titulo, descripcion, fecha, usuario_id, leido) 
                         VALUES (:tit, :desc, NOW(), :uid, 0)";
            $stmtNotif = $con->prepare($sqlNotif);

            $sqlRel = "INSERT INTO notificacion_evento (notificacion_id, evento_id) VALUES (:nid, :eid)";
            $stmtRel = $con->prepare($sqlRel);

            foreach ($usuarios as $uid) {
                $stmtNotif->execute([
                    ':tit'  => $this->titulo,
                    ':desc' => $this->descripcion,
                    ':uid'  => $uid
                ]);
                $idNotif = $con->lastInsertId();

                $stmtRel->execute([
                    ':nid' => $idNotif,
                    ':eid' => $idEvento
                ]);
            }

            $con->commit();
            return ['estatus' => true, 'mensaje' => 'Notificaciones enviadas correctamente'];

        } catch (PDOException $e) {
            $con->rollBack();
            error_log("Error en _notificar_por_rol: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al enviar notificaciones: ' . $e->getMessage()];
        }
    }

    /**
     * Consultar notificaciones de un usuario (con datos de evento si existen)
     */
    private function _consultar_mis_notificaciones()
    {
        $sql = "SELECT n.id_notificacion, n.titulo, n.descripcion, n.fecha, n.leido,
                       e.tipo_evento, e.tabla_origen, e.id_registro_origen
                FROM notificaciones n
                LEFT JOIN notificacion_evento ne ON n.id_notificacion = ne.notificacion_id
                LEFT JOIN eventos_sistema e ON ne.evento_id = e.id_evento
                WHERE n.usuario_id = :uid
                ORDER BY n.leido ASC, n.fecha DESC
                LIMIT 50";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':uid' => $this->usuario_id]);
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_mis_notificaciones: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar notificaciones'];
        }
    }

    /**
     * Marcar una como leída
     */
    private function _marcar_leida()
    {
        try {
            $sql = "UPDATE notificaciones SET leido = 1 WHERE id_notificacion = :id";
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':id' => $this->id_notificacion]);
            return ['estatus' => true, 'mensaje' => 'Notificación marcada como leída'];
        } catch (PDOException $e) {
            error_log("Error en _marcar_leida: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al marcar notificación'];
        }
    }

    /**
     * Marcar todas como leídas
     */
    private function _marcar_todas_leidas()
    {
        try {
            $sql = "UPDATE notificaciones SET leido = 1 WHERE usuario_id = :uid AND leido = 0";
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':uid' => $this->usuario_id]);
            return ['estatus' => true, 'mensaje' => 'Todas las notificaciones marcadas como leídas'];
        } catch (PDOException $e) {
            error_log("Error en _marcar_todas_leidas: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al marcar notificaciones'];
        }
    }
}
?>