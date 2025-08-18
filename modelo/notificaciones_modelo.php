<?php
require_once "modelo/conexion.php";

class Notificaciones extends Conexion
{
    private $id_notificacion;
    private $titulo;
    private $descripcion;
    private $fecha;
    private $usuario_id;
    private $activo;

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

    public function set_activo($activo)
    {
        $this->activo = $activo;
    }

    public function get_activo()
    {
        return $this->activo;
    }

    public function realizar_consulta($accion){
        $this->cambiar_db_seguridad();
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }
                
            case 'consultar_notificaciones_usuario':
                $respuesta = $this->consultar_notificaciones_usuario();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'agregar_notificacion':
                $respuesta = $this->agregar_notificacion();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta notificacion"];
                }

            case 'notificar_pago':
                $respuesta = $this->notificar_pago();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta notificacion"];
                }

            case 'marcar_como_activo':
                $respuesta = $this->marcar_como_activo();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    if ($respuesta["fila_afectada"] < 1) {
                        return ["estatus"=>false,"mensaje"=>"No se modificó ningún registro","h"=>$respuesta];
                    }
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar marcar esta notificacion"];
                }

            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    private function consultar()
    {        
        $sql = "SELECT id_notificacion, titulo, descripcion, fecha, usuario_id,
            activo, id_usuario, nombre, usuarios.apellido FROM notificaciones INNER JOIN usuarios ON 
            usuarios.id_usuario = notificaciones.usuario_id";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_notificaciones_usuario()
    {        
        $sql = "SELECT id_notificacion, titulo, descripcion FROM notificaciones WHERE notificaciones.usuario_id = :usuario_id";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":usuario_id", $this->usuario_id);

        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];        
    }

    private function agregar_notificacion()
    {        
        $sql ="INSERT INTO notificaciones(titulo, descripcion, fecha, usuario_id, activo) VALUES (:titulo,:descripcion, :fecha, :usuario_id,0)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":titulo", $this->titulo);
        $conexion->bindParam(":descripcion", $this->descripcion);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":usuario_id", $this->usuario_id);
        $result = $conexion->execute();

        return $result;
    }

    private function notificar_pago()
    {        
        $sql = "INSERT INTO notificaciones (titulo, descripcion, fecha, usuario_id, activo)
        SELECT :titulo, :descripcion, :fecha, u.id_usuario, 0
        FROM usuarios u
        WHERE u.rol_id IN (1, 2)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":titulo", $this->titulo);
        $conexion->bindParam(":descripcion", $this->descripcion);
        $conexion->bindParam(":fecha", $this->fecha);

        $result = $conexion->execute();

        return $result;
    }

    private function marcar_como_activo()
    {        
        $sql = "UPDATE notificaciones SET activo = 1 WHERE id_notificacion = :id_notificacion";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_notificacion", $this->id_notificacion);

        $result = $conexion->execute();
        
        $filas_afectadas = $conexion->rowCount();
        
        return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
    }
}
