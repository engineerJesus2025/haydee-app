<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Notificaciones extends Conexion
{
    // ====================================================================
    // PROPIEDADES
    // ====================================================================

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

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    private $reglas = [
        'id_notificacion' => [
            'regex' => '/^\d+$/'
        ],
        'titulo' => [
            'regex' => '/^[A-Za-z0-9 áéíóúÁÉÍÓÚñÑ\.,-]{3,100}$/'
        ],
        'descripcion' => [
            'regex' => '/^.{3,255}$/'
        ],
        'fecha' => [
            'regex' => '/^\d{4}-\d{2}-\d{2}$/',
            'opcional' => true
        ],
        'usuario_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'usuarios', 'campo' => 'id_usuario']
        ],
        'tabla_origen' => [
            'regex' => '/^[a-z_]+$/',
            'opcional' => true
        ],
        'id_registro_origen' => [
            'regex' => '/^\d+$/',
            'opcional' => true
        ],
        'tipo_evento' => [
            'regex' => '/^[A-Za-z0-9_]{3,30}$/',
            'opcional' => true
        ]
    ];

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
    // VALIDACIÓN CENTRALIZADA (MEJORADA)
    // ====================================================================
    private function validar($campos)
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) {
                continue;
            }
            $regla = $this->reglas[$campo];

            $getter = 'get_' . $campo;
            if (!method_exists($this, $getter)) {
                return ['estatus' => false, 'mensaje' => "Getter no encontrado para $campo."];
            }
            $valor = $this->$getter();

            // Determinar si el campo es requerido (por defecto sí, a menos que sea opcional)
            $requerido = !(isset($regla['opcional']) && $regla['opcional'] === true);

            if ($requerido) {
                if ($valor === null) {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' es requerido y no se ha establecido."];
                }
                if (is_string($valor) && trim($valor) === '') {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' no puede estar vacío."];
                }
            } else {
                // Si es opcional y está vacío (considerando que 0 no es vacío), saltamos validaciones adicionales
                if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                    continue;
                }
            }

            // Validar expresión regular
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' no tiene un formato válido."];
            }

            // Validar existencia en otra tabla (foránea)
            if (isset($regla['exists'])) {
                $tabla = $regla['exists']['tabla'];
                $campoFor = $regla['exists']['campo'] ?? $campo;
                if (!$this->existeEnTabla($tabla, $campoFor, $valor)) {
                    return ['estatus' => false, 'mensaje' => "El valor del campo '$campo' no existe en la tabla $tabla."];
                }
            }
        }
        return ['estatus' => true];
    }

    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        $stmt = $this->get_conex('seguridad')->prepare($sql);
        $stmt->execute([':valor' => $valor]);
        return $stmt->fetchColumn() > 0;
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO
    // ====================================================================

    /**
     * REGISTRO SIMPLE (1 a 1)
     */
    private function _registrar_simple()
    {
        $campos = ['titulo', 'descripcion', 'usuario_id'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        // Asignar fecha si no se ha establecido
        if ($this->fecha === null) {
            $this->fecha = date('Y-m-d');
        } else {
            // Validar la fecha si fue establecida
            $valFecha = $this->validar(['fecha']);
            if (!$valFecha['estatus']) {
                return $valFecha;
            }
        }

        try {
            $sql = "INSERT INTO notificaciones (titulo, descripcion, fecha, usuario_id, leido) 
                    VALUES (:tit, :desc, :fecha, :uid, 0)";
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([
                ':tit'   => $this->titulo,
                ':desc'  => $this->descripcion,
                ':fecha' => $this->fecha,
                ':uid'   => $this->usuario_id
            ]);
            $lastId = $this->get_conex('seguridad')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Notificación enviada', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar_simple: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al enviar notificación: ' . $e->getMessage()];
        }
    }

    /**
     * NOTIFICAR PAGO (Masivo a Admins)
     */
    private function _notificar_pago()
    {
        $campos = ['titulo', 'descripcion'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        if ($this->fecha === null) {
            $this->fecha = date('Y-m-d');
        } else {
            $valFecha = $this->validar(['fecha']);
            if (!$valFecha['estatus']) {
                return $valFecha;
            }
        }

        try {
            $sql = "INSERT INTO notificaciones (titulo, descripcion, fecha, leido, usuario_id)
                    SELECT :tit, :desc, :fecha, 0, id_usuario
                    FROM usuarios 
                    WHERE rol_id IN (1, 2) AND activo = 1";
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([
                ':tit'   => $this->titulo,
                ':desc'  => $this->descripcion,
                ':fecha' => $this->fecha
            ]);
            return ['estatus' => true, 'mensaje' => 'Administradores notificados del pago'];
        } catch (PDOException $e) {
            error_log("Error en _notificar_pago: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al notificar pago: ' . $e->getMessage()];
        }
    }

    /**
     * REGISTRO INTELIGENTE (1 a Muchos con Enlace)
     */
    private function _notificar_evento_admins()
    {
        $campos = ['titulo', 'descripcion', 'tabla_origen', 'id_registro_origen'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        if ($this->tipo_evento !== null) {
            $valTipo = $this->validar(['tipo_evento']);
            if (!$valTipo['estatus']) {
                return $valTipo;
            }
        }

        try {
            $sql = "CALL sp_notificar_administradores(:tit, :desc, :tabla, :id_reg, :tipo)";
            $stmt = $this->get_conex('seguridad')->prepare($sql);
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
     * Consultar notificaciones de un usuario (con datos de evento si existen)
     */
    private function _consultar_mis_notificaciones()
    {
        $validacion = $this->validar(['usuario_id']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT n.id_notificacion, n.titulo, n.descripcion, n.fecha, n.leido,
                       e.tipo_evento, e.tabla_origen, e.id_registro_origen
                FROM notificaciones n
                LEFT JOIN notificacion_evento ne ON n.id_notificacion = ne.notificacion_id
                LEFT JOIN eventos_sistema e ON ne.evento_id = e.id_evento
                WHERE n.usuario_id = :uid
                ORDER BY n.leido ASC, n.fecha DESC
                LIMIT 50";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
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
        $validacion = $this->validar(['id_notificacion']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            $sql = "UPDATE notificaciones SET leido = 1 WHERE id_notificacion = :id";
            $stmt = $this->get_conex('seguridad')->prepare($sql);
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
        $validacion = $this->validar(['usuario_id']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            $sql = "UPDATE notificaciones SET leido = 1 WHERE usuario_id = :uid AND leido = 0";
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute([':uid' => $this->usuario_id]);
            return ['estatus' => true, 'mensaje' => 'Todas las notificaciones marcadas como leídas'];
        } catch (PDOException $e) {
            error_log("Error en _marcar_todas_leidas: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al marcar notificaciones'];
        }
    }
}
?>