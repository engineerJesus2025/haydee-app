<?php
require_once "modelo/conexion.php";

class Rol extends Conexion
{

    private $id_rol;
    private $nombre;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_rol($id_rol)
    {
        $this->id_rol = $id_rol;
    }

    public function get_id_rol()
    {
        return $this->id_rol;
    }

    public function set_nombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function get_nombre()
    {
        return $this->nombre;
    }

    public function verificar_nombre()
    {
        $this->cambiar_db_seguridad();
        
        $sql = "SELECT * FROM roles WHERE nombre = :nombre";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre", $this->nombre);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if (isset($datos["nombre"])) {
            $r["estatus"] = true;
            $r["busqueda"] = "nombre";
            return $r;
        } else {
            $r["estatus"] = false;
            $r["busqueda"] = "nombre";
            return $r;
        }
    }

    public function consultar($consulta_externa = false)
    {
        $this->cambiar_db_seguridad();
        
        $sql = "SELECT * FROM roles";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
            
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            if (!$consulta_externa) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_ROLES, "TODOS LOS ROLES");
            }
            
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_rol()
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT id_rol, nombre FROM roles WHERE id_rol = :rol";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":rol", $this->id_rol);

        $result = $conexion->execute();        
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function registrar()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos();
        if(!($validaciones["estatus"])){return $validaciones;}

        $this->cambiar_db_seguridad();

        $sql = "INSERT INTO roles(nombre) VALUES (:nombre)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre", $this->nombre);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();
        
        if ($result) {
            $this->registrar_bitacora(REGISTRAR, GESTIONAR_ROLES, "Rol " . $this->get_nombre());
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Rol"];
        }
    }

    public function editar_rol()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("editar");
        if(!($validaciones["estatus"])){return $validaciones;}

        $this->cambiar_db_seguridad();

        $sql = "UPDATE roles SET nombre = :nombre WHERE id_rol = :id_rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_rol", $this->id_rol);
        $conexion->bindParam(":nombre", $this->nombre);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();        
        
        if ($result) {
            $this->registrar_bitacora(MODIFICAR, GESTIONAR_ROLES, "Rol " . $this->get_nombre());

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Rol"];
        }
    }
    public function eliminar_rol()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("eliminar");
        if(!($validaciones["estatus"])){return $validaciones;}

        $this->cambiar_db_seguridad();        

        $sql = "DELETE FROM roles WHERE id_rol = :id_rol";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_rol", $this->id_rol);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if ($result) {
            $this->registrar_bitacora(ELIMINAR, GESTIONAR_ROLES, "Rol " . $this->get_nombre());

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Rol"];
        }
    }

    public function lastId()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_rol) as last_id FROM roles";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        $this->cambiar_db_negocio();

        if ($result) {
            return ["estatus"=>true,"mensaje"=>$datos["last_id"]];
        } else {
            return ["estatus"=>false,"mensaje"=>"Error en la consulta"];
        } 
    }

    private function validarDatos($consulta = "registrar")
    {   
        // Validamos el id rol en caso de editar o eliminar porque en registrar no existe todavia
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_rol))) {return ["estatus"=>false,"mensaje"=>"El ID del rol no se recibio correctamente"];}

            if (empty($this->id_rol)) {return ["estatus"=>false,"mensaje"=>"El ID del Rol se envio vacio"];}

            if(is_numeric($this->id_rol)){
                if (!($this->validarClaveForanea("roles","id_rol",$this->id_rol,true))) {
                    return ["estatus"=>false,"mensaje"=>"El Rol seleccionado no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Rol tiene debe ser un valor numerico entero"];}
        }
        // Validamos que los campos enviados si existan

        if (!(isset($this->nombre))) {return ["estatus"=>false,"mensaje"=>"El campos 'nombre' no se recibio correctamente"];}

        // Validamos que los campos enviados no esten vacios        
        if (empty($this->nombre)) {return ["estatus"=>false,"mensaje"=>"El campos 'nombre' esta vacio"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(!(is_string($this->nombre)) || !(preg_match("/^[A-Za-z \b]*$/",$this->nombre))){
            return ["estatus"=>false,"mensaje"=>"El campo 'nombre' no posee un valor valido"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    //esta funcion es para revisar si una clave foranea existe, porque sino dara error la consulta
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
}
