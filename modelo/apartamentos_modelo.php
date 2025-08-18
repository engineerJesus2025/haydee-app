<?php
    require_once("modelo/conexion.php");

    class Apartamento extends Conexion{
        private $id_apartamento;
        private $nro_apartamento;
        private $porcentaje_participacion;
        private $gas;
        private $agua;
        private $alquilado;

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

        // Metodos CRUD
        public function verificar_apartamento(){
            $sql = "SELECT * FROM apartamentos WHERE nro_apartamento = :nro_apartamento";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nro_apartamento", $this->nro_apartamento);
            $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);
            if (isset($datos["nro_apartamento"])) { 
                $r["estatus"] = true;
                $r["busqueda"] = "nro_apartamento";
                return $r;
            } else {
                $r["estatus"] = false;
                $r["busqueda"] = "nro_apartamento";
                return $r;
            }
        }

        public function consultar_propietario($correo) {
            $sql = "SELECT 
                        a.id_apartamento,
                        a.nro_apartamento,
                        a.porcentaje_participacion,
                        a.gas,
                        a.agua,
                        a.alquilado,
                        ha.tipo_vinculo,
                        h.nombre,
                        h.apellido,
                        h.correo
                    FROM habitantes h
                    JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                    JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                    WHERE h.correo = :correo";

            $stmt = $this->get_conex()->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        public function consultar_apartamentos_mensualidad(){
            
            $sql = "SELECT * FROM apartamentos ORDER BY id_apartamento";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($result == true) {                
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

        public function consultar_detalles(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT 
                a.id_apartamento,
                h.*,
                ha.tipo_vinculo
            FROM apartamentos a
            LEFT JOIN habitantes_apartamentos ha ON ha.apartamento_id = a.id_apartamento
            LEFT JOIN habitantes h ON ha.habitante_id = h.id_habitante
            WHERE a.id_apartamento = :id_apartamento";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_apartamento", $this->id_apartamento);
            $result = $conexion->execute();        
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            $this->cambiar_db_negocio();

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function registrar_apartamento(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            $validaciones = $this->validarDatos();
            if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO apartamentos(nro_apartamento,porcentaje_participacion,gas,agua,alquilado) VALUES (:nro_apartamento,:porcentaje_participacion,:gas,:agua,:alquilado)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nro_apartamento", $this->nro_apartamento);
            $conexion->bindParam(":porcentaje_participacion", $this->porcentaje_participacion);
            $conexion->bindParam(":gas", $this->gas);
            $conexion->bindParam(":agua", $this->agua);
            $conexion->bindParam(":alquilado", $this->alquilado);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();
                $this->set_id_apartamento($id_ultimo["mensaje"]);
                $apartamento_alterado = $this->consultar_apartamento();

                $this->registrar_bitacora(REGISTRAR, GESTIONAR_APARTAMENTOS, $apartamento_alterado["nro_apartamento"]);

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este apartamento"];
            }
        }

        public function editar_apartamento(){
            //Validamos los datos obtenidos del controlador
            $validaciones = $this->validarDatos("editar");
            if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE apartamentos SET nro_apartamento=:nro_apartamento,porcentaje_participacion=:porcentaje_participacion,gas=:gas,agua=:agua,alquilado=:alquilado WHERE id_apartamento=:id_apartamento";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_apartamento", $this->id_apartamento);
            $conexion->bindParam(":nro_apartamento", $this->nro_apartamento);
            $conexion->bindParam(":porcentaje_participacion", $this->porcentaje_participacion);
            $conexion->bindParam(":gas", $this->gas);
            $conexion->bindParam(":agua", $this->agua);
            $conexion->bindParam(":alquilado", $this->alquilado);

            $result = $conexion->execute();

            $this->cambiar_db_negocio();        
            
            if ($result) {
                $apartamento_alterado = $this->consultar_apartamento();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_APARTAMENTOS, $apartamento_alterado["nro_apartamento"]. " (".$apartamento_alterado["nro_apartamento"].")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este apartamento"];
            }
        }

        public function eliminar_apartamento(){
            //Validamos los datos obtenidos del controlador
            $validaciones = $this->validarDatos("eliminar");
            if(!($validaciones["estatus"])){return $validaciones;}
            
            $apartamento_alterado = $this->consultar_apartamento();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM apartamentos WHERE id_apartamento = :id_apartamento";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_apartamento", $this->id_apartamento);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_APARTAMENTOS, $apartamento_alterado["nro_apartamento"]);

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
                if (!(isset($this->id_apartamento))) {return ["estatus"=>false,"mensaje"=>"El id del Apartamento requerido no se recibio correctamente"];}

                if (empty($this->id_apartamento)) {return ["estatus"=>false,"mensaje"=>"El id del Apartamento requerido esta vacio"];}

                if(is_numeric($this->id_apartamento)){
                    if (!($this->validarClaveForanea("apartamentos","id_apartamento",$this->id_apartamento))) {
                        return ["estatus"=>false,"mensaje"=>"El apartamento seleccionado no existe"];
                    }
                    if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
                }
                else{return ["estatus"=>false,"mensaje"=>"El id del Apartamento tiene debe ser un valor numerico entero"];}
            } 
            // Validamos que los campos enviados si existan

            if (!(isset($this->nro_apartamento) && isset($this->porcentaje_participacion) && isset($this->gas) && isset($this->agua) && isset($this->alquilado))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            // Validamos que los campos enviados no esten vacios

            if (empty($this->nro_apartamento) || empty($this->porcentaje_participacion) || empty($this->gas) || empty($this->agua) || empty($this->alquilado)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            // Verificamos si los valores tienen los datos que deberian
            
            if(!(is_string($this->nro_apartamento)) || !(preg_match("/^[0-9\-\b]{1,3}$/",$this->nro_apartamento))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Nro de Apartamento' no posee un valor valido"];
            }
            if(!(is_numeric($this->porcentaje_participacion)) || !(preg_match("/^\d{1,2}(\.\d{1,2})?$/",$this->porcentaje_participacion))){
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