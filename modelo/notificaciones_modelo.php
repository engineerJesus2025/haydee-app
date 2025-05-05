<?php
require_once "modelo/conexion.php";

class Notificaciones extends Conexion
{
    private $id_notificacion;
    private $titulo;
    private $contenido;
    private $usuario_id;
    private $visto;

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

    public function set_contenido($contenido)
    {
        $this->contenido = $contenido;
    }

    public function get_contenido()
    {
        return $this->contenido;
    }

    public function set_usuario_id($usuario_id)
    {
        $this->usuario_id = $usuario_id;
    }

    public function get_usuario_id()
    {
        return $this->usuario_id;
    }

    public function set_visto($visto)
    {
        $this->visto = $visto;
    }

    public function get_visto()
    {
        return $this->visto;
    }

    public function consultar()
    {
        $sql = "SELECT id_notificacion, titulo, contenido, usuario_id,
            visto, id_usuario, nombre , usuarios.apellido FROM notificaciones INNER JOIN usuarios ON 
            usuarios.id_usuario = notificaciones.usuario_id";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return [];
        }
    }

    public function consultar_notificaciones($usuario)
    {
        $sql = "SELECT id_notificacion, titulo, contenido, usuario_id,
            visto, id_usuario, nombre , usuarios.apellido FROM notificaciones INNER JOIN usuarios ON 
            usuarios.id_usuario = notificaciones.usuario_id WHERE visto = 0 AND usuarios.id_usuario = :usuario";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":usuario", $usuario);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return [];
        }
    }

    public function verificar_vacaciones($fecha)
    {
        $sql = "SELECT * FROM vacaciones WHERE estatus = 'En proceso' AND fecha_culminacion < :fecha";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":fecha", $fecha);
        $result = $conexion->execute();
        //$datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        $result = $conexion->rowCount();

        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function agregar_notificacion($titulo,$mensaje,$usuario)
    {
        $sql ="INSERT INTO notificaciones(titulo, contenido, usuario_id, visto) VALUES (:titulo,:contenido,:usuario_id,0)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":titulo", $titulo);
        $conexion->bindParam(":contenido", $mensaje);
        $conexion->bindParam(":usuario_id", $usuario);
        $result = $conexion->execute();

        return $result;
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function quitar_notificacion($id_notificacion)
    {
        $sql = "UPDATE notificaciones SET visto = 1 WHERE id_notificacion = :id_notificacion";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_notificacion", $id_notificacion);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return true;
        } else {
            return false;
        }
    }




}
