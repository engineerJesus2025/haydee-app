<?php
    require_once("modelo/conexion.php");

    class Habitantes_apartamentos extends Conexion{
        // Atributos
        private $id_habitante_apartamento; 
        private $apartamento_id;
        private $habitante_id;
        private $tipo_vinculo;

        public function __construct(){
            parent::__construct();
        }

        public function set_id_habitante_apartamento($id_habitante_apartamento){
            $this->id_habitante_apartamento = $id_habitante_apartamento;
        }

        public function get_id_habitante_apartamento(){
            return $this->id_habitante_apartamento;
        }

        public function set_apartamento_id($apartamento_id){
            $this->apartamento_id = $apartamento_id;
        }

        public function get_apartamento_id(){
            return $this->apartamento_id;
        }

        public function set_habitante_id($habitante_id){
            $this->habitante_id = $habitante_id;
        }

        public function get_habitante_id(){
            return $this->habitante_id;
        }

        public function set_tipo_vinculo($tipo_vinculo){
            $this->tipo_vinculo = $tipo_vinculo;
        }

        public function get_tipo_vinculo(){
            return $this->tipo_vinculo;
        }

        // Metodos CRUD
        public function registrar_habitante_apartamento(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            $validaciones = $this->validarDatos(false);
            if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO habitantes_apartamentos(apartamento_id,habitante_id,tipo_vinculo) VALUES (:apartamento_id,:habitante_id,:tipo_vinculo)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":apartamento_id", $this->apartamento_id);
            $conexion->bindParam(":habitante_id", $this->habitante_id);
            $conexion->bindParam(":tipo_vinculo", $this->tipo_vinculo);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar a este habitante"];
            }
        }

        public function editar_habitante_apartamento(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            $validaciones = $this->validarDatos(false);
            if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "UPDATE habitantes_apartamentos SET apartamento_id = :apartamento_id, tipo_vinculo = :tipo_vinculo WHERE habitante_id = :habitante_id";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":apartamento_id", $this->apartamento_id);
            $conexion->bindParam(":tipo_vinculo", $this->tipo_vinculo);
            $conexion->bindParam(":habitante_id", $this->habitante_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar actualizar a esta habitante"];
            }
        }

        public function consultar_habitantes_por_apartamento() {
            $sql = "SELECT 
                        h.id_habitante,
                        h.nombre,
                        h.apellido,
                        h.cedula,
                        h.telefono,
                        h.correo,
                        h.fecha_nacimiento,
                        h.sexo,
                        ha.tipo_vinculo,
                        a.nro_apartamento
                    FROM habitantes h
                    INNER JOIN habitantes_apartamentos ha ON ha.habitante_id = h.id_habitante
                    INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                    WHERE a.id_apartamento = :apartamento_id
                    ORDER BY h.apellido ASC, h.nombre ASC";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":apartamento_id", $this->apartamento_id);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_HABITANTES, 'HABITANTES EN APARTAMENTO #' . $this->apartamento_id);
                return $datos;
            } else {
                return ["estatus" => false, "mensaje" => "Error al consultar los habitantes del apartamento."];
            }
        }

        private function validarDatos($eliminar = false){   

                if (!(isset($this->apartamento_id))) {return ["estatus"=>false,"mensaje"=>"El ID del Apartamento no se recibio correctamente para registrar o modificar"];}

                if (empty($this->apartamento_id)) {return ["estatus"=>false,"mensaje"=>"El ID del Apartamento para registrar o modificar se envio vacio"];}

                if(is_numeric($this->apartamento_id)){
                    if (!($this->validarClaveForanea("apartamentos","id_apartamento",$this->apartamento_id))) {
                        return ["estatus"=>false,"mensaje"=>"El Apartamento seleccionado para registrar o modificar no existe"];
                    }
                    if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
                }
                else{return ["estatus"=>false,"mensaje"=>"El id del Apartamento para registrar o modificar debe ser un valor numerico entero"];}

                if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
            // Validamos que los campos enviados si existan
            if (!(isset($this->habitante_id))) {return ["estatus"=>false,"mensaje"=>"El ID del Habitante no se recibio correctamente"];}

            // Validamos que los campos enviados no esten vacios        
            if (empty($this->habitante_id)) {return ["estatus"=>false,"mensaje"=>"El ID del Habitante esta vacio"];}

            if(is_numeric($this->habitante_id)){
                if (!($this->validarClaveForanea("habitantes","id_habitante",$this->habitante_id))) {
                    return ["estatus"=>false,"mensaje"=>"El ID de Habitante seleccionado para modificar permisos no existe"];
                }
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El id del Habitante para modificar permisos tiene debe ser un valor numerico entero"];
            }

            if (!(isset($this->tipo_vinculo))) {return ["estatus"=>false,"mensaje"=>"El tipo de vinculo no se recibio correctamente"];}
            if (empty($this->tipo_vinculo)) {return ["estatus"=>false,"mensaje"=>"El tipo de vinculo esta vacio"];}
            
            if(!(is_string($this->tipo_vinculo))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Vinculo' no posee un valor valido"];
            }

            return ["estatus"=>true,"mensaje"=>"OK"];
        }
        
        private function validarClaveForanea($tabla,$nombreClave,$valor){
            $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":valor", $valor);
            $conexion->execute();
            $result = $conexion->fetch(PDO::FETCH_ASSOC);
           
            return ($result)?true:false;        
        }
    }
?>