<?php

require_once "modelo/conexion.php";

class Usuario extends Conexion
{
    private $id_usuario;
    private $apellido;
    private $nombre;
    private $direccion;
    private $correo;
    private $contra;
    private $rol_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_usuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    public function get_id_usuario()
    {
        return $this->id_usuario;
    }

    public function set_apellido($apellido)
    {
        $this->apellido = $apellido;
    }

    public function get_apellido()
    {
        return $this->apellido;
    }

    public function set_nombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function get_nombre()
    {
        return $this->nombre;
    }

    public function set_direccion($direccion)
    {
        $this->direccion = $direccion;
    }

    public function get_direccion()
    {
        return $this->direccion;
    }

    public function set_correo($correo)
    {
        $this->correo = $correo;
    }

    public function get_correo()
    {
        return $this->correo;
    }

    public function set_contra($contra)
    {
        $this->contra = $contra;
    }

    public function get_contra()
    {
        return $this->contra;
    }

    public function set_rol_id($rol_id)
    {
        $this->rol_id = $rol_id;
    }

    public function get_rol_id()
    {
        return $this->rol_id;
    }

    public function verificar_correo()
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT * FROM usuarios WHERE correo = :correo";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":correo", $this->correo);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if (isset($datos["correo"])) {
            $r["estatus"] = true;
            $r["busqueda"] = "correo";
            return $r;
        } else {
            $r["estatus"] = false;
            $r["busqueda"] = "correo";
            return $r;
        }
    }

    public function consultar()
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT `id_usuario`, `apellido`, usuarios.nombre as nombre_usuario, `correo`, `contrasenia`, `rol_id`, roles.nombre as nombre_rol FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol ORDER BY id_usuario";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
       
        //$this->registrar_bitacora(CONSULTAR, GESTIONAR_USUARIOS);
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un erro con la consulta"];
        }
    }

    public function consultar_usuario($prueba = false)
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT `id_usuario`, `apellido`, usuarios.nombre as nombre_usuario, `correo`, `contrasenia`, `rol_id`, roles.nombre as nombre_rol FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol WHERE id_usuario = :usuario";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":usuario", $this->id_usuario);

        $result = $conexion->execute();
        if ($prueba) {
            $this->registrar_bitacora(CONSULTAR, GESTIONAR_USUARIOS);
        }
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_roles()
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT * FROM roles";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["resultado"=>false];
        }
    }
    public function registrar($prueba = false)
    {
        $this->cambiar_db_seguridad();

        //Validamos los datos obtenidos del controlador
        //$validaciones = $this->validarDatos();
        //if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "INSERT INTO usuarios(apellido,nombre,correo,contrasenia,rol_id) VALUES (:apellido,:nombre,:correo,:contrasenia,:rol)";

        //$contra_hash = password_hash($this->contra, PASSWORD_DEFAULT);
        $contra_hash = $this->contra;
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":apellido", $this->apellido);
        $conexion->bindParam(":nombre", $this->nombre);
        $conexion->bindParam(":correo", $this->correo);
        $conexion->bindParam(":contrasenia", $contra_hash);
        $conexion->bindParam(":rol", $this->rol_id);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if (!$prueba) {
        $this->registrar_bitacora(REGISTRAR, GESTIONAR_USUARIOS);
        }
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    public function editar_usuario($prueba = false)
    {
        $this->cambiar_db_seguridad();

        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("editar");
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "UPDATE usuarios SET apellido=:apellido,nombre=:nombre,correo=:correo,contrasenia=:contrasenia,rol_id=:rol WHERE id_usuario=:id_usuario";

        $contra_hash = password_hash($this->contra, PASSWORD_DEFAULT);


        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_usuario", $this->id_usuario);    
        $conexion->bindParam(":apellido", $this->apellido);
        $conexion->bindParam(":nombre", $this->nombre);
        $conexion->bindParam(":correo", $this->correo);
        $conexion->bindParam(":contrasenia", $contra_hash);
        $conexion->bindParam(":rol", $this->rol_id);

        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if (!$prueba) {
            $this->registrar_bitacora(MODIFICAR, GESTIONAR_USUARIOS);
        }
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    public function eliminar_usuario($prueba = false)
    {
        $this->cambiar_db_seguridad();

        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("eliminar");
        if(!($validaciones["estatus"])){return $validaciones;}

        $sql = "DELETE FROM usuarios WHERE id_usuario = :id_usuario";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_usuario", $this->id_usuario);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if (!$prueba) {
            $this->registrar_bitacora(ELIMINAR, GESTIONAR_USUARIOS);
        }
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    public function validar_usuario($prueba = false)
    {
        $this->cambiar_db_seguridad();


        $sql = "SELECT usuarios.id_usuario,usuarios.correo as correo, usuarios.nombre as nombre_usuario, roles.id_rol, roles.nombre as nombre_rol, usuarios.contrasenia FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol WHERE correo = :correo";
        $conexion = $this->get_conex()->prepare($sql);



        $conexion->bindParam(":correo", $this->correo);
        // $conexion->bindParam(":contra", $this->contra);

        $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        $result = $conexion->rowCount();

        $this->cambiar_db_negocio();

        if ($result == 1) {
            $_SESSION["id_usuario"] = $datos["id_usuario"];
            if ($prueba) {
                $this->registrar_bitacora(INICIAR_SESION, GESTIONAR_USUARIOS);
            }
            return $datos;
        } else {
            return false;
        }
    }

    public function consultar_permisos_por_usuario($rol_permiso)
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT modulos.id_modulo, permisos_usuarios.nombre_accion AS nombre_permiso
        FROM roles_permisos INNER JOIN permisos_usuarios ON permisos_usuarios.id_permiso_usuario = 
        roles_permisos.permiso_usuario_id INNER JOIN  modulos ON modulos.id_modulo = permisos_usuarios.modulo_id
        WHERE roles_permisos.rol_id = :rol_permiso";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":rol_permiso", $rol_permiso);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return "";
        }
    }

    public function lastId()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_usuario) as last_id FROM usuarios";
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
        // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_usuario))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            if (empty($this->id_usuario)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            if(is_numeric($this->id_usuario)){
                if (!($this->validarClaveForanea("usuarios","id_usuario",$this->id_usuario))) {
                    return ["estatus"=>false,"mensaje"=>"El usuario seleccionado no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Usuario tiene debe ser un valor numerico entero"];}
        }
        // Validamos que los campos enviados si existan

        if (!(isset($this->apellido) && isset($this->nombre) && isset($this->correo) && isset($this->contra) && isset($this->rol_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        // Validamos que los campos enviados no esten vacios

        if (empty($this->apellido) || empty($this->nombre) ||empty($this->correo) || empty($this->contra) || empty($this->rol_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(!(is_string($this->apellido)) || !(preg_match("/^[A-Za-z \b]*$/",$this->apellido))){
            return ["estatus"=>false,"mensaje"=>"El campo 'apellido' no posee un valor valido"];
        }
        if(!(is_string($this->nombre)) || !(preg_match("/^[A-Za-z \b]*$/",$this->nombre))){
            return ["estatus"=>false,"mensaje"=>"El campo 'nombre' no posee un valor valido"];
        }
        if(!(is_string($this->correo)) || !(preg_match("/^[-A-Za-z0-9_.]{3,20}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/",$this->correo))){
            return ["estatus"=>false,"mensaje"=>"El campo 'correo' no posee un valor valido"];
        }
        if(!(is_string($this->contra)) || !(preg_match("/^[A-Za-z0-9_.+*$#%&@]*$/",$this->contra))){
            return ["estatus"=>false,"mensaje"=>"El campo 'contraseña' no posee un valor valido"];
        }

        if(is_numeric($this->rol_id)){
            if (!($this->validarClaveForanea("roles","id_rol",$this->rol_id))) {
                return ["estatus"=>false,"mensaje"=>"El campo 'Rol' no posee un valor valido"];
            }            
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El campo 'Rol' no posee un valor valido"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    //esta funcion es para revisar si una clave foranea existe, porque sino dara error la consulta
    private function validarClaveForanea($tabla,$nombreClave,$valor)
    {
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        return ($result)?true:false;        
    }

}
