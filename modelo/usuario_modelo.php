<?php

require_once "modelo/conexion.php";

class Usuario extends Conexion
{
    private $id_usuario;
    private $apellido;
    private $nombre;
    private $correo;
    private $contra;
    private $rol_id;
    private $token;
    private $duracion_token;
    private $token_recuerdame;
    private $duracion_token_recuerdame;

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

    public function set_token($token)
    {
        $this->token = $token;
    }

    public function get_token()
    {
        return $this->token;
    }

    public function set_duracion_token($duracion_token)
    {
        $this->duracion_token = $duracion_token;
    }

    public function get_duracion_token()
    {
        return $this->duracion_token;
    }

    public function set_token_recuerdame($token_recuerdame)
    {
        $this->token_recuerdame = $token_recuerdame;
    }

    public function get_token_recuerdame()
    {
        return $this->token_recuerdame;
    }

    public function set_duracion_token_recuerdame($duracion_token_recuerdame)
    {
        $this->duracion_token_recuerdame = $duracion_token_recuerdame;
    }

    public function get_duracion_token_recuerdame()
    {
        return $this->duracion_token_recuerdame;
    }
    
    public function realizar_consulta($accion){
        $this->cambiar_db_seguridad();
        switch ($accion) {
            case 'validar_usuario':
                $respuesta = $this->validar_usuario();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    if($respuesta["total_resultados"] != 1){
                        return ["estatus"=>false,"mensaje"=>"Usuario no encontrado"];
                    }                        
                    
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'verificar_correo':
                $respuesta = $this->verificar_correo();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    if (isset($respuesta["datos"]["correo"])) {                        
                        return ["estatus"=>true,"busqueda"=>"correo"];
                    } else {                        
                        return ["estatus"=>false,"busqueda"=>"correo"];
                    }
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }            

            case 'validar_token':
                $respuesta = $this->validar_token();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {                    
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'validar_token_recuerdame':
                $respuesta = $this->validar_token_recuerdame();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {                    
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al tratar de validar el codigo para mantener sesion"];
                }

            case 'consultar':
                $respuesta = $this->consultar();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {                        
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_usuario':
                $respuesta = $this->consultar_usuario();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_perfil_usuario':
                $respuesta = $this->consultar_perfil_usuario();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'registrar':
                $validaciones = $this->validarDatos();
                if(!($validaciones["estatus"])){return $validaciones;}
        
                $respuesta = $this->registrar();                

                $this->cambiar_db_negocio();

                if ($respuesta) {                        
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Usuario"];
                }

            case 'registrar_token':
                $validaciones = $this->validarTokens();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->registrar_token();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar el codigo de recuperación de contraseña"];
                } 

            case 'registrar_token_recuerdame':
                $validaciones = $this->validarTokensRecuerdame();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->registrar_token_recuerdame();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar el codigo para mantener sesion"];
                } 

            case 'editar_usuario':
                $validaciones = $this->validarDatos("editar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar_usuario();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este Usuario"];
                }

            case 'editar_perfil':
                $validaciones = $this->validarDatos("editar",true);
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar_perfil();

                $this->cambiar_db_negocio();

                if ($respuesta) {

                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar sus datos de Usuario"];
                }

            case 'cambiar_contrasenia':
                $validaciones = $this->validarNuevaContrasenia();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->cambiar_contrasenia();

                $this->cambiar_db_negocio();

                if ($respuesta) {

                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar la contraseña"];
                }

            case 'eliminar_usuario':
                $validaciones = $this->validarDatos("eliminar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->eliminar_usuario();

                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Usuario"];
                }

            case 'eliminar_token':
                $validaciones = $this->validarTokens('Eliminar');
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->eliminar_token();
                
                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Token de autenticacion de contraseña"];
                }

            case 'eliminar_token_recuerdame':
                $validaciones = $this->validarTokensRecuerdame('Eliminar');
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->eliminar_token_recuerdame();
                
                $this->cambiar_db_negocio();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este codigo para mantener sesion"];
                }

            case 'lastId':
                $respuesta = $this->lastId();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    private function validar_usuario()
    {
        $sql = "SELECT usuarios.id_usuario, usuarios.correo as correo, usuarios.nombre as nombre_usuario, roles.id_rol, roles.nombre as nombre_rol, usuarios.contrasenia FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol WHERE correo = :correo";
        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":correo", $this->correo);

        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        $total_resultados = $conexion->rowCount();

        return ["resultado"=>$result,"datos"=>$datos,"total_resultados"=>$total_resultados];
    }
    
    private function verificar_correo()
    {
        $sql = "SELECT * FROM usuarios WHERE correo = :correo";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":correo", $this->correo);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function validar_token()
    {
        $sql = "SELECT * FROM usuarios WHERE DATE_ADD(usuarios.duracion_token, INTERVAL 10 MINUTE) > :duracion_token && usuarios.token = :token";
        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":duracion_token", $this->duracion_token);
        $conexion->bindParam(":token", $this->token);

        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);        

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function validar_token_recuerdame()
    {
        $sql = "SELECT usuarios.id_usuario, usuarios.correo as correo, usuarios.nombre as nombre_usuario, roles.id_rol, roles.nombre as nombre_rol, usuarios.contrasenia, token_recuerdame, duracion_token_recuerdame FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol WHERE usuarios.correo = :correo";
        $conexion = $this->get_conex()->prepare($sql);
        
        $conexion->bindParam(":correo", $this->correo);

        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);        

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar()
    {
        $sql = "SELECT id_usuario, apellido, usuarios.nombre as nombre_usuario, correo, rol_id, roles.nombre as nombre_rol FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol ORDER BY id_usuario";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_usuario()
    {
        $sql = "SELECT id_usuario, apellido, usuarios.nombre as nombre_usuario, correo, roles.nombre as nombre_rol, contrasenia, rol_id FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol WHERE id_usuario = :usuario";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":usuario", $this->id_usuario);

        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_perfil_usuario()
    {
        $sql = "SELECT 
                    usuarios.nombre as nombre_usuario, 
                    apellido, 
                    correo, 
                    roles.nombre as nombre_rol,
                    COALESCE(
                        (SELECT bitacora.fecha_hora 
                         FROM bitacora 
                         WHERE bitacora.usuario_id = usuarios.id_usuario 
                         AND bitacora.accion = 'iniciar sesion' 
                         ORDER BY bitacora.fecha_hora DESC 
                         LIMIT 1 OFFSET 1),
                        (SELECT bitacora.fecha_hora 
                         FROM bitacora 
                         WHERE bitacora.usuario_id = usuarios.id_usuario 
                         AND bitacora.accion = 'iniciar sesion' 
                         ORDER BY bitacora.fecha_hora DESC 
                         LIMIT 1)
                    ) as ultima_vez
                FROM usuarios 
                INNER JOIN roles ON usuarios.rol_id = roles.id_rol 
                WHERE usuarios.id_usuario = :usuario";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":usuario", $this->id_usuario);

        $result = $conexion->execute();        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function registrar()
    {
        $sql = "INSERT INTO usuarios(apellido,nombre,correo,contrasenia,rol_id) VALUES (:apellido,:nombre,:correo,:contrasenia,:rol)";

        $contra_hash = password_hash($this->contra, PASSWORD_DEFAULT);        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":apellido", $this->apellido);
        $conexion->bindParam(":nombre", $this->nombre);
        $conexion->bindParam(":correo", $this->correo);
        $conexion->bindParam(":contrasenia", $contra_hash);
        $conexion->bindParam(":rol", $this->rol_id);
        $result = $conexion->execute();

        return $result;
    }

    private function registrar_token()
    {
        $sql = "UPDATE usuarios SET token=:token, duracion_token=:duracion_token WHERE correo = :correo";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":token", $this->token);
        $conexion->bindParam(":duracion_token", $this->duracion_token);
        $conexion->bindParam(":correo", $this->correo);        
        $result = $conexion->execute();

        return $result;
    }

    private function registrar_token_recuerdame()
    {
        $hashToken = password_hash($this->token_recuerdame, PASSWORD_DEFAULT);
        $fechaExpiracion = date('Y-m-d H:i:s', $this->duracion_token_recuerdame);

        $sql = "UPDATE usuarios SET token_recuerdame=:token, duracion_token_recuerdame=:duracion_token WHERE correo = :correo";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":token", $hashToken);
        $conexion->bindParam(":duracion_token", $fechaExpiracion);
        $conexion->bindParam(":correo", $this->correo);        
        $result = $conexion->execute();

        return $result;
    }

    private function editar_usuario()
    {
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
        $filas_afectadas = $conexion->rowCount();
        
        return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
    }

    private function editar_perfil()
    {
        $sql = "UPDATE usuarios SET apellido=:apellido,nombre=:nombre,correo=:correo WHERE id_usuario=:id_usuario";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_usuario", $this->id_usuario);    
        $conexion->bindParam(":apellido", $this->apellido);
        $conexion->bindParam(":nombre", $this->nombre);
        $conexion->bindParam(":correo", $this->correo);

        $result = $conexion->execute();
 
        return $result;
    }

    private function cambiar_contrasenia()
    {
        $sql = "UPDATE usuarios SET contrasenia=:contrasenia WHERE correo=:correo";

        $contra_hash = password_hash($this->contra, PASSWORD_DEFAULT);

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":correo", $this->correo);
        $conexion->bindParam(":contrasenia", $contra_hash);

        $result = $conexion->execute();

        return $result;
    }

    private function eliminar_usuario()
    {
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id_usuario";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_usuario", $this->id_usuario);

        $result = $conexion->execute();
        return $result;
    }

    private function eliminar_token()
    {
        $sql = "UPDATE usuarios SET token=null, duracion_token=null WHERE token=:token";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":token", $this->token);

        $result = $conexion->execute();
        return $result;
    }

    private function eliminar_token_recuerdame()
    {
        $sql = "UPDATE usuarios SET token_recuerdame=null, duracion_token_recuerdame=null WHERE correo=:correo";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":correo", $this->correo);

        $result = $conexion->execute();
        return $result;
    }
    
    private function lastId()
    {
        $sql = "SELECT MAX(id_usuario) as last_id FROM usuarios";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    public function verificarRecaptcha($respuestaRecaptcha) {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        
        $datos = [
            'secret' => CLAVE_SECRETA_RECAPTCHA,
            'response' => $respuestaRecaptcha,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $opciones = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($datos)
            ]
        ];

        $contexto = stream_context_create($opciones);
        $resultado = file_get_contents($url, false, $contexto);
        return json_decode($resultado, true);
    }

    private function validarDatos($consulta = "registrar",$perfil = false)
    {   

        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_usuario))) {return ["estatus"=>false,"mensaje"=>"El id del Usuario requerido no se recibio correctamente"];}

            if (empty($this->id_usuario)) {return ["estatus"=>false,"mensaje"=>"El id del Usuario requerido esta vacio"];}

            if(is_numeric($this->id_usuario)){
                if (!($this->validarClaveForanea("usuarios","id_usuario",$this->id_usuario))) {
                    return ["estatus"=>false,"mensaje"=>"El usuario seleccionado no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Usuario debe ser un valor numerico entero"];}
        }

        if (!(isset($this->apellido) && isset($this->nombre) && isset($this->correo))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        if (!$perfil) {
            if (!(isset($this->rol_id))) {
                return["estatus"=>false,"mensaje"=>"No se envio el rol solicitado"];
            }
            if (!(isset($this->contra))) {
                return["estatus"=>false,"mensaje"=>"No se envio la contraseña solicitada"];
            }
        }

        if (empty($this->apellido) || empty($this->nombre) ||empty($this->correo)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        if (!$perfil) {
            if (empty($this->rol_id)) {
                return["estatus"=>false,"mensaje"=>"El campo del rol se envio vacío"];
            }
            if (empty($this->contra)) {
                return["estatus"=>false,"mensaje"=>"El campo de la contraseña se envio vacío"];
            }
        }
        
        if(!(is_string($this->apellido)) || !(preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/",$this->apellido))){
            return ["estatus"=>false,"mensaje"=>"El campo 'apellido' no posee un valor valido"];
        }
        if(!(is_string($this->nombre)) || !(preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,20}$/",$this->nombre))){
            return ["estatus"=>false,"mensaje"=>"El campo 'nombre' no posee un valor valido"];
        }
        if(!(is_string($this->correo)) || !(preg_match("/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/",$this->correo))){
            return ["estatus"=>false,"mensaje"=>"El campo 'correo' no posee un valor valido"];
        }
        if (!$perfil) {
            if(!(is_string($this->contra)) || !(preg_match("/^[A-Za-z0-9_.+*$#%&@ñÑ]{5,50}$/",$this->contra))){
                return ["estatus"=>false,"mensaje"=>"El campo 'contraseña' no posee un valor valido"];
            }
        }
        
        if (!$perfil) {
            if(is_numeric($this->rol_id)){
                if (!($this->validarClaveForanea("roles","id_rol",$this->rol_id))) {
                    return ["estatus"=>false,"mensaje"=>"El Rol seleccionado no existe"];
                }            
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El campo 'Rol' no posee un valor valido"];
            }
        }        
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarTokens($consulta = "Registrar")
    {   
        if ($consulta == "Eliminar") {
            if (!(isset($this->token))){return ["estatus"=>false,"mensaje"=>"El token requerido no se recibio correctamente"];}

            if (empty($this->token)) {return ["estatus"=>false,"mensaje"=>"El tiempo del token requerido esta vacio"];}

            return ["estatus"=>true,"mensaje"=>"OK"];
        }
        if (!(isset($this->token) && isset($this->duracion_token) && isset($this->correo))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        if (empty($this->token) || empty($this->duracion_token) ||empty($this->correo)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        if(!(is_string($this->correo)) || !(preg_match("/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/",$this->correo))){
            return ["estatus"=>false,"mensaje"=>"El campo 'correo' no posee un valor valido"];
        }

        if (!($this->validarClaveForanea("usuarios","correo",$this->correo))) {
            return ["estatus"=>false,"mensaje"=>"El correo seleccionado no existe"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarTokensRecuerdame($consulta = "Registrar")
    {   
        if ($consulta == "Eliminar") {
            if (!(isset($this->correo))){return ["estatus"=>false,"mensaje"=>"El correo para eliminar el token no se recibio correctamente"];}

            if (empty($this->correo)) {return ["estatus"=>false,"mensaje"=>"El correo para eliminar el token requerido esta vacío"];}

            if(!(is_string($this->correo)) || !(preg_match("/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/",$this->correo))){
                return ["estatus"=>false,"mensaje"=>"El campo 'correo' no posee un valor valido"];
            }

            return ["estatus"=>true,"mensaje"=>"OK"];
        }
        if (!(isset($this->token_recuerdame) && isset($this->duracion_token_recuerdame) && isset($this->correo))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        if (empty($this->token_recuerdame) || empty($this->duracion_token_recuerdame) ||empty($this->correo)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        if(!(is_string($this->correo)) || !(preg_match("/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/",$this->correo))){
            return ["estatus"=>false,"mensaje"=>"El campo 'correo' no posee un valor valido"];
        }
        
        if (!($this->validarClaveForanea("usuarios","correo",$this->correo))) {
            return ["estatus"=>false,"mensaje"=>"El correo seleccionado no existe"];
        }  
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarNuevaContrasenia()
    {   
        if (!(isset($this->correo) && isset($this->contra))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        if (empty($this->correo) || empty($this->contra)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        if(!(is_string($this->contra)) || !(preg_match("/^[A-Za-z0-9_.+*$#%&@]{5,50}$/",$this->contra))){
            return ["estatus"=>false,"mensaje"=>"El campo 'contraseña' no posee un valor valido"];
        }

        if(!(is_string($this->correo)) || !(preg_match("/^[a-zA-Z0-9._+-]{3,35}@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/",$this->correo))){
            return ["estatus"=>false,"mensaje"=>"El campo 'correo' no posee un valor valido"];
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
}
?>