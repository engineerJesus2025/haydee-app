<?php
    require_once("modelo/conexion.php");

    class Personas_apartamentos extends Conexion{
        // Atributos
        private $id_persona_apartamento; 
        private $apartamento_id;
        private $persona_id;
        private $tipo_vinculo;

        public function __construct(){
            parent::__construct();
        }

        public function set_id_persona_apartamento($id_persona_apartamento){
            $this->id_persona_apartamento = $id_persona_apartamento;
        }

        public function get_id_persona_apartamento(){
            return $this->id_persona_apartamento;
        }

        public function set_apartamento_id($apartamento_id){
            $this->apartamento_id = $apartamento_id;
        }

        public function get_apartamento_id(){
            return $this->apartamento_id;
        }

        public function set_persona_id($persona_id){
            $this->persona_id = $persona_id;
        }

        public function get_persona_id(){
            return $this->persona_id;
        }

        public function set_tipo_vinculo($tipo_vinculo){
            $this->tipo_vinculo = $tipo_vinculo;
        }

        public function get_tipo_vinculo(){
            return $this->tipo_vinculo;
        }

        // Metodos CRUD
        public function registrar_persona_apartamento(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO personas_apartamentos(apartamento_id,persona_id,tipo_vinculo) VALUES (:apartamento_id,:persona_id,:tipo_vinculo)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":apartamento_id", $this->apartamento_id);
            $conexion->bindParam(":persona_id", $this->persona_id);
            $conexion->bindParam(":tipo_vinculo", $this->tipo_vinculo);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar a esta persona"];
            }
        }

        public function editar_persona_apartamento(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "UPDATE personas_apartamentos SET apartamento_id = :apartamento_id, tipo_vinculo = :tipo_vinculo WHERE persona_id = :persona_id";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":apartamento_id", $this->apartamento_id);
            $conexion->bindParam(":tipo_vinculo", $this->tipo_vinculo);
            $conexion->bindParam(":persona_id", $this->persona_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar actualizar a esta persona"];
            }
        }
    }
?>