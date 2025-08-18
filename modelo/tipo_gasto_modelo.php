<?php
    require_once("modelo/conexion.php");

    class Tipo_gasto extends Conexion{
        // Atributos
        private $id_tipo_gasto;
        private $nombre_tipo_gasto;

        public function __construct(){
            parent::__construct();
        }
        
        // Metodos setter y getter
        public function set_id_tipo_gasto($id_tipo_gasto){
            $this->id_tipo_gasto = $id_tipo_gasto;
        }

        public function get_id_tipo_gasto(){
            return $this->id_tipo_gasto;
        }

        public function set_nombre_tipo_gasto($nombre_tipo_gasto){
            $this->nombre_tipo_gasto = $nombre_tipo_gasto;
        }

        public function get_nombre_tipo_gasto(){
            return $this->nombre_tipo_gasto;
        }


        // Metodos CRUD entre otro

        public function consultar($externa = false){

            //$this->cambiar_db_seguridad(); // Esta va documentada

            // 1) Sentencia SQL de toda la vida.
            $sql = "SELECT * FROM `tipo_gasto` ORDER BY id_tipo_gasto";

            // 2) Se prepara una consulta a la base de datos.
            $conexion = $this->get_conex()->prepare($sql);

            // 3) Se ejecuta la consulta SQL en la base de datos.
            $result = $conexion->execute();
            
            // 4)Se obtiene la primera fila del resultado como un arreglo asociativo.
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            $this->cambiar_db_negocio();

            if ($result == true) {
                if (!$externa) {
                    $this->registrar_bitacora(CONSULTAR, GESTIONAR_TIPO_GASTO, "TODOS LOS TIPOS DE GASTO");//registra cuando se entra al modulo de tipos de gasto
                }
                
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function consultar_tipo_gasto(){

            //$this->cambiar_db_seguridad();

            // 1) Sentencia SQL de toda la vida
            $sql = "SELECT * FROM tipo_gasto WHERE id_tipo_gasto = :id_tipo_gasto";

            // 2) Se prepara la conexion
            $conexion = $this->get_conex()->prepare($sql);

            // 3) Se manda el tipo de gasto que queremos consultar
            $conexion->bindParam(":id_tipo_gasto", $this->id_tipo_gasto);

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

        public function registrar_tipo_gasto(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO tipo_gasto(nombre_tipo_gasto) VALUES (:nombre_tipo_gasto)";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nombre_tipo_gasto", $this->nombre_tipo_gasto);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();//obtenemos el ultimo id
                $this->set_id_tipo_gasto($id_ultimo["mensaje"]);
                $tipo_gasto_alterado = $this->consultar_tipo_gasto();//lo consultamos

                $this->registrar_bitacora(REGISTRAR, GESTIONAR_TIPO_GASTO, $tipo_gasto_alterado["nombre_tipo_gasto"]);//registramos en la bitacora

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este banco"];
            }
        }

        public function editar_tipo_gasto(){
            //Validamos los datos obtenidos del controlador     

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE tipo_gasto SET nombre_tipo_gasto=:nombre_tipo_gasto WHERE id_tipo_gasto=:id_tipo_gasto";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_tipo_gasto", $this->id_tipo_gasto);
            $conexion->bindParam(":nombre_tipo_gasto", $this->nombre_tipo_gasto);

            $result = $conexion->execute();

            //$this->cambiar_db_negocio();        
            
            if ($result) {
                $tipo_gasto_alterado = $this->consultar_tipo_gasto();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_TIPO_GASTO, $tipo_gasto_alterado["nombre_tipo_gasto"]);

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este tipo de gasto"];
            }
        }

        public function eliminar_tipo_gasto(){

            $tipo_gasto_alterado = $this->consultar_tipo_gasto();


            $sql = "DELETE FROM tipo_gasto WHERE id_tipo_gasto = :id_tipo_gasto";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_tipo_gasto", $this->id_tipo_gasto);
            $result = $conexion->execute();

            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_TIPO_GASTO, $tipo_gasto_alterado["nombre_tipo_gasto"]);

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este tipo de gasto"];
            }
        }

        public function lastId(){
            $sql = "SELECT MAX(id_tipo_gasto) as last_id FROM tipo_gasto";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return ["estatus"=>true,"mensaje"=>$datos["last_id"]];
            } else {
                return ["estatus"=>false,"mensaje"=>"Error en la consulta"];
            } 
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