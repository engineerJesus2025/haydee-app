<?php
    require_once("modelo/conexion.php");

    class Habitantes extends Conexion{
        private $id_habitante;
        private $cedula;
        private $nombre_habitante;
        private $apellido;
        private $fecha_nacimiento;
        private $sexo;
        private $telefono;
        private $apartamento_id;

        public function __construct(){
            parent::__construct();
        }

        // Metodos Setter y Getter
        public function set_id_habitante($id_habitante){
            $this->id_habitante = $id_habitante;
        }

        public function get_id_habitante(){
            return $this->id_habitante;
        }

        public function set_cedula($cedula){
            $this->cedula = $cedula;
        }

        public function get_cedula(){
            return $this->cedula;
        }

        public function set_nombre_habitante($nombre_habitante){
            $this->nombre_habitante = $nombre_habitante;
        }

        public function get_nombre_habitante(){
            return $this->nombre_habitante;
        }

        public function set_apellido($apellido){
            $this->apellido = $apellido;
        }

        public function get_apellido(){
            return $this->apellido;
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

        public function set_telefono($telefono){
            $this->telefono = $telefono;
        }

        public function get_telefono(){
            return $this->telefono;
        }

        public function set_apartamento_id($apartamento_id){
            $this->apartamento_id = $apartamento_id;
        }

        public function get_apartamento_id(){
            return $this->apartamento_id;
        }

        // Metodos CRUD
        public function verificar_habitante(){  

            //$this->cambiar_db_seguridad();

            $sql = "SELECT * FROM habitantes WHERE cedula = :cedula"; 

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

            $sql = "SELECT h.id_habitante, h.cedula, h.nombre_habitante, h.apellido, h.fecha_nacimiento, h.sexo, h.telefono, CONCAT('Nro: ', a.nro_apartamento) AS apartamento FROM habitantes h INNER JOIN apartamentos a ON h.apartamento_id = a.id_apartamento";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            $this->cambiar_db_negocio();

            if ($result == true) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_HABITANTES, "TODOS LOS HABITANTES");//registra cuando se entra al modulo de bancos

                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function consultar_habitante(){

            //$this->cambiar_db_seguridad();
 
            $sql = "SELECT * FROM habitantes WHERE id_habitante = :id_habitante";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_habitante", $this->id_habitante);
            $result = $conexion->execute();        
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function registrar_habitante(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO habitantes(cedula,nombre_habitante,apellido,fecha_nacimiento,sexo,telefono,apartamento_id) VALUES (:cedula,:nombre_habitante,:apellido,:fecha_nacimiento,:sexo,:telefono,:apartamento_id)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":cedula", $this->cedula);
            $conexion->bindParam(":nombre_habitante", $this->nombre_habitante);
            $conexion->bindParam(":apellido", $this->apellido);
            $conexion->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
            $conexion->bindParam(":sexo", $this->sexo);
            $conexion->bindParam(":telefono", $this->telefono);
            $conexion->bindParam(":apartamento_id", $this->apartamento_id);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();//obtenemos el ultimo id
                $this->set_id_habitante($id_ultimo["mensaje"]);
                $habitante_alterado = $this->consultar_habitante();//lo consultamos

                $this->registrar_bitacora(REGISTRAR, GESTIONAR_HABITANTES, $habitante_alterado["cedula"] . " (" . $habitante_alterado["nombre_habitante"] . ")");//registramos en la bitacora

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este habitante"];
            }
        }

        public function editar_habitante(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE habitantes SET cedula=:cedula,nombre_habitante=:nombre_habitante,apellido=:apellido,fecha_nacimiento=:fecha_nacimiento,sexo=:sexo,telefono=:telefono,apartamento_id=:apartamento_id WHERE id_habitante=:id_habitante";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_habitante", $this->id_habitante);
            $conexion->bindParam(":cedula", $this->cedula);
            $conexion->bindParam(":nombre_habitante", $this->nombre_habitante);
            $conexion->bindParam(":apellido", $this->apellido);
            $conexion->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
            $conexion->bindParam(":sexo", $this->sexo);
            $conexion->bindParam(":telefono", $this->telefono);
            $conexion->bindParam(":apartamento_id", $this->apartamento_id);

            $result = $conexion->execute();

            $this->cambiar_db_negocio();        
            
            if ($result) {
                $habitante_alterado = $this->consultar_habitante();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_HABITANTES, $habitante_alterado["cedula"] . " (" . $habitante_alterado["nombre_habitante"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este habitante"];
            }
        }

        public function eliminar_habitante(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            $habitante_alterado = $this->consultar_habitante();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM habitantes WHERE id_habitante = :id_habitante";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_habitante", $this->id_habitante);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_HABITANTES, $habitante_alterado["cedula"] . " (" . $habitante_alterado["nombre_habitante"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este habitante"];
            }
        }

        public function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_habitante) as last_id FROM habitantes";
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
    }
?>