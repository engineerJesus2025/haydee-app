<?php
    namespace haydee\modelo;
    use haydee\modelo\Conexion;
    use PDO;
    
    class Banco extends Conexion{
        // Atributos
        private $id_banco;
        private $nombre_banco;
        private $codigo;
        private $numero_cuenta;
        private $telefono_afiliado;
        private $cedula_afiliada;

        public function __construct(){
            parent::__construct();
        }
        
        // Metodos setter y getter
        public function set_id_banco($id_banco){
            $this->id_banco = $id_banco;
        }

        public function get_id_banco(){
            return $this->id_banco;
        }

        public function set_nombre_banco($nombre_banco){
            $this->nombre_banco = $nombre_banco;
        }

        public function get_nombre_banco(){
            return $this->nombre_banco;
        }

        public function set_codigo($codigo){
            $this->codigo = $codigo;
        }

        public function get_codigo(){
            return $this->codigo;
        }

        public function set_numero_cuenta($numero_cuenta){
            $this->numero_cuenta = $numero_cuenta;
        }

        public function get_numero_cuenta(){
            return $this->numero_cuenta;
        }

        public function set_telefono_afiliado($telefono_afiliado){
            $this->telefono_afiliado = $telefono_afiliado;
        }

        public function get_telefono_afiliado(){
            return $this->telefono_afiliado;
        }

        public function set_cedula_afiliada($cedula_afiliada){
            $this->cedula_afiliada = $cedula_afiliada;
        }
        
        public function get_cedula_afiliada(){
            return $this->cedula_afiliada;
        }

        // Metodos CRUD entre otros
        public function realizar_consulta($accion){
            switch ($accion) {
                case 'validar':
                    $respuesta = $this->verificar_banco();

                    if ($respuesta["resultado"]) {
                        if (isset($respuesta["datos"]["numero_cuenta"])) {
                            return ["estatus"=>true,"busqueda"=>"numero_cuenta"];
                        } else {
                            return ["estatus"=>false,"busqueda"=>"numero_cuenta"];
                        }
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la validación del banco"];
                    }

                case 'consultar':
                    $respuesta = $this->consultar();

                    if ($respuesta["resultado"]) {
                        $this->registrar_bitacora(CONSULTAR, GESTIONAR_BANCOS, "TODOS LOS BANCOS");
                        return $respuesta["datos"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                    }

                case 'consulta_especifica':
                    $respuesta = $this->consultar_banco();

                    if ($respuesta["resultado"]) {
                        return $respuesta["datos"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta de este banco"];
                    }

                case 'registrar':
                    // Validaciones
                    $validaciones = $this->validarDatos();
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->registrar_banco();

                    if ($respuesta) {
                        $id_ultimo = $this->lastId();
                        $this->set_id_banco($id_ultimo["datos"]["last_id"]);
                        $banco_alterado = $this->consultar_banco();
                        $this->registrar_bitacora(REGISTRAR, GESTIONAR_BANCOS, $banco_alterado["datos"]["nombre_banco"] . " (" . $banco_alterado["datos"]["codigo"] . ")");
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este banco"];
                    }

                case 'modificar':
                    // Validaciones
                    $validaciones = $this->validarDatos("editar");
                    if(!($validaciones["estatus"])){return $validaciones;} 

                    $respuesta = $this->editar_banco();

                    if ($respuesta["resultado"]) {
                        if ($respuesta["fila_afectada"] < 1) {
                            return ["estatus"=>false,"mensaje"=>"No se realizaron cambios en la edición de este banco"];
                        }

                        $banco_alterado = $this->consultar_banco();
                        $this->registrar_bitacora(MODIFICAR, GESTIONAR_BANCOS, $banco_alterado["datos"]["nombre_banco"] . " (" . $banco_alterado["datos"]["codigo"] . ")");
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este banco"];
                    }

                case 'eliminar':
                    // Validaciones
                    $validaciones = $this->validarDatos("eliminar");
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $banco_alterado = $this->consultar_banco();

                    $respuesta = $this->eliminar_banco();

                    if ($respuesta["resultado"]) {
                        if ($respuesta["fila_afectada"] < 1) {
                            return ["estatus"=>false,"mensaje"=>"No se elimino este banco"];
                        }

                        $this->registrar_bitacora(ELIMINAR, GESTIONAR_BANCOS, $banco_alterado["datos"]["nombre_banco"] . " (" . $banco_alterado["datos"]["codigo"] . ")");
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este banco"];
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

        private function verificar_banco(){  // Verifica si existe el banco para así ver si se registra o no.

            //$this->cambiar_db_seguridad(); // va documentada
            $sql = "SELECT * FROM bancos WHERE numero_cuenta = :numero_cuenta"; 
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":numero_cuenta", $this->numero_cuenta);
            $result =$conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio(); // va documentada
            return ["resultado"=>$result,"datos"=>$datos];
        }

        private function consultar(){

            $sql = "SELECT * FROM `bancos` ORDER BY id_banco";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            return ["resultado"=>$result,"datos"=>$datos];
        }

        private function consultar_banco(){

            $sql = "SELECT * FROM bancos WHERE id_banco = :id_banco";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_banco", $this->id_banco);
            $result = $conexion->execute();        
            $filas = $conexion->fetch(PDO::FETCH_ASSOC);

            if (!$filas || count($filas) === 0) {
                return ["estatus"=>false,"mensaje"=>"No se encontró el banco"];
            }

            return ["resultado"=>$result,"datos"=>$filas];
        }

        private function registrar_banco(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO bancos(nombre_banco,codigo,numero_cuenta,telefono_afiliado,cedula_afiliada) VALUES (:nombre_banco,:codigo,:numero_cuenta,:telefono_afiliado,:cedula_afiliada)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nombre_banco", $this->nombre_banco);
            $conexion->bindParam(":codigo", $this->codigo);
            $conexion->bindParam(":numero_cuenta", $this->numero_cuenta);
            $conexion->bindParam(":telefono_afiliado", $this->telefono_afiliado);
            $conexion->bindParam(":cedula_afiliada", $this->cedula_afiliada);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            return $result;
        }

        private function editar_banco(){
            //Validamos los datos obtenidos del controlador       

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE bancos SET nombre_banco=:nombre_banco,codigo=:codigo,numero_cuenta=:numero_cuenta,telefono_afiliado=:telefono_afiliado,cedula_afiliada=:cedula_afiliada WHERE id_banco=:id_banco";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_banco", $this->id_banco);
            $conexion->bindParam(":nombre_banco", $this->nombre_banco);
            $conexion->bindParam(":codigo", $this->codigo);
            $conexion->bindParam(":numero_cuenta", $this->numero_cuenta);
            $conexion->bindParam(":telefono_afiliado", $this->telefono_afiliado);
            $conexion->bindParam(":cedula_afiliada", $this->cedula_afiliada);

            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
        }

        private function eliminar_banco(){
            //Validamos los datos obtenidos del controlador
            
            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM bancos WHERE id_banco = :id_banco";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_banco", $this->id_banco);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            return ["resultado"=>$result,"fila_afectada"=>$conexion->rowCount()];
        }

        private function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_banco) as last_id FROM bancos";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);
            $this->cambiar_db_negocio();

            return ["resultado"=>$result,"datos"=>$datos];
        }

        private function validarDatos($consulta = "registrar"){   
            // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
            if ($consulta == "editar" || $consulta == "eliminar") {
                if (!(isset($this->id_banco))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

                if (empty($this->id_banco)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}
                
                if ($consulta == "eliminar") {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                }
            } 
            // Validamos que los campos enviados si existan

            if (!(isset($this->nombre_banco) && isset($this->codigo) && isset($this->numero_cuenta) && isset($this->telefono_afiliado) && isset($this->cedula_afiliada))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            // Validamos que los campos enviados no esten vacios

            if (empty($this->nombre_banco) || empty($this->codigo) ||empty($this->numero_cuenta) || empty($this->telefono_afiliado) || empty($this->cedula_afiliada)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            // Verificamos si los valores tienen los datos que deberian
            
            if(!(is_string($this->nombre_banco)) || !(preg_match("/^[A-Za-z \b]{3,30}$/",$this->nombre_banco))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Nombre del Banco' no posee un valor valido"];
            }
            if(!(is_numeric($this->codigo)) || !(preg_match("/^[0-9\b]{4}$/",$this->codigo))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Codigo' no posee un valor valido"];
            }
            if(!(is_numeric($this->numero_cuenta)) || !(preg_match("/^[0-9\b]{18,30}$/",$this->numero_cuenta))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Numero de Cuenta' no posee un valor valido"];
            }
            if(!(is_numeric($this->telefono_afiliado)) || !(preg_match("/^[0-9\b]{11}$/",$this->telefono_afiliado))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Telefono Afiliado' no posee un valor valido"];
            }
            if(!(is_string($this->cedula_afiliada)) || !(preg_match("/^[VEJG\b]{1}[0-9\b]{7,10}$/",$this->cedula_afiliada))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Documento Afiliado' no posee un valor valido"];
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