<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;
use haydee\enums\Accion;
use haydee\enums\Modulo;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Bitacora extends Conexion
{
    private const LIMITE_WIDGET_DASHBOARD = 7;
    private const OFFSET_WIDGET_DASHBOARD = 1;

    private static $instancia = null;
    
    private $id_bitacora;
    private $fecha_hora;
    private $accion;
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
        $metodo = '_' . $accion;
        
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }

        return $this->$metodo($datos);
    }

    private function _consultar()
    {
        $sql = "SELECT
                    bitacora.id_bitacora,
                    bitacora.fecha_hora,
                    bitacora.accion,
                    bitacora.valores_anteriores,
                    bitacora.valores_nuevos,
                    usuarios.nombre AS nombre_usuario,
                    modulos.nombre AS nombre_modulo,
                    roles.nombre AS nombre_rol
                FROM bitacora
                INNER JOIN usuarios ON usuarios.id_usuario = bitacora.usuario_id
                INNER JOIN modulos ON modulos.id_modulo = bitacora.modulo_id
                INNER JOIN roles ON roles.id_rol = usuarios.rol_id
                ORDER BY id_bitacora DESC";

        $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return [
            'estatus' => true,
            'mensaje' => '',
            'datos' => $datos
        ];
    }

    private static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public static function registrar(Accion $accionElemento, Modulo $moduloElemento, $usuarioId = null, $valores_anteriores = null, $valores_nuevos = null)
    {
        $instancia = self::getInstancia();
        $pdo = $instancia->get_conex(TipoBaseDatos::SEGURIDAD);

        if ($usuarioId === null && isset($_SESSION['id_usuario'])) {
            $usuarioId = $_SESSION['id_usuario'];
        }

        if (!$usuarioId) {
            error_log("Bitácora: No se pudo registrar porque falta el usuario.");
            return false;
        }

        $accion = $accionElemento->value;
        $moduloId = $moduloElemento->value;

        $anteriores_json = is_array($valores_anteriores) ? json_encode($valores_anteriores, JSON_UNESCAPED_UNICODE) : '{}';
        $nuevos_json = is_array($valores_nuevos) ? json_encode($valores_nuevos, JSON_UNESCAPED_UNICODE) : '{}';

        $sql = "INSERT INTO bitacora (fecha_hora, accion, usuario_id, modulo_id, valores_anteriores, valores_nuevos)
                VALUES (NOW(), :accion, :usuario_id, :modulo_id, :anteriores, :nuevos)";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':accion', $accion);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->bindParam(':modulo_id', $moduloId);
            $stmt->bindParam(':anteriores', $anteriores_json);
            $stmt->bindParam(':nuevos', $nuevos_json);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al registrar en bitácora: " . $e->getMessage());
            return false;
        }
    }

    public static function cerrarConexionBitacora() {
        $instancia = self::getInstancia();
        $instancia->cerrar('seguridad');
    }

    private function _consultar_actividad_dashboard()
    {
        $limite = self::LIMITE_WIDGET_DASHBOARD;
        $offset = self::OFFSET_WIDGET_DASHBOARD;
        
        $sql = "SELECT
                    bitacora.id_bitacora,
                    bitacora.fecha_hora,
                    bitacora.accion,
                    bitacora.valores_anteriores,
                    bitacora.valores_nuevos,
                    usuarios.nombre AS nombre_usuario,
                    modulos.nombre AS nombre_modulo,
                    roles.nombre AS nombre_rol
                FROM bitacora
                INNER JOIN usuarios ON usuarios.id_usuario = bitacora.usuario_id
                INNER JOIN modulos ON modulos.id_modulo = bitacora.modulo_id
                INNER JOIN roles ON roles.id_rol = usuarios.rol_id
                ORDER BY bitacora.fecha_hora DESC
                LIMIT $limite OFFSET $offset";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($datos as &$fila) {
            $accion_original = strtoupper($fila['accion']);
            $modulo_original = strtoupper($fila['nombre_modulo']);

            $modulo_limpio = str_replace('GESTIONAR_', '', $modulo_original);
            $modulo_limpio = ucwords(strtolower(str_replace('_', ' ', $modulo_limpio)));
            
            $fila['nombre_modulo'] = $modulo_limpio;

            if (strpos($accion_original, 'REGISTRAR') !== false || strpos($accion_original, 'CREAR') !== false) {
                $fila['accion'] = 'Registró';
                $fila['descripcion'] = "Agregó un nuevo registro.";
            } elseif (strpos($accion_original, 'MODIFICAR') !== false || strpos($accion_original, 'ACTUALIZAR') !== false) {
                $fila['accion'] = 'Modificó';
                $fila['descripcion'] = "Actualizó un registro existente.";
            } elseif (strpos($accion_original, 'ELIMINAR') !== false || strpos($accion_original, 'ANULAR') !== false) {
                $fila['accion'] = 'Eliminó';
                $fila['descripcion'] = "Borró un registro del sistema.";
            } elseif (strpos($accion_original, 'CONSULTAR') !== false) {
                $fila['accion'] = 'Consultó';
                $fila['descripcion'] = "Visualizó información en el módulo.";
            } elseif (strpos($accion_original, 'INICIAR') !== false || strpos($accion_original, 'INICIO') !== false || strpos($accion_original, 'LOGIN') !== false) {
                $fila['accion'] = 'Inició sesión';
                $fila['descripcion'] = "Accedió al sistema.";
                $fila['nombre_modulo'] = "Sistema"; 
            } elseif (strpos($accion_original, 'CERRAR') !== false || strpos($accion_original, 'SALIR') !== false || strpos($accion_original, 'LOGOUT') !== false) {
                $fila['accion'] = 'Cerró sesión';
                $fila['descripcion'] = "Salió del sistema de forma segura.";
                $fila['nombre_modulo'] = "Sistema"; 
            } elseif (strpos($accion_original, 'DESCARGAR') !== false) {
                $fila['accion'] = 'Descargó';
                $fila['descripcion'] = "Generó un reporte del sistema.";
            } elseif (strpos($accion_original, 'RESPALDAR') !== false) {
                $fila['accion'] = 'Respaldó';
                $fila['descripcion'] = "Generó un respaldo del sistema.";
            } elseif (strpos($accion_original, 'RESTAURAR') !== false) {
                $fila['accion'] = 'Restauró';
                $fila['descripcion'] = "Restauró el sistema a un punto anterior.";
            } else {
                $fila['accion'] = ucfirst(strtolower(rtrim($accion_original, 'R')));
                $fila['descripcion'] = "Realizó una acción.";
            }
        }

        return ['estatus' => true, 'datos' => $datos];
    }
}