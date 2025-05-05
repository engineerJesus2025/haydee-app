<?php
require_once "modelo/conexion.php";

class Bitacora extends Conexion
{
    private $id_bitacora;
    private $fecha_hora;
    private $accion;
    private $ip;
    private $usuario_id;
    private $modulo_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_bitacora($id_bitacora)
    {
        $this->id_bitacora = $id_bitacora;
    }

    public function get_id_bitacora()
    {
        return $this->id_bitacora;
    }

    public function set_fecha_hora($fecha_hora)
    {
        $this->fecha_hora = $fecha_hora;
    }

    public function get_fecha_hora()
    {
        return $this->fecha_hora;
    }

    public function set_accion($accion)
    {
        $this->accion = $accion;
    }

    public function get_accion()
    {
        return $this->accion;
    }

    public function set_ip($ip)
    {
        $this->ip = $ip;
    }

    public function get_ip()
    {
        return $this->ip;
    }

    public function set_usuario_id($usuario_id)
    {
        $this->usuario_id = $usuario_id;
    }

    public function get_usuario_id()
    {
        return $this->usuario_id;
    }

    public function set_modulo_id($modulo_id)
    {
        $this->modulo_id = $modulo_id;
    }

    public function get_modulo_id()
    {
        return $this->modulo_id;
    }

    public function consultar()
    {
        $sql = "SELECT bitacora.id_bitacora, bitacora.fecha_hora, bitacora.accion, bitacora.ip,
            bitacora.usuario_id, bitacora.modulo_id, usuarios.id_usuario, usuarios.nombre AS 
            nombre_usuario, usuarios.apellido, modulos.id_modulo,
            modulos.nombre AS nombre_modulo FROM bitacora INNER JOIN usuarios ON 
            usuarios.id_usuario = bitacora.usuario_id INNER JOIN
            modulos ON modulos.id_modulo = bitacora.modulo_id ORDER BY id_bitacora DESC";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return "";
        }
    }
}
