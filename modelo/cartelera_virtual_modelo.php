<?php 
require_once "modelo/conexion.php";

class Cartelera_virtual extends Conexion
{
    private $id_cartelera;
    private $titulo;
    private $descripcion;
    private $fecha;
    private $tipo;
    private $imagen;
    private $prioridad;
    private $usuario_id;

    public function __construct()
    {
        parent::__construct();
    }
    public function set_id_cartelera($id_cartelera)
    {
        $this->id_cartelera = $id_cartelera;
    }
    public function get_id_cartelera()
    {
        return $this->id_cartelera;
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
    public function set_tipo($tipo)
    {
        $this->tipo = $tipo;
    }
    public function get_tipo()
    {
        return $this->tipo;
    }
    public function set_imagen($imagen)
    {
        $this->imagen = $imagen;
    }
    public function get_imagen()
    {
        return $this->imagen;
    }
    public function set_prioridad($prioridad)
    {
        $this->prioridad = $prioridad;
    }
    public function get_prioridad()
    {
        return $this->prioridad;
    }
    public function set_usuario_id($usuario_id)
    {
        $this->usuario_id = $usuario_id;
    }
    public function get_usuario_id()
    {
        return $this->usuario_id;
    }

   public function consultar(){
    $this->cambiar_db_seguridad();
    $sql = "SELECT id_cartelera, titulo, prioridad, fecha, usuarios.nombre as nombre_usuario 
            FROM cartelera_virtual
            INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
            ORDER BY fecha ASC";
    $conexion = $this->get_conex()->prepare($sql);
    $result = $conexion->execute();

    $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

    $this->cambiar_db_negocio();
    if ($result) {
        return $datos;
    } else {
        return ["estatus" => false, "mensaje" => "Error al consultar los datos"];
    }
}

    public function consultar_cartelera_id()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT * FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_cartelera", $this->id_cartelera);
        $result = $conexion->execute();

        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        $this->cambiar_db_negocio();
        if ($result) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Error al consultar los datos"];
        }
    }

    public function registrar(){
        $this->cambiar_db_seguridad();
        $sql = "INSERT INTO cartelera_virtual (titulo, descripcion, fecha, imagen, prioridad, usuario_id) VALUES (:titulo, :descripcion, :fecha, :imagen, :prioridad, :usuario_id)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":titulo", $this->titulo);
        $conexion->bindParam(":descripcion", $this->descripcion);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":imagen", $this->imagen);
        $conexion->bindParam(":prioridad", $this->prioridad);
        $conexion->bindParam(":usuario_id", $this->usuario_id);
        $result = $conexion->execute();
        $this->cambiar_db_negocio();
        // $this->registrar_bitacora(REGISTRAR, GESTIONAR_CARTELERA_VIRTUAL, $this->titulo);//registra cuando se registra un nuevo cartelera

        if ($result) {
            $id_ultimo = $this->lastId();
            $this->set_id_cartelera($id_ultimo['mensaje']);
            $cartelera_alterada = $this->consultar_cartelera_id();

            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al registrar los datos"];
        }
    }

    public function editar_publicacion(){
        $this->cambiar_db_seguridad();
        $sql = "UPDATE cartelera_virtual SET titulo = :titulo, descripcion = :descripcion, fecha = :fecha, imagen = :imagen, prioridad = :prioridad, usuario_id = :usuario_id WHERE id_cartelera = :id_cartelera";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_cartelera", $this->id_cartelera);
        $conexion->bindParam(":titulo", $this->titulo);
        $conexion->bindParam(":descripcion", $this->descripcion);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":imagen", $this->imagen);
        $conexion->bindParam(":prioridad", $this->prioridad);
        $conexion->bindParam(":usuario_id", $this->usuario_id);
        $result = $conexion->execute();
        $this->cambiar_db_negocio();
        // $this->registrar_bitacora(MODIFICAR, GESTIONAR_CARTELERA_VIRTUAL, "ID: ".$this->id_cartelera);//registra cuando se edita un cartelera

        if ($result) {
            return ["estatus" => true, "mensaje" => "Edición exitosa"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al editar los datos"];
        }
    }

    public function eliminar_publicacion(){
        $this->cambiar_db_seguridad();
        $sql = "DELETE FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_cartelera", $this->id_cartelera);
        $result = $conexion->execute();
        $this->cambiar_db_negocio();
        // $this->registrar_bitacora(ELIMINAR, GESTIONAR_CARTELERA_VIRTUAL, "ID: ".$this->id_cartelera);//registra cuando se elimina un cartelera

        if ($result) {
            return ["estatus" => true, "mensaje" => "Eliminación exitosa"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al eliminar los datos"];
        }
    }

    public function lastId()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_cartelera) as last_id FROM cartelera_virtual";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        $this->cambiar_db_negocio();
        if ($result) {
            return ["estatus" =>true, "mensaje"=>$datos[0]['last_id']];
        } else {
            return ["estatus" => false, "mensaje" => "Error al consultar los datos"];
        }
    }

    private function validarClaveForanea($tabla,$nombreClave,$valor,$seguridad = false)
    {
        if ($seguridad) {
            $this->cambiar_db_seguridad();
        }
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($seguridad) {
            $this->cambiar_db_negocio();
        }
        return ($result)?true:false;
    }

    public function obtener_imagen_actual() {
    $this->cambiar_db_seguridad(); // Si usas múltiples bases
    $sql = "SELECT imagen FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":id_cartelera", $this->id_cartelera);
    $conexion->execute();
    $this->cambiar_db_negocio();

    $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
    return $resultado ? $resultado["imagen"] : null;
}

}
?>