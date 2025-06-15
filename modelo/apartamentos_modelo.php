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
            $sql = "SELECT a.id_apartamento, a.nro_apartamento, CONCAT(a.porcentaje_participacion, '%') AS porcentaje_participacion, CASE a.gas WHEN 1 THEN 'TIENE' WHEN 0 THEN 'NO TIENE' END AS gas, CASE a.agua WHEN 1 THEN 'TIENE' WHEN 0 THEN 'NO TIENE' END AS agua, CASE a.alquilado WHEN 1 THEN 'SI' WHEN 0 THEN 'NO' END AS alquilado, CONCAT(p.nombre, ' ', p.apellido) AS propietario_id FROM apartamentos a JOIN propietarios p ON a.propietario_id = p.id_propietario ORDER BY a.id_apartamento";
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

            $this->cambiar_db_negocio();

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

            $this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_APARTAMENTOS, $apartamento_alterado["nro_apartamento"] . " (" . $apartamento_alterado["propietario_id"] . ")");

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

        private function validarDatos($consulta = "registrar"){   
            // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
            if ($consulta == "editar" || $consulta == "eliminar") {
                if (!(isset($this->id_apartamento))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

                if (empty($this->id_apartamento)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            } 
            // Validamos que los campos enviados si existan

            if (!(isset($this->nro_apartamento) && isset($this->porcentaje_participacion) && isset($this->gas) && isset($this->agua) && isset($this->alquilado) && isset($this->propietario_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            // Validamos que los campos enviados no esten vacios

            if (empty($this->nro_apartamento) || empty($this->porcentaje_participacion) ||empty($this->gas) || empty($this->agua) || empty($this->alquilado) || empty($this->propietario_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            // Verificamos si los valores tienen los datos que deberian
            
            if(!(is_string($this->nro_apartamento)) || !(preg_match("/^[0-9\b]{1,2}$/",$this->nro_apartamento))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Nro de Apartamento' no posee un valor valido"];
            }
            if(!(is_string($this->porcentaje_participacion)) || !(preg_match("/^\d{1,2}(\.\d{1,2})?$/",$this->porcentaje_participacion))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Codigo' no posee un valor valido"];
            }
            if(!(is_numeric($this->gas))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Gas' no posee un valor valido"];
            }
            if(!(is_numeric($this->agua))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Agua' no posee un valor valido"];
            }
            if(!(is_numeric($this->alquilado))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Alquilado' no posee un valor valido"];
            }
            if(is_numeric($this->propietario_id)){
                if (!($this->validarClaveForanea("propietarios","id_propietario",$this->propietario_id,true))) {
                    return ["estatus"=>false,"mensaje"=>"El campo 'Propietario' no posee un valor valido"];
                }            
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El campo 'Propietario' no posee un valor valido"];
            }

            return ["estatus"=>true,"mensaje"=>"OK"];
        }

        private function validarClaveForanea($tabla,$nombreClave,$valor,$seguridad = false){
            if ($seguridad) {
                $this->cambiar_db_seguridad();
            }
            $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":valor", $valor);
            $conexion->execute();
            $result = $conexion->fetch(PDO::FETCH_ASSOC);

            if ($seguridad) {
                $this->cambiar_db_negocio();
            }
            return ($result)?true:false;
        }
    }
?>