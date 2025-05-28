<?php
require_once "modelo/conexion.php";

class Notificaciones extends Conexion
{
    private $id_notificacion;
    private $titulo;
    private $descripcion;
    private $fecha;
    private $usuario_id;
    private $leida;

    public function __construct()
    {
        parent::__construct();

    }

    public function set_id_notificacion($id_notificacion)
    {
        $this->id_notificacion = $id_notificacion;
    }

    public function get_id_notificacion()
    {
        return $this->id_notificacion;
    }

    public function set_titulo($titulo)
    {
        $this->titulo = $titulo;
    }

    public function get_titulo()
    {
        return $this->titulo;
    }

    public function set_descripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    public function get_descripcion()
    {
        return $this->descripcion;
    }
        public function set_fecha($fecha)
    {
        $this->fecha = $fecha;
    }

    public function get_fecha()
    {
        return $this->fecha;
    }

    public function set_usuario_id($usuario_id)
    {
        $this->usuario_id = $usuario_id;
    }

    public function get_usuario_id()
    {
        return $this->usuario_id;
    }

    public function set_leida($leida)
    {
        $this->leida = $leida;
    }

    public function get_leida()
    {
        return $this->leida;
    }

    public function consultar()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT id_notificacion, titulo, descripcion, fecha, usuario_id,
            leida, id_usuario, nombre, usuarios.apellido FROM notificaciones INNER JOIN usuarios ON 
            usuarios.id_usuario = notificaciones.usuario_id";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();
        if ($result == true) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_notificaciones($usuario)
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT id_notificacion, titulo, descripcion, fecha, usuario_id,
            leida, id_usuario, nombre , usuarios.apellido FROM notificaciones INNER JOIN usuarios ON 
            usuarios.id_usuario = notificaciones.usuario_id WHERE leida = 0 AND usuarios.id_usuario = :usuario";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":usuario", $usuario);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

    public function agregar_notificacion($titulo,$mensaje,$fecha,$usuario)
    {
        $this->cambiar_db_seguridad();
        $sql ="INSERT INTO notificaciones(titulo, descripcion, fecha, usuario_id, leida) VALUES (:titulo,:descripcion, :fecha, :usuario_id,0)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":titulo", $titulo);
        $conexion->bindParam(":descripcion", $mensaje);
        $conexion->bindParam(":fecha", $fecha);
        $conexion->bindParam(":usuario_id", $usuario);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        return $result;
        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar esta notificacion"];
        }
    }

    public function marcar_como_leida($id_notificacion)
    {
        $this->cambiar_db_seguridad();
        $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id_notificacion";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_notificacion", $id_notificacion);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();
        if ($result == true) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar editar esta notificacion"];
        }
    }




}
