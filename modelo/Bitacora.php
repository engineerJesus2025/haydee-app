<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Bitacora extends Conexion
{
    private static $instancia = null;
    
    private $id_bitacora;
    private $fecha_hora;
    private $accion;
    private $registro_alterado;
    private $usuario_id;
    private $modulo_id;

    public function set_id_bitacora($id_bitacora) { $this->id_bitacora = $id_bitacora; }
    public function get_id_bitacora() { return $this->id_bitacora; }
    public function set_fecha_hora($fecha_hora) { $this->fecha_hora = $fecha_hora; }
    public function get_fecha_hora() { return $this->fecha_hora; }
    public function set_accion($accion) { $this->accion = $accion; }
    public function get_accion() { return $this->accion; }
    public function set_registro_alterado($registro_alterado) { $this->registro_alterado = $registro_alterado; }
    public function get_registro_alterado() { return $this->registro_alterado; }
    public function set_usuario_id($usuario_id) { $this->usuario_id = $usuario_id; }
    public function get_usuario_id() { return $this->usuario_id; }
    public function set_modulo_id($modulo_id) { $this->modulo_id = $modulo_id; }
    public function get_modulo_id() { return $this->modulo_id; }

    public function realizar_consulta($accion, $datos = null)
    {
        // Construimos el nombre del método privado: _consultar, _insertar, etc.
        $metodo = '_' . $accion;
        
        if (!method_exists($this, $metodo)) {
            return [
                'estatus' => false,
                'mensaje' => "La acción '$accion' no está implementada."
            ];
        }

        return $this->$metodo($datos);
    }

    private function _consultar()
    {
        $sql = "SELECT 
                    bitacora.fecha_hora, 
                    bitacora.accion, 
                    bitacora.registro_alterado,
                    usuarios.nombre AS nombre_usuario,
                    modulos.nombre AS nombre_modulo,
                    roles.nombre AS nombre_rol
                FROM bitacora
                INNER JOIN usuarios ON usuarios.id_usuario = bitacora.usuario_id
                INNER JOIN modulos ON modulos.id_modulo = bitacora.modulo_id
                INNER JOIN roles ON roles.id_rol = usuarios.rol_id
                ORDER BY id_bitacora DESC";

        try {
            $conexion = $this->get_conex('seguridad')->prepare($sql);

            $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
            return [
                'estatus' => true,
                'mensaje' => 'Consulta exitosa',
                'datos' => $datos
            ];
        } catch (PDOException $e) {
            error_log("Error en _consultar de Bitacora: " . $e->getMessage());
            return [
                'estatus' => false,
                'mensaje' => 'Error al consultar la bitácora'
            ];
        }
    }

    // --- Método estático para registrar en bitácora desde cualquier parte ---

    private static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public static function registrar($accion, $moduloId, $registroAlt, $usuarioId = null)
    {
        $instancia = self::getInstancia();
        $pdo = $instancia->get_conex('seguridad');

        if ($usuarioId === null && isset($_SESSION['id_usuario'])) {
            $usuarioId = $_SESSION['id_usuario'];
        }

        if (!$usuarioId) {
            error_log("Bitácora: No se pudo registrar porque falta el usuario.");
            return false;
        }

        $sql = "INSERT INTO bitacora (fecha_hora, accion, registro_alterado, usuario_id, modulo_id)
                VALUES (NOW(), :accion, :registro_alterado, :usuario_id, :modulo_id)";

        try {
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':accion', $accion);
            $stmt->bindParam(':registro_alterado', $registroAlt);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->bindParam(':modulo_id', $moduloId);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al registrar en bitácora: " . $e->getMessage());
            return false;
        }
    }
}