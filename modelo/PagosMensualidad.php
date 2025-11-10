<?php
    namespace haydee\modelo;
    use haydee\modelo\Conexion;
    use PDO;
    
    class PagosMensualidad extends Conexion{
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

        public function realizar_consulta($accion){
            switch($accion){
                case 'registrar':
                    $validaciones = $this->validarDatos(false);
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->registrar_pagos_mensualidad();
                
                    if ($respuesta) {
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta pago mensualidad"];
                    }
                
                case 'modificar':
                    $validaciones = $this->validarDatos(false);
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->editar_pagos_mensualidad();

                    if ($respuesta["resultado"]) {
                        // if ($respuesta["filas_afectada"] < 1) {
                        //     return ["estatus"=>false,"mensaje"=>"No se modificó ningún registro en la tabla pagos_mensualidad"];
                        // }

                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar actualizar a este pago mensualidad"];
                    }

                case 'eliminar':
                    $respuesta = $this->eliminar_pagos_mensualidad();

                    if ($respuesta["resultado"]) {
                        // if ($respuesta["filas_afectada"] < 1) {
                        //     return ["estatus"=>false,"mensaje"=>"No se eliminó ningún registro en la tabla pagos mensualidad"];
                        // }

                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este pago mensualidad"];
                    }

                default:
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error en el proceso"];
                    break;
            }
        }

        private function registrar_pagos_mensualidad(){
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

            return $result;
        }

        private function editar_pagos_mensualidad(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "UPDATE pagos_mensualidad SET mensualidad_id = :mensualidad_id WHERE detalle_pago_id = :detalle_pago_id";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":detalle_pago_id", $this->detalle_pago_id);
            $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            //$this->cambiar_db_negocio();

            return ["resultado"=>$result,"filas_afectada"=>$filas_afectadas];
        }

        private function eliminar_pagos_mensualidad(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM pagos_mensualidad WHERE id_pago_mensualidad = :id_pago_mensualidad";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_pago_mensualidad", $this->id_pago_mensualidad);
            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            //$this->cambiar_db_negocio();

            return ["resultado"=>$result,"filas_afectada"=>$filas_afectadas];
        }

        private function validarDatos($eliminar = false){
            if (!(isset($this->detalle_pago_id))) {return ["estatus"=>false,"mensaje"=>"El ID del Detalle Pago no se recibio correctamente para registrar o modificar"];}

                if (empty($this->detalle_pago_id)) {return ["estatus"=>false,"mensaje"=>"El ID del Detalle Pago para registrar o modificar se envio vacio"];}

                if(is_numeric($this->detalle_pago_id)){
                    if (!($this->validarClaveForanea("detalles_pagos","id_detalle_pago",$this->detalle_pago_id))) {
                        return ["estatus"=>false,"mensaje"=>"El Detalle Pago seleccionado para registrar o modificar no existe"];
                    }
                    if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
                }
                else{return ["estatus"=>false,"mensaje"=>"El id del Detalle Pago para registrar o modificar debe ser un valor numerico entero"];}

                if ($eliminar) {return ["estatus"=>true,"mensaje"=>"OK"];}
            
            if (!(isset($this->mensualidad_id))) {return ["estatus"=>false,"mensaje"=>"El ID de la Mensualidad no se recibio correctamente"];}

            // Validamos que los campos enviados no esten vacios        
            if (empty($this->mensualidad_id)) {return ["estatus"=>false,"mensaje"=>"El ID de la Mensualidad esta vacio"];}

            if(is_numeric($this->mensualidad_id)){
                if (!($this->validarClaveForanea("mensualidad","id_mensualidad",$this->mensualidad_id))) {
                    return ["estatus"=>false,"mensaje"=>"El ID de la Mensualidad seleccionado para modificar no existe"];
                }
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El id de la Mensualidad para modificar debe ser un valor numerico entero"];
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