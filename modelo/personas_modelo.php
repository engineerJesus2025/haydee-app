<?php
    require_once("modelo/conexion.php");

    class Personas extends Conexion{
        private $id_persona;
        private $nombre;
        private $apellido;
        private $cedula;
        private $telefono;
        private $correo;
        private $fecha_nacimiento;
        private $sexo;

        public function __construct(){
            parent::__construct();
        }

        // Metodos Setter y Getter
        public function set_id_persona($id_persona){
            $this->id_persona = $id_persona;
        }

        public function get_id_persona(){
            return $this->id_persona;
        }

        public function set_nombre($nombre){
            $this->nombre = $nombre;
        }

        public function get_nombre(){
            return $this->nombre;
        }

        public function set_apellido($apellido){
            $this->apellido = $apellido;
        }

        public function get_apellido(){
            return $this->apellido;
        }

        public function set_cedula($cedula){
            $this->cedula = $cedula;
        }

        public function get_cedula(){
            return $this->cedula;
        }

        public function set_telefono($telefono){
            $this->telefono = $telefono;
        }

        public function get_telefono(){
            return $this->telefono;
        }

        public function set_correo($correo){
            $this->correo = $correo;
        }

        public function get_correo(){
            return $this->correo;
        }

        public function set_fecha_nacimiento($fecha_nacimiento){
            $this->fecha_nacimiento = $fecha_nacimiento;
        }

        public function get_fecha_nacimiento(){
            return $this->fecha_nacimiento;
        }

        public function set_sexo($sexo){
            $this->sexo = $sexo;
        }

        public function get_sexo(){
            return $this->sexo;
        }

        // Metodos CRUD
        public function verificar_persona(){  

            //$this->cambiar_db_seguridad();

            $sql = "SELECT * FROM personas WHERE cedula = :cedula"; 

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":cedula", $this->cedula);
            $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();
        
            if (isset($datos["cedula"])) {
                $r["estatus"] = true; 
                $r["busqueda"] = "cedula";
                return $r;
            } else {
                $r["estatus"] = false;
                $r["busqueda"] = "cedula";
                return $r;
            }
        }

        public function consultar(){

            //$this->cambiar_db_seguridad();

            $sql = "SELECT
                p.id_persona,
                p.nombre,
                p.apellido,
                p.cedula,
                p.telefono,
                p.correo,
                p.fecha_nacimiento,
                p.sexo,
                a.nro_apartamento,
                pa.tipo_vinculo
            FROM personas p
            INNER JOIN personas_apartamentos pa ON p.id_persona = pa.persona_id
            INNER JOIN apartamentos a ON pa.apartamento_id = a.id_apartamento
            ORDER BY p.id_persona";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            $this->cambiar_db_negocio();

            if ($result == true) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_PERSONAS, "TODAS LAS PERSONAS");//registra cuando se entra al modulo de bancos

                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function consultar_persona(){
            //var_dump("ID recibido:", $this->id_persona);

            //$this->cambiar_db_seguridad();
 
            $sql = "SELECT 
                p.id_persona,
                p.nombre,
                p.apellido,
                p.cedula,
                p.telefono,
                p.correo,
                p.fecha_nacimiento,
                p.sexo,
                a.nro_apartamento AS apartamento,
                pa.tipo_vinculo,
                pa.apartamento_id
            FROM personas p
            LEFT JOIN personas_apartamentos pa ON p.id_persona = pa.persona_id
            LEFT JOIN apartamentos a ON pa.apartamento_id = a.id_apartamento
            WHERE p.id_persona = :id_persona";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_persona", $this->id_persona);
            $result = $conexion->execute();        
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();
            //var_dump("Resultado SQL:", $datos);

            if ($result && $datos) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function registrar_persona(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO personas(nombre,apellido,cedula,telefono,correo,fecha_nacimiento,sexo) VALUES (:nombre,:apellido,:cedula,:telefono,:correo,:fecha_nacimiento,:sexo)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nombre", $this->nombre);
            $conexion->bindParam(":apellido", $this->apellido);
            $conexion->bindParam(":cedula", $this->cedula);
            $conexion->bindParam(":telefono", $this->telefono);
            $conexion->bindParam(":correo", $this->correo);
            $conexion->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
            $conexion->bindParam(":sexo", $this->sexo);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();//obtenemos el ultimo id
                $this->set_id_persona($id_ultimo["mensaje"]);
                $persona_alterada = $this->consultar_persona();//lo consultamos

                $this->registrar_bitacora(REGISTRAR, GESTIONAR_PERSONAS, $persona_alterada["cedula"] . " (" . $persona_alterada["nombre"] . ")");//registramos en la bitacora

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar a esta persona"];
            }
        }

        public function editar_persona(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE personas SET nombre=:nombre,apellido=:apellido,cedula=:cedula,telefono=:telefono,correo=:correo,fecha_nacimiento=:fecha_nacimiento,sexo=:sexo WHERE id_persona=:id_persona";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_persona", $this->id_persona);
            $conexion->bindParam(":nombre", $this->nombre);
            $conexion->bindParam(":apellido", $this->apellido);
            $conexion->bindParam(":cedula", $this->cedula);
            $conexion->bindParam(":telefono", $this->telefono);
            $conexion->bindParam(":correo", $this->correo);
            $conexion->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
            $conexion->bindParam(":sexo", $this->sexo);

            $result = $conexion->execute();

            $this->cambiar_db_negocio();        
            
            if ($result) {
                $persona_alterada = $this->consultar_persona();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_PERSONAS, $persona_alterada["cedula"] . " (" . $persona_alterada["nombre"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar a esta persona"];
            }
        }

        public function eliminar_persona(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            $persona_alterada = $this->consultar_persona();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM personas WHERE id_persona = :id_persona";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_persona", $this->id_persona);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_PERSONAS, $persona_alterada["cedula"] . " (" . $persona_alterada["nombre"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar a esta persona"];
            }
        }

        public function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_persona) as last_id FROM personas";
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

        private function validarDatos($consulta = "registrar"){   
            // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
            if ($consulta == "editar" || $consulta == "eliminar") {
                if (!(isset($this->id_habitante))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

                if (empty($this->id_habitante)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            } 
            // Validamos que los campos enviados si existan

            if (!(isset($this->cedula) && isset($this->nombre_habitante) && isset($this->apellido) && isset($this->fecha_nacimiento) && isset($this->sexo) && isset($this->telefono) && isset($this->apartamento_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            // Validamos que los campos enviados no esten vacios

            if (empty($this->cedula) || empty($this->nombre_habitante) ||empty($this->apellido) || empty($this->fecha_nacimiento) || empty($this->sexo) || empty($this->telefono) || empty($this->apartamento_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            // Verificamos si los valores tienen los datos que deberian
            
            if(!(is_string($this->cedula)) || !(preg_match("/^[0-9\b]{7,8}$/",$this->cedula))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Cedula' no posee un valor valido"];
            }
            if(!(is_string($this->nombre_habitante)) || !(preg_match("/^[A-Za-z \b]{3,30}$/",$this->nombre_habitante))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Nombre' no posee un valor valido"];
            }
            if(!(is_string($this->apellido)) || !(preg_match("/^[A-Za-z \b]{3,30}$/",$this->apellido))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Apellido' no posee un valor valido"];
            }
            if(!(is_string($this->sexo))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Sexo' no posee un valor valido"];
            }
            if(!(is_numeric($this->telefono)) || !(preg_match("/^[0-9\b]{11}$/",$this->telefono))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Telefono' no posee un valor valido"];
            }
            if(is_numeric($this->apartamento_id)){
                if (!($this->validarClaveForanea("apartamentos","id_apartamento",$this->apartamento_id,true))) {
                    return ["estatus"=>false,"mensaje"=>"El campo 'Apartamento' no posee un valor valido"];
                }            
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El campo 'Apartamento' no posee un valor valido"];
            }

            return ["estatus"=>true,"mensaje"=>"OK"];
        }

        private function validarClaveForanea($tabla,$nombreClave,$valor,$seguridad = false){
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
?>