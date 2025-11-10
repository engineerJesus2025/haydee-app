<?php
    namespace haydee\modelo;
    use haydee\modelo\Conexion;
    use PDO;
    
    class DetallesPago extends Conexion{
        private $id_detalle_pago;
        private $fecha;
        private $monto;
        private $monto_dolar;
        private $tipo_pago;
        private $pago_id;

        public function __construct(){
            parent::__construct();
        }

        // Metodos Setter y Getter
        public function set_id_detalle_pago($id_detalle_pago){
            $this->id_detalle_pago = $id_detalle_pago;
        }

        public function get_id_detalle_pago(){
            return $this->id_detalle_pago;
        }

        public function set_fecha($fecha){
            $this->fecha = $fecha;
        }

        public function get_fecha(){
            return $this->fecha;
        }

        public function set_monto($monto){
            $this->monto = $monto;
        }

        public function get_monto(){
            return $this->monto;
        }

        public function set_monto_dolar($monto_dolar){
            $this->monto_dolar = $monto_dolar;
        }

        public function get_monto_dolar(){
            return $this->monto_dolar;
        }

        public function set_tipo_pago($tipo_pago){
            $this->tipo_pago = $tipo_pago;
        }

        public function get_tipo_pago(){
            return $this->tipo_pago;
        }

        public function set_pago_id($pago_id){
            $this->pago_id = $pago_id;
        }

        public function get_pago_id(){
            return $this->pago_id;
        }

        // Metodos CRUD
        public function realizar_consulta($accion){
            switch($accion){
                case 'consultar_detalles':

                    $respuesta = $this->consultar_detalles_por_pago();

                    if ($respuesta["resultado"]) {

                        $this->registrar_bitacora(CONSULTAR, GESTIONAR_PAGOS, "DETALLES DE PAGO ID " . $this->pago_id);
                        return $respuesta["datos"];

                    } else {
                        return ["estatus" => false, "mensaje" => "Error al consultar los detalles del pago."];
                    }

                case 'registrar_detalles':
                    $validaciones = $this->validarDatos();
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->registrar_detalle_pago();

                    if ($respuesta) {
                        $id_ultimo = $this->lastId();
                        $this->set_id_detalle_pago($id_ultimo["datos"]["last_id"]);
                        $pago_alterado = $this->consultar_detalle_pago();//lo consultamos

                        $this->registrar_bitacora(REGISTRAR, GESTIONAR_PAGOS, $pago_alterado["datos"]["fecha"] . " (" . "Detalle pago anexado: ".$pago_alterado["datos"]["monto"] . ")");//registramos en la bitacora
                        //$this->registrar_notificacion();
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este detalle pago"];
                    }
                
                case 'consulta_especifica_detalles':
                    $respuesta = $this->consultar_detalle_pago();

                    if ($respuesta["resultado"]) {
                        return $respuesta["datos"];
                    } else {
                        return ["estatus" => false, "mensaje" => "Error al consultar el detalle del pago."];
                    }

                case 'modificar_detalles':
                    $validaciones = $this->validarDatos("editar");
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->editar_detalle_pago();

                    if ($respuesta["resultado"]) {
                        if ($respuesta["fila_afectada"] < 1) {
                            return ["estatus" => false, "mensaje" => "No se modifico el detalle del pago"];
                        }

                        $pago_alterado = $this->consultar_detalle_pago();
                        $this->registrar_bitacora(MODIFICAR, GESTIONAR_PAGOS, $pago_alterado["datos"]["fecha"] . " (" . "Detalle pago editado: ".$pago_alterado["datos"]["monto"] . ")");
                
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este detalle pago"];
                    }

                case 'eliminar_detalles':
                    $validaciones = $this->validarDatos("eliminar");
                    if(!($validaciones["estatus"])){return $validaciones;}
                    
                    $pago_alterado = $this->consultar_detalle_pago();

                    $respuesta = $this->eliminar_detalle_pago();

                    if ($respuesta["resultado"]) {
                        if ($respuesta["fila_afectada"] < 1) {
                            return ["estatus"=>false,"mensaje"=>"No se eliminó ningún detalle pago"];
                        }

                        $this->registrar_bitacora(ELIMINAR, GESTIONAR_PAGOS, $pago_alterado["datos"]["fecha"] . " (" . $pago_alterado["datos"]["monto"] . ")");
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este detalle pago"];
                    }

                case 'lastId':
                    $respuesta = $this->lastId();
                    
                    if ($respuesta["resultado"]) {
                        return $respuesta["datos"];
                    } 
                    else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                    }

                default:
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error en el proceso"];
                    break;
            }
        }

        public function consultar(){

            //$this->cambiar_db_seguridad();
            $sql = "SELECT * FROM detalles_pagos ORDER BY id_detalle_pago";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();

            if ($result == true) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_PAGOS, "TODOS LOS DETALLES DE UN PAGO");//registra cuando se entra al modulo de pagos

                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        private function consultar_detalle_pago(){ // SI SE USA

            //$this->cambiar_db_seguridad();
            //$this->cambiar_db_negocio();
            $sql = "SELECT 
                dp.*, 
                bt.id_banco_transaccion,
                bt.referencia,
                bt.imagen,
                b.id_banco,
                b.nombre_banco,
                pm.mensualidad_id,
                m.apartamento_id,
                m.monto AS monto_mensualidad,
                m.mes,
                m.anio
            FROM detalles_pagos dp
            LEFT JOIN banco_transacciones bt ON bt.detalle_pago_id = dp.id_detalle_pago
            LEFT JOIN bancos b ON bt.banco_id = b.id_banco
            LEFT JOIN pagos_mensualidad pm ON pm.detalle_pago_id = dp.id_detalle_pago
            LEFT JOIN mensualidad m ON m.id_mensualidad = pm.mensualidad_id
            WHERE dp.id_detalle_pago = :id_detalle_pago";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_detalle_pago", $this->id_detalle_pago);
            $result = $conexion->execute();        
            $filas = $conexion->fetch(PDO::FETCH_ASSOC);

            if (!$filas || count($filas) === 0) {
                return ["estatus" => false, "mensaje" => "No se encontró el detalle de pago."];
            }

            return ["resultado" => $result, "datos" => $filas];
        }

        private function registrar_detalle_pago(){ // SI SE USA
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO detalles_pagos(fecha,monto,monto_dolar,tipo_pago,pago_id) VALUES (:fecha,:monto,:monto_dolar,:tipo_pago,:pago_id)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":fecha", $this->fecha);
            $conexion->bindParam(":monto", $this->monto);
            $conexion->bindParam(":monto_dolar", $this->monto_dolar);
            $conexion->bindParam(":tipo_pago", $this->tipo_pago);
            $conexion->bindParam(":pago_id", $this->pago_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            return $result;
        }

        private function editar_detalle_pago(){ // SI SE USA
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE detalles_pagos SET fecha=:fecha,monto=:monto,monto_dolar=:monto_dolar,tipo_pago=:tipo_pago,pago_id=:pago_id WHERE id_detalle_pago=:id_detalle_pago";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_detalle_pago", $this->id_detalle_pago);
            $conexion->bindParam(":fecha", $this->fecha);
            $conexion->bindParam(":monto", $this->monto);
            $conexion->bindParam(":monto_dolar", $this->monto_dolar);
            $conexion->bindParam(":tipo_pago", $this->tipo_pago);
            $conexion->bindParam(":pago_id", $this->pago_id);
            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            //$this->cambiar_db_negocio();        
            
            return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
        }

        private function eliminar_detalle_pago(){ // SI SE USA
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$pago_alterado = $this->consultar_detalle_pago();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM detalles_pagos WHERE id_detalle_pago = :id_detalle_pago";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_detalle_pago", $this->id_detalle_pago);
            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            //$this->cambiar_db_negocio();
            
            return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
        }

        private function lastId(){ // SI SE USA
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_detalle_pago) as last_id FROM detalles_pagos";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);
            //$this->cambiar_db_negocio();

            return ["resultado"=>$result,"datos"=>$datos];
        }

        private function consultar_detalles_por_pago() { // SI SE USA
            $sql = "SELECT 
                dp.*, 
                bt.referencia, 
                bt.imagen,
                b.nombre_banco
            FROM detalles_pagos dp
            LEFT JOIN banco_transacciones bt ON bt.detalle_pago_id = dp.id_detalle_pago
            LEFT JOIN bancos b ON bt.banco_id = b.id_banco
            WHERE dp.pago_id = :pago_id
            ORDER BY dp.fecha ASC";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":pago_id", $this->pago_id);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            return ["resultado" => $result, "datos" => $datos];
        }

        public function eliminar_detalles_por_pago() { // SI SE USA
            // 1. ELIMINAR REGISTROS DE BANCOS_TRANSACCIONES ASOCIADOS PRIMERO
            $sql_transacciones = "DELETE FROM banco_transacciones WHERE detalle_pago_id IN (SELECT id_detalle_pago FROM detalles_pagos WHERE pago_id = :pago_id)";
            $conexion_trans = $this->get_conex()->prepare($sql_transacciones);
            $conexion_trans->bindParam(":pago_id", $this->pago_id);
            $resultado_trans = $conexion_trans->execute();

            // 2. ELIMINAR FINALMENTE LOS REGISTROS DE DETALLES_PS
            $sql_detalles = "DELETE FROM detalles_pagos WHERE pago_id = :pago_id";
            $conexion_det = $this->get_conex()->prepare($sql_detalles);
            $conexion_det->bindParam(":pago_id", $this->pago_id);
            $resultado_det = $conexion_det->execute();


            if ($resultado_trans && $resultado_det) {
                // 3. REGISTRAR EN BITÁCORA Y DEVOLVER ÉXITO
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_PAGOS, "Eliminados todos los detalles del Pago ID: " . $this->pago_id);
                return ["estatus" => true, "mensaje" => "OK"];
            } else {
                return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar eliminar los detalles del pago."];
            }
        }

        private function validarDatos($consulta = "registrar"){
            if ($consulta == "editar" || $consulta == "eliminar") {
                if (!(isset($this->id_detalle_pago))) {return ["estatus"=>false,"mensaje"=>"El id del Detalle Pago requerido no se recibio correctamente"];}

                if (empty($this->id_detalle_pago)) {return ["estatus"=>false,"mensaje"=>"El id del Detalle Pago requerido esta vacio"];}

                if(is_numeric($this->id_detalle_pago)){
                    if (!($this->validarClaveForanea("detalles_pagos","id_detalle_pago",$this->id_detalle_pago))) {
                        return ["estatus"=>false,"mensaje"=>"El Detalle Pago seleccionado no existe"];
                    }
                    if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
                }
                else{return ["estatus"=>false,"mensaje"=>"El id del Detalle Pago debe ser un valor numerico entero"];}
            }

            if (!(isset($this->fecha) && isset($this->monto) && isset($this->monto_dolar) && isset($this->tipo_pago) && isset($this->pago_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            if (empty($this->fecha) || empty($this->monto) || empty($this->monto_dolar) || empty($this->tipo_pago) || empty($this->pago_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            if(!(is_string($this->fecha))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Fecha' no posee un valor valido"];
            }
            if(!(is_numeric($this->monto)) || !(preg_match("/^\d{1,6}(\.\d{1,2})?$/",$this->monto))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Monto' no posee un valor valido"];
            }
            if(!(is_numeric($this->monto_dolar)) || !(preg_match("/^\d{1,6}(\.\d{1,2})?$/",$this->monto_dolar))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Monto' no posee un valor valido"];
            }
            if(!(is_string($this->tipo_pago))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Método de Pago' no posee un valor valido"];
            }
            if(!(is_numeric($this->pago_id))){
                return ["estatus"=>false,"mensaje"=>"El id del Pago asociado no posee un valor valido"];
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