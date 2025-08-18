<?php
    require_once("modelo/conexion.php");

    class Pagos_mensualidad extends Conexion{
        private $id_pago_mensualidad;
        private $detalle_pago_id;
        private $mensualidad_id;

        public function __construct(){
            parent::__construct();
        }

        // Metodos Setter Y Getter
        public function set_id_pago_mensualidad($id_pago_mensualidad){
            $this->id_pago_mensualidad = $id_pago_mensualidad;
        }

        public function get_id_pago_mensualidad(){
            return $this->id_pago_mensualidad;
        }

        public function set_detalle_pago_id($detalle_pago_id){
            $this->detalle_pago_id = $detalle_pago_id;
        }

        public function get_detalle_pago_id(){
            return $this->detalle_pago_id;
        }

        public function set_mensualidad_id($mensualidad_id){
            $this->mensualidad_id = $mensualidad_id;
        }

        public function get_mensualidad_id(){
            return $this->mensualidad_id;
        }

        public function registrar_pagos_mensualidad(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO pagos_mensualidad(detalle_pago_id,mensualidad_id) VALUES (:detalle_pago_id,:mensualidad_id)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":detalle_pago_id", $this->detalle_pago_id);
            $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta pago mensualidad"];
            }
        }

        public function editar_pagos_mensualidad(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "UPDATE pagos_mensualidad SET mensualidad_id = :mensualidad_id WHERE detalle_pago_id = :detalle_pago_id";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":detalle_pago_id", $this->detalle_pago_id);
            $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar actualizar a este pago mensualidad"];
            }
        }

        public function eliminar_pagos_mensualidad(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM pagos_mensualidad WHERE id_pago_mensualidad = :id_pago_mensualidad";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_pago_mensualidad", $this->id_pago_mensualidad);
            $result = $conexion->execute();
            
            if ($result) {
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este apartamento"];
            }
        }
    }
?>