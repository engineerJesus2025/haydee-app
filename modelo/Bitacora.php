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
    private $valores_anteriores;
    private $valores_nuevos;

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

    public function set_valores_anteriores($valores_anteriores) { $this->valores_anteriores = $valores_anteriores; }
    public function get_valores_anteriores() { return $this->valores_anteriores; }

    public function set_valores_nuevos($valores_nuevos) { $this->valores_nuevos = $valores_nuevos; }
    public function get_valores_nuevos() { return $this->valores_nuevos; }

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
                    bitacora.id_bitacora,
                    bitacora.fecha_hora,
                    bitacora.accion,
                    bitacora.registro_alterado,
                    bitacora.valores_anteriores,
                    bitacora.valores_nuevos,
                    usuarios.nombre AS nombre_usuario,
                    modulos.nombre AS nombre_modulo,
                    roles.nombre AS nombre_rol
                FROM
                    bitacora
                INNER JOIN usuarios ON usuarios.id_usuario = bitacora.usuario_id
                INNER JOIN modulos ON modulos.id_modulo = bitacora.modulo_id
                INNER JOIN roles ON roles.id_rol = usuarios.rol_id
                ORDER BY
                    id_bitacora
                DESC";

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

    // En la clase Bitacora, modificar el método registrar:

public static function registrar($accion, $moduloId, $registroAlt = null, $usuarioId = null, $valores_anteriores = null, $valores_nuevos = null)
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

    // Convertir arrays a JSON, si no se proporcionan, usar '{}'
    $anteriores_json = is_array($valores_anteriores) ? json_encode($valores_anteriores, JSON_UNESCAPED_UNICODE) : '{}';
    $nuevos_json = is_array($valores_nuevos) ? json_encode($valores_nuevos, JSON_UNESCAPED_UNICODE) : '{}';

    $sql = "INSERT INTO bitacora (fecha_hora, accion, registro_alterado, usuario_id, modulo_id, valores_anteriores, valores_nuevos)
            VALUES (NOW(), :accion, :registro_alterado, :usuario_id, :modulo_id, :anteriores, :nuevos)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':accion', $accion);
        $stmt->bindParam(':registro_alterado', $registroAlt);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->bindParam(':modulo_id', $moduloId);
        $stmt->bindParam(':anteriores', $anteriores_json);
        $stmt->bindParam(':nuevos', $nuevos_json);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al registrar en bitácora: " . $e->getMessage());
        return false;
    }
}
    
}