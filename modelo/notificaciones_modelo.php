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
                $validaciones = $this->validarDatos('agregar_notificacion');
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->agregar_notificacion();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta notificacion"];
                }

            case 'notificar_pago':
                $validaciones = $this->validarDatos();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->notificar_pago();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta notificacion"];
                }

            case 'marcar_como_activo':
                $validaciones = $this->validarDatos('editar');
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->marcar_como_activo();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar marcar esta notificacion"];
                }
            case 'marcar_todas_leidas':
                $respuesta = $this->marcar_todas_como_leidas();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus" => true, "mensaje" => "OK"];
                } 
                else {
                    return ["estatus" => false, "mensaje" => "Error al marcar las notificaciones"];
                }

            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    private function consultar()
    {        
        $sql = "SELECT nombre, titulo, descripcion, fecha,
            activo FROM notificaciones INNER JOIN usuarios ON 
            usuarios.id_usuario = notificaciones.usuario_id";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_notificaciones_usuario()
    {        
        $sql = "SELECT id_notificacion, titulo, descripcion FROM notificaciones WHERE notificaciones.usuario_id = :usuario_id and notificaciones.activo = 0";

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

        return $result;
    }

    private function marcar_todas_como_leidas()
    {
        // Actualiza todas las notificaciones pendientes (activo=0) del usuario a leídas (activo=1).
        $sql = "UPDATE notificaciones SET activo = 1 WHERE usuario_id = :usuario_id AND activo = 0";

        $conexion = $this->get_conex();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $this->usuario_id);

        return $stmt->execute();
    }

    private function validarDatos($consulta = "registrar")
    {   
        if ($consulta == "editar") {
            if (empty($this->id_notificacion)){
                return ["estatus"=>false,"mensaje"=>"El id de la notificacion se envio vacío"];
            }
            if (!($this->validarClaveForanea("notificaciones","id_notificacion",$this->id_notificacion))) {
                return ["estatus"=>false,"mensaje"=>"El id de la notificacion seleccionada no existe"];
            }
            return ["estatus"=>true,"mensaje"=>"OK"];
        }

        if (!(isset($this->titulo) && isset($this->descripcion) && isset($this->fecha))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        if (empty($this->titulo) || empty($this->descripcion) || empty($this->fecha)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}
        
        if(!(is_string($this->titulo)) || !(preg_match("/^[A-Za-z áéíóúÑñ \b]*$/",$this->titulo))){
            return ["estatus"=>false,"mensaje"=>"El campo 'titulo' no posee un valor valido"];
        }

        if(!(is_string($this->descripcion)) || !(preg_match("/^[A-Za-z áéíóúÑñ \b]*$/",$this->descripcion))){
            return ["estatus"=>false,"mensaje"=>"El campo 'descripcion' no posee un valor valido"];
        }

        if(!(is_string($this->fecha)) || !($this->validarFecha($this->fecha))){
            return ["estatus"=>false,"mensaje"=>"El campo 'fecha' no posee un valor valido"];
        }

        if($consulta == 'agregar_notificacion'){
            if (!($this->validarClaveForanea("usuarios","id_usuario",$this->usuario_id))) {
                return ["estatus"=>false,"mensaje"=>"El id del usuario seleccionado no existe"];
            }
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarClaveForanea($tabla,$nombreClave,$valor)
    {

        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        return ($result)?true:false;
    }

    private function validarFecha($fecha){
        $valores = explode('-', $fecha);
        if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
            return true;
        }
        return false;
    }
}
