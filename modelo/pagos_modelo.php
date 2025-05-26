<?php
    require_once("modelo/conexion.php");

    class Pagos extends Conexion{
        private $id_pago;
        private $fecha;
        private $monto;
        private $tasa_dolar;
        private $estado;
        private $metodo_pago;
        private $banco_id;
        private $referencia;
        private $imagen;
        private $observacion;

        // Campos de la tabla puente
        private $id_pago_mensualidad;
        private $pago_id;
        private $mensualidad_id;

        public function __construct(){
            parent::__construct();
        }

        // Metodos Setter y Getter
        public function set_id_pago($id_pago){
            $this->id_pago = $id_pago;
        }

        public function get_id_pago(){
            return $this->id_pago;
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

        public function set_tasa_dolar($tasa_dolar){
            $this->tasa_dolar = $tasa_dolar;
        }

        public function get_tasa_dolar(){
            return $this->tasa_dolar;
        }

        public function set_estado($estado){
            $this->estado = $estado;
        }

        public function get_estado(){
            return $this->estado;
        }

        public function set_metodo_pago($metodo_pago){
            $this->metodo_pago = $metodo_pago;
        }

        public function get_metodo_pago(){
            return $this->metodo_pago;
        }

        public function set_banco_id($banco_id){
            $this->banco_id = $banco_id;
        }

        public function get_banco_id(){
            return $this->banco_id;
        }

        public function set_referencia($referencia){
            $this->referencia = $referencia;
        }

        public function get_referencia(){
            return $this->referencia;
        }

        public function set_imagen($imagen){
            $this->imagen = $imagen;
        }

        public function get_imagen(){
            return $this->imagen;
        }

        public function set_observacion($observacion){
            $this->observacion = $observacion;
        }

        public function get_observacion(){
            return $this->observacion;
        }

        // Metodos Get y Set de la tabla puente

        public function set_id_pago_mensualidad($id_pago_mensualidad){
            $this->id_pago_mensualidad = $id_pago_mensualidad;
        }

        public function get_id_pago_mensualidad(){
            return $this->id_pago_mensualidad;
        }

        public function set_pago_id($pago_id){
            $this->pago_id = $pago_id;
        }

        public function get_pago_id(){
            return $this->pago_id;
        }

        public function set_mensualidad_id($mensualidad_id){
            $this->mensualidad_id = $mensualidad_id;
        }

        public function get_mensualidad_id(){
            return $this->mensualidad_id;
        }

        // Metodos CRUD
        public function verificar_pago(){ 

            
            $sql = "SELECT * FROM pagos WHERE referencia = :referencia"; 
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":referencia", $this->referencia);
            $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            if (isset($datos["referencia"])) {   
                $r["estatus"] = true; 
                $r["busqueda"] = "referencia"; 
                return $r; 
            } else { 
                $r["estatus"] = false;
                $r["busqueda"] = "referencia";
                return $r;
            }
        }

        public function consultar(){

            //$this->cambiar_db_seguridad();

            // Mouseque Herramienta misteriosa que nos ayudara mas tarde
            // SELECT pagos.id_pago, pagos.fecha, apartamentos.nro_apartamento, pagos.monto, pagos.tasa_dolar, pagos.estado, pagos.metodo_pago, bancos.nombre_banco, pagos.referencia, pagos.imagen, pagos.observacion FROM pagos JOIN bancos ON pagos.banco_id = bancos.id_banco JOIN pagos_mensualidad ON pagos.id_pago = pagos_mensualidad.pago_id JOIN mensualidad ON pagos_mensualidad.mensualidad_id = mensualidad.id_mensualidad JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento ORDER BY pagos.fecha DESC, pagos.estado = 'PENDIENTE' DESC;
            // 1) Sentencia SQL de toda la vida.
            $sql = "SELECT pagos.id_pago, pagos.fecha, pagos.monto, apartamentos.nro_apartamento, pagos.tasa_dolar, pagos.estado, pagos.metodo_pago, bancos.nombre_banco, pagos.referencia, pagos.imagen, pagos.observacion, CONCAT(CASE mensualidad.mes WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo' WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio' WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre' WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre' END, ' ', mensualidad.anio) AS mes_anio FROM pagos JOIN bancos ON pagos.banco_id = bancos.id_banco JOIN pagos_mensualidad ON pagos.id_pago = pagos_mensualidad.pago_id JOIN mensualidad ON pagos_mensualidad.mensualidad_id = mensualidad.id_mensualidad JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento ORDER BY pagos.fecha DESC, pagos.estado = 'PENDIENTE' DESC";

            // 2) Se prepara una consulta a la base de datos.
            $conexion = $this->get_conex()->prepare($sql);

            // 3) Se ejecuta la consulta SQL en la base de datos.
            $result = $conexion->execute();
            
            // 4)Se obtiene la primera fila del resultado como un arreglo asociativo.
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            $this->cambiar_db_negocio();

            if ($result == true) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_PAGOS, "TODOS LOS PAGOS");//registra cuando se entra al modulo de pagos

                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function consultar_pago(){

            //$this->cambiar_db_seguridad();

            // 1) Sentencia SQL de toda la vida
            $sql = "SELECT * FROM pagos WHERE id_pago = :id_pago";

            // 2) Se prepara la conexion
            $conexion = $this->get_conex()->prepare($sql);

            // 3) Se manda el banco que queremos consultar
            $conexion->bindParam(":id_pago", $this->id_pago);

            // 4) Se ejecuta la sentencia
            $result = $conexion->execute();        
            
            // 5) Se obtienen los datos
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function registrar_pago(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO pagos(fecha,monto,tasa_dolar,estado,metodo_pago,banco_id,referencia,imagen,observacion) VALUES (:fecha,:monto,:tasa_dolar,:estado,:metodo_pago,:banco_id,:referencia,:imagen,:observacion)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":fecha", $this->fecha);
            $conexion->bindParam(":monto", $this->monto);
            $conexion->bindParam(":tasa_dolar", $this->tasa_dolar);
            $conexion->bindParam(":estado", $this->estado);
            $conexion->bindParam(":metodo_pago", $this->metodo_pago);
            $conexion->bindParam(":banco_id", $this->banco_id);
            $conexion->bindParam(":referencia", $this->referencia);
            $conexion->bindParam(":imagen", $this->imagen);
            $conexion->bindParam(":observacion", $this->observacion);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();//obtenemos el ultimo id
                $this->set_id_pago($id_ultimo["mensaje"]);
                $pago_alterado = $this->consultar_pago();//lo consultamos

                $this->registrar_bitacora(REGISTRAR, GESTIONAR_PAGOS, $pago_alterado["monto"] . " (" . $pago_alterado["fecha"] . ")");//registramos en la bitacora

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este pago"];
            }
        }

        public function registrar_pago_mensualidad(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO pagos_mensualidad(pago_id,mensualidad_id) VALUES (pago_id,mensualidad_id)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":pago_id", $this->pago_id);
            $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();//obtenemos el ultimo id
                $this->set_id_pago($id_ultimo["mensaje"]);
                $pago_alterado = $this->consultar_pago();//lo consultamos

                //$this->registrar_bitacora(REGISTRAR, GESTIONAR_PAGOS, $pago_alterado["monto"] . " (" . $pago_alterado["fecha"] . ")");//registramos en la bitacora

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este pago"];
            }
        }

        public function editar_pago(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE pagos SET fecha=:fecha,monto=:monto,tasa_dolar=:tasa_dolar,estado=:estado,metodo_pago=:metodo_pago,banco_id=:banco_id,referencia=:referencia,imagen=:imagen,observacion=:observacion WHERE id_pago=:id_pago";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_pago", $this->id_pago);
            $conexion->bindParam(":fecha", $this->fecha);
            $conexion->bindParam(":monto", $this->monto);
            $conexion->bindParam(":tasa_dolar", $this->tasa_dolar);
            $conexion->bindParam(":estado", $this->estado);
            $conexion->bindParam(":metodo_pago", $this->metodo_pago);
            $conexion->bindParam(":banco_id", $this->banco_id);
            $conexion->bindParam(":referencia", $this->referencia);
            $conexion->bindParam(":imagen", $this->imagen);
            $conexion->bindParam(":observacion", $this->observacion);

            $result = $conexion->execute();

            $this->cambiar_db_negocio();        
            
            if ($result) {
                $pago_alterado = $this->consultar_pago();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_PAGOS, $pago_alterado["monto"] . " (" . $pago_alterado["fecha"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este pago"];
            }
        }

        public function eliminar_pago(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            $pago_alterado = $this->consultar_pago();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM pagos WHERE id_pago = :id_pago";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_pago", $this->id_pago);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_PAGOS, $pago_alterado["monto"] . " (" . $pago_alterado["fecha"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este pago"];
            }
        }

        public function obtener_imagen_actual(){

            $this->cambiar_db_seguridad(); 
            $sql = "SELECT imagen FROM pagos WHERE id_pago = :id_pago";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_pago", $this->id_pago);
            $conexion->execute();
            $this->cambiar_db_negocio();

            $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado["imagen"] : null;
        }

        public function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_pago) as last_id FROM pagos";
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

        public function registrar_pagos_mensualidad(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO pagos_mensualidad(pago_id,mensualidad_id) VALUES (:pago_id,:mensualidad_id)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":pago_id", $this->pago_id);
            $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastIdPagoMensualidad();//obtenemos el ultimo id
                $this->set_id_pago_mensualidad($id_ultimo["mensaje"]);

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este pago"];
            }
        }

        public function lastIdPagoMensualidad(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_pago_mensualidad) as last_id FROM pagos_mensualidad";
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

        public function consultarMensualidad(){
            $sql = "SELECT m.id_mensualidad,m.monto,m.tasa_dolar,m.mes,m.anio,a.nro_apartamento,m.gasto_mes_id FROM mensualidad m JOIN apartamentos a ON m.apartamento_id = a.id_apartamento ORDER BY a.nro_apartamento, m.anio, m.mes";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
            $this->cambiar_db_negocio();
            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }
    }
?>