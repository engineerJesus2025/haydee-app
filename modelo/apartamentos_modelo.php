<?php
    require_once("modelo/conexion.php");

    class Apartamento extends Conexion{
        private $id_apartamento;
        private $nro_apartamento;
        private $porcentaje_participacion;
        private $gas;
        private $agua;
        private $alquilado;
        private $propietario_id;

        // Metodos Setter y Getter
        public function set_id_apartamento($id_apartamento){
            $this->id_apartamento = $id_apartamento;
        }

        public function get_id_apartamento(){
            return $this->id_apartamento;
        }

        public function set_nro_apartamento($nro_apartamento){
            $this->nro_apartamento = $nro_apartamento;
        }

        public function get_nro_apartamento(){
            return $this->nro_apartamento;
        }

        public function set_porcentaje_participacion($porcentaje_participacion){
            $this->porcentaje_participacion = $porcentaje_participacion;
        }

        public function get_porcentaje_participacion(){
            return $this->porcentaje_participacion;
        }

        public function set_gas($gas){
            $this->gas = $gas;
        }

        public function get_gas(){
            return $this->gas;
        }

        public function set_agua($agua){
            $this->agua = $agua;
        }

        public function get_agua(){
            return $this->agua;
        }

        public function set_alquilado($alquilado){
            $this->alquilado = $alquilado;
        }

        public function get_alquilado(){
            return $this->alquilado;
        }

        public function set_propietario_id($propietario_id){
            $this->propietario_id = $propietario_id;
        }

        public function get_propietario_id(){
            return $this->propietario_id;
        }

        // Metodos CRUD
        public function verificar_apartamento(){
            $sql = "SELECT * FROM apartamentos WHERE nro_apartamento = :nro_apartamento";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nro_apartamento", $this->nro_apartamento);
            $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);
            if (isset($datos["numero_cuenta"])) { 
                $r["estatus"] = true;
                $r["busqueda"] = "numero_cuenta";
                return $r;
            } else {
                $r["estatus"] = false;
                $r["busqueda"] = "numero_cuenta";
                return $r;
            }
        }

        public function consultar(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT * FROM apartamentos ORDER BY id_apartamento";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            $this->cambiar_db_negocio();

            if ($result == true) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_APARTAMENTOS, "TODOS LOS APARTAMENTOS");
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function consultar_apartamento(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT * FROM apartamentos WHERE id_apartamento = :id_apartamento";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_apartamento", $this->id_apartamento);
            $result = $conexion->execute();        
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function registrar_apartamento(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO apartamentos(nro_apartamento,porcentaje_participacion,gas,agua,alquilado,propietario_id) VALUES (:nro_apartamento,:porcentaje_participacion,:gas,:agua,:alquilado,:propietario_id)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nro_apartamento", $this->nro_apartamento);
            $conexion->bindParam(":porcentaje_participacion", $this->porcentaje_participacion);
            $conexion->bindParam(":gas", $this->gas);
            $conexion->bindParam(":agua", $this->agua);
            $conexion->bindParam(":alquilado", $this->alquilado);
            $conexion->bindParam(":propietario_id", $this->propietario_id);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();
                $this->set_id_apartamento($id_ultimo["mensaje"]);
                $apartamento_alterado = $this->consultar_apartamento();

                $this->registrar_bitacora(REGISTRAR, GESTIONAR_APARTAMENTOS, $apartamento_alterado["nro_apartamento"] . " (" . $apartamento_alterado["propietario_id"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este apartamento"];
            }
        }

        public function editar_apartamento(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE apartamentos SET nro_apartamento=:nro_apartamento,porcentaje_participacion=:porcentaje_participacion,gas=:gas,agua=:agua,alquilado=:alquilado,propietario_id=:propietario_id WHERE id_apartamento=:id_apartamento";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_apartamento", $this->id_apartamento);
            $conexion->bindParam(":nro_apartamento", $this->nro_apartamento);
            $conexion->bindParam(":porcentaje_participacion", $this->porcentaje_participacion);
            $conexion->bindParam(":gas", $this->gas);
            $conexion->bindParam(":agua", $this->agua);
            $conexion->bindParam(":alquilado", $this->alquilado);
            $conexion->bindParam(":propietario_id", $this->propietario_id);

            $result = $conexion->execute();

            $this->cambiar_db_negocio();        
            
            if ($result) {
                $apartamento_alterado = $this->consultar_apartamento();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_APARTAMENTOS, $apartamento_alterado["nro_apartamento"] . " (" . $apartamento_alterado["propietario_id"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este apartamento"];
            }
        }

        public function eliminar_apartamento(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            $apartamento_alterado = $this->consultar_apartamento();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM apartamentos WHERE id_apartamento = :id_apartamento";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_apartamento", $this->id_apartamento);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_APARTAMENTOS, $apartamento_alterado["nombre_banco"] . " (" . $apartamento_alterado["codigo"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este apartamento"];
            }
        }

        public function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_apartamento) as last_id FROM apartamentos";
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