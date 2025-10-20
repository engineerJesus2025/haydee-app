<?php
    require_once("modelo/conexion.php");

    class Bancos_transacciones extends Conexion{
        private $id_banco_transaccion;
        private $referencia;
        private $imagen;
        private $detalle_pago_id;
        private $detalle_gasto_id;
        private $banco_id;

        public function __construct(){
            parent::__construct();
        }

        public function set_id_banco_transaccion($id_banco_transaccion){
            $this->id_banco_transaccion = $id_banco_transaccion;
        }

        public function get_id_banco_transaccion(){
            return $this->id_banco_transaccion;
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

        public function set_detalle_pago_id($detalle_pago_id){
            $this->detalle_pago_id = $detalle_pago_id;
        }

        public function get_detalle_pago_id(){
            return $this->detalle_pago_id;
        }

        public function set_detalle_gasto_id($detalle_gasto_id){
            $this->detalle_gasto_id = $detalle_gasto_id;
        }

        public function get_detalle_gasto_id(){
            return $this->detalle_gasto_id;
        }

        public function set_banco_id($banco_id){
            $this->banco_id = $banco_id;
        }

        public function get_banco_id(){
            return $this->banco_id;
        }

        public function realizar_consulta($accion){
            switch ($accion) {
                case 'validar':
                    $respuesta = $this->verificar_bancos_transacciones();

                    if ($respuesta["resultado"]) {
                        if (isset($respuesta["datos"]["referencia"])) {
                            return ["estatus"=>true,"busqueda"=>"referencia"];
                        } else {
                            return ["estatus"=>false,"busqueda"=>"referencia"];
                        }
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la validación de la transaccion bancaria"];
                    }

                case 'consultar':
                    $respuesta = $this->consultar();

                    if ($respuesta["resultado"]) {
                        $this->registrar_bitacora(CONSULTAR, GESTIONAR_PAGOS, "TODOS LOS DETALLES DE UN PAGO");
                        return $respuesta["datos"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                    }

                case 'consulta_especifica':
                    $respuesta = $this->consultar_banco_transaccion();

                    if ($respuesta["resultado"]) {
                        return $respuesta["datos"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta de esta transaccion bancaria"];
                    }

                case 'registrar':
                    $respuesta = $this->registrar_banco_transaccion();

                    if ($respuesta) {
                        $id_ultimo = $this->lastId();
                        $this->set_id_banco_transaccion($id_ultimo["datos"]["last_id"]);
                        $pago_alterado = $this->consultar_banco_transaccion();
                        $this->registrar_bitacora(REGISTRAR, GESTIONAR_PAGOS, $pago_alterado["datos"]["referencia"]);
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta transaccion de banco"];
                    }
                
                case 'modificar':
                    $respuesta = $this->editar_banco_transaccion();

                    if ($respuesta["resultado"]) {
                        if ($respuesta["fila_afectada"] < 1) {
                            return ["estatus"=>false,"mensaje"=>"No se realizaron cambios en la transaccion bancaria"];
                        }

                        $pago_alterado = $this->consultar_banco_transaccion();
                        $this->registrar_bitacora(MODIFICAR, GESTIONAR_PAGOS, $this->referencia);
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar esta transaccion de banco"];
                    }

                case 'eliminar':
                    $pago_alterado = $this->consultar_banco_transaccion();

                    if (empty($pago_alterado) || !isset($pago_alterado["datos"]["referencia"])) {
                        return ["estatus" => true, "mensaje" => "No hay transacción bancaria que eliminar"];
                    }

                    $respuesta = $this->eliminar_banco_transaccion();

                    if ($respuesta["resultado"]) {
                        if ($respuesta["fila_afectada"] < 1) {
                            return ["estatus"=>false,"mensaje"=>"No se pudo eliminar esta transaccion bancaria"];
                        }

                        $this->registrar_bitacora(ELIMINAR, GESTIONAR_PAGOS, $pago_alterado["datos"]["referencia"]);
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar esta transaccion de banco"];
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

        private function verificar_bancos_transacciones(){
            
            $sql = "SELECT * FROM banco_transacciones WHERE referencia = :referencia"; 
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":referencia", $this->referencia);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            return ["resultado" => $result, "datos" => $datos];
        }

        private function consultar(){

            //$this->cambiar_db_seguridad();
            $sql = "SELECT * FROM banco_transacciones ORDER BY id_banco_transaccion";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();

            return ["resultado" => $result, "datos" => $datos];
        }

        private function consultar_banco_transaccion(){

            //$this->cambiar_db_seguridad();
            //$this->cambiar_db_negocio();
            $sql = "SELECT * FROM banco_transacciones WHERE id_banco_transaccion = :id_banco_transaccion";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_banco_transaccion", $this->id_banco_transaccion);
            $result = $conexion->execute();        
            $filas = $conexion->fetch(PDO::FETCH_ASSOC);

            if (!$filas || count($filas) === 0) {
                return ["resultado" => false, "mensaje" => "null"];
            }

            return ["resultado" => $result, "datos" => $filas];
        }

        private function registrar_banco_transaccion(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO banco_transacciones(referencia,imagen,detalle_pago_id,banco_id) VALUES (:referencia,:imagen,:detalle_pago_id,:banco_id)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":referencia", $this->referencia);
            $conexion->bindParam(":imagen", $this->imagen);
            $conexion->bindParam(":detalle_pago_id", $this->detalle_pago_id);
            $conexion->bindParam(":banco_id", $this->banco_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            return $result;
        }

        private function editar_banco_transaccion(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE banco_transacciones 
            SET referencia=:referencia,
                imagen=:imagen,
                banco_id=:banco_id
            WHERE detalle_pago_id=:detalle_pago_id";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":referencia", $this->referencia);
            $conexion->bindParam(":imagen", $this->imagen);
            $conexion->bindParam(":detalle_pago_id", $this->detalle_pago_id);
            $conexion->bindParam(":banco_id", $this->banco_id);
            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();
        
            return ["resultado" => $result, "fila_afectada" => $filas_afectadas];
        }

        private function editar_banco_transaccion_original(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE banco_transacciones SET referencia=:referencia,imagen=:imagen,detalle_pago_id=:detalle_pago_id,banco_id=:banco_id WHERE id_banco_transaccion=:id_banco_transaccion";
            $conexion = $this->get_conex()->prepare($sql); 
            $conexion->bindParam(":id_banco_transaccion", $this->id_banco_transaccion); 
            $conexion->bindParam(":referencia", $this->referencia); 
            $conexion->bindParam(":imagen", $this->imagen); 
            $conexion->bindParam(":detalle_pago_id", $this->detalle_pago_id); 
            $conexion->bindParam(":banco_id", $this->banco_id); 
            $result = $conexion->execute(); 
            $filas_afectadas = $conexion->rowCount();

            return ["resultado" => $result, "fila_afectada" => $filas_afectadas];
        }

        private function eliminar_banco_transaccion(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            // $pago_alterado = $this->consultar_banco_transaccion();

            // if (empty($pago_alterado) || !isset($pago_alterado["referencia"])) {
            //     return ["estatus" => true, "mensaje" => "No hay transacción bancaria que eliminar"];
            // }

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM banco_transacciones WHERE id_banco_transaccion = :id_banco_transaccion";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_banco_transaccion", $this->id_banco_transaccion);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            return ["resultado" => $result, "fila_afectada" => $conexion->rowCount()];
        }

        private function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_banco_transaccion) as last_id FROM banco_transacciones";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);
            //$this->cambiar_db_negocio();

            return ["resultado" => $result, "datos" => $datos];
        }

        public function obtener_imagen_actual(){

            //$this->cambiar_db_seguridad(); 
            $sql = "SELECT imagen FROM banco_transacciones WHERE id_banco_transaccion = :id_banco_transaccion";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_banco_transaccion", $this->id_banco_transaccion);
            $conexion->execute();
            //$this->cambiar_db_negocio();

            $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado["imagen"] : null;
        }
    


    // BANCOS TRANSACCIONES DE GASTOS, ESTO SE DEBIO HACER DENTRO DE LOS MODELOS ORIGINALES PERO BUENO
    public function registrar_banco_transaccion_gasto(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO banco_transacciones(referencia,imagen,detalle_gasto_id,banco_id) VALUES (:referencia,:imagen,:detalle_gasto_id,:banco_id)";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":referencia", $this->referencia);
            $conexion->bindParam(":imagen", $this->imagen);
            $conexion->bindParam(":detalle_gasto_id", $this->detalle_gasto_id);
            $conexion->bindParam(":banco_id", $this->banco_id);
            
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();//obtenemos el ultimo id
                $this->set_id_banco_transaccion($id_ultimo["datos"]["last_id"]);
                $gasto_alterado = $this->consultar_banco_transaccion();//lo consultamos

                // var_dump($gasto_alterado);
                $this->registrar_bitacora(REGISTRAR, GESTIONAR_GASTOS, $gasto_alterado['datos']["referencia"]);//registramos en la bitacora
                //$this->registrar_notificacion();
                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta transaccion de banco"];
            }
        }

        public function editar_banco_transaccion_gasto(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE banco_transacciones SET referencia=:referencia,imagen=:imagen,detalle_gasto_id=:detalle_gasto_id,banco_id=:banco_id WHERE id_banco_transaccion=:id_banco_transaccion";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_banco_transaccion", $this->id_banco_transaccion);
            $conexion->bindParam(":referencia", $this->referencia);
            $conexion->bindParam(":imagen", $this->imagen);
            $conexion->bindParam(":detalle_gasto_id", $this->detalle_gasto_id);
            $conexion->bindParam(":banco_id", $this->banco_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();        
            
            if ($result) {
                $gasto_alterado = $this->consultar_banco_transaccion();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_GASTOS, $gasto_alterado["referencia"]);

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar esta transaccion de banco"];
            }
        }

        public function eliminar_banco_transaccion_gasto(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}

            $gasto_alterado = $this->consultar_banco_transaccion();

            if (empty($gasto_alterado) || !isset($gasto_alterado["referencia"])) {
                return ["estatus" => true, "mensaje" => "No hay transacción bancaria que eliminar"];
            }

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM banco_transacciones WHERE id_banco_transaccion = :id_banco_transaccion";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_banco_transaccion", $this->id_banco_transaccion);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_GASTOS, $gasto_alterado["referencia"]);

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar esta transaccion de banco"];
            }
        }
    }
?>