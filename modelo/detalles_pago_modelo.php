<?php
    require_once("modelo/conexion.php");

    class Detalles_pago extends Conexion{
        private $id_detalle_pago;
        private $fecha;
        private $monto;
        private $monto_dolar;
        private $tipo_pago;
        private $pago_id;
        private $caja_id;

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

        public function set_caja_id($caja_id){
            $this->caja_id = $caja_id;
        }

        public function get_caja_id(){
            return $this->caja_id;
        }

        // Metodos CRUD
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

        public function consultar_detalle_pago(){

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
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            if ($result == true && $datos) {
                return $datos;
            } else {
                return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
            }
        }

        public function registrar_detalle_pago(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO detalles_pagos(fecha,monto,monto_dolar,tipo_pago,pago_id,caja_id) VALUES (:fecha,:monto,:monto_dolar,:tipo_pago,:pago_id,:caja_id)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":fecha", $this->fecha);
            $conexion->bindParam(":monto", $this->monto);
            $conexion->bindParam(":monto_dolar", $this->monto_dolar);
            $conexion->bindParam(":tipo_pago", $this->tipo_pago);
            $conexion->bindParam(":pago_id", $this->pago_id);
            $conexion->bindParam(":caja_id", $this->caja_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();//obtenemos el ultimo id
                $this->set_id_detalle_pago($id_ultimo["mensaje"]);
                $pago_alterado = $this->consultar_detalle_pago();//lo consultamos

                $this->registrar_bitacora(REGISTRAR, GESTIONAR_PAGOS, $pago_alterado["fecha"] . " (" . "Detalle pago anexado: ".$pago_alterado["monto"] . ")");//registramos en la bitacora
                //$this->registrar_notificacion();
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este detalle pago"];
            }
        }

        public function editar_detalle_pago(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE detalles_pagos SET fecha=:fecha,monto=:monto,monto_dolar=:monto_dolar,tipo_pago=:tipo_pago,pago_id=:pago_id,caja_id=:caja_id WHERE id_detalle_pago=:id_detalle_pago";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_detalle_pago", $this->id_detalle_pago);
            $conexion->bindParam(":fecha", $this->fecha);
            $conexion->bindParam(":monto", $this->monto);
            $conexion->bindParam(":monto_dolar", $this->monto_dolar);
            $conexion->bindParam(":tipo_pago", $this->tipo_pago);
            $conexion->bindParam(":pago_id", $this->pago_id);
            $conexion->bindParam(":caja_id", $this->caja_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();        
            
            if ($result) {
                $pago_alterado = $this->consultar_detalle_pago();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_PAGOS, $pago_alterado["fecha"] . " (" . "Detalle pago editado: ".$pago_alterado["monto"] . ")");
                
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este detalle pago"];
            }
        }

        public function eliminar_detalle_pago(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            $pago_alterado = $this->consultar_detalle_pago();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM detalles_pagos WHERE id_detalle_pago = :id_detalle_pago";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_detalle_pago", $this->id_detalle_pago);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_PAGOS, $pago_alterado["fecha"] . " (" . "Detalle pago eliminado: ".$pago_alterado["monto"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este detalle pago"];
            }
        }

        public function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_detalle_pago) as last_id FROM detalles_pagos";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);
            //$this->cambiar_db_negocio();

            if ($result) {
                return ["estatus"=>true,"mensaje"=>$datos["last_id"]];
            } else {
                return ["estatus"=>false,"mensaje"=>"Error en la consulta"];
            } 
        }

        public function consultar_detalles_por_pago() {
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

            if ($result) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_PAGOS, "DETALLES DE PAGO ID " . $this->pago_id);
                return $datos;
            } else {
                return ["estatus" => false, "mensaje" => "Error al consultar los detalles del pago."];
            }
        }
    }
?>