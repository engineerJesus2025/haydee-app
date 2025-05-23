<?php
    require_once("modelo/conexion.php");

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
        public function verificar_banco(){  // Verifica si existe el banco para así ver si se registra o no.

            //$this->cambiar_db_seguridad();

            // 1) Se guarda una sentencia sql en una variable (la variable sql).
            $sql = "SELECT * FROM bancos WHERE numero_cuenta = :numero_cuenta"; 

            /* 
                2) Se prepara una consulta a la base de datos. 
                
                "get_conex" es el metodo que esta en el modelo, y "prepare" es una
                función de php que se usa para preparar una consulta SQL para su
                ejecución posterior, asi que colocamos la sentencia de arriba dentro
                de la función solo que esta vez colocamos la variable en su caso.
            */
            $conexion = $this->get_conex()->prepare($sql);

            /* 
                3) Luego asociamos el valor del atributo ($this->numero_cuenta) al
                parametro ":numero_cuenta" de la consulta.

                bindParam: es una función que asocia una variable PHP con un marcador 
                (placeholder) en una consulta SQL, para enviarle datos de forma segura.
            */
            $conexion->bindParam(":numero_cuenta", $this->numero_cuenta);

            /*
                4) Se ejecuta la consulta SQL en la base de datos.

                execute: método que se utiliza para ejecutar una sentencia SQL 
                previamente preparada.
            */
            $conexion->execute();

            /*
                5) Se obtiene la primera fila del resultado como un arreglo asociativo.

                fetch: Se usa para obtener una fila del resultado de una consulta SQL.
                NOTA: Después de ejecutar una consulta, fetch() te da una fila de datos).

                PDO: Es una forma moderna y segura de conectarse a bases de datos en PHP.
                NOTA: Te permite preparar y ejecutar consultas fácilmente, recordar que
                "PDO" es una clase de PHP.

                FETCH_ASSOC: Es una opción que le dices a fetch() para que te devuelva 
                los datos como un arreglo asociativo (clave => valor).
                NOTA: Así puedes acceder a los datos por el nombre de las columnas, como 
                $fila["correo"], recordar que esta opción es una constante de la clase
                "PDO" e indica como queremos recibir los datos.

                "::": Los dos puntos significan "acceder a algo estático o constante dentro 
                de una clase". 
            */
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();
            /* 
                La función "isset" verifica si una variable existe o no.

                NOTA: Cabe destacar que "$datos" tiene el valor de la base de datos,
                mientras que "$r["busqueda"]" simplemente esta escribiendo una etiqueta.
            */
            if (isset($datos["numero_cuenta"])) {  // "$datos" es un arreglo asociativo, por ende sería: "numero_cuenta" => "valor", "nombre_banco" => "valor". 
                $r["estatus"] = true; // Se crea esta variable para notificar que existe ese número de cuenta en la base de datos.
                $r["busqueda"] = "numero_cuenta"; // Esto solo esta para verificar qué se estaba buscando, en este caso un número de cuenta.
                return $r; // Aquí no estamos retornando valores de la base de datos, solamente sí existe o no dicho número de cuenta.
            } else { // lo mismo pero su contra parte
                $r["estatus"] = false;
                $r["busqueda"] = "numero_cuenta";
                return $r;
            }
        }

        public function consultar(){

            //$this->cambiar_db_seguridad();

            // 1) Sentencia SQL de toda la vida.
            $sql = "SELECT * FROM `bancos` ORDER BY id_banco";

            // 2) Se prepara una consulta a la base de datos.
            $conexion = $this->get_conex()->prepare($sql);

            // 3) Se ejecuta la consulta SQL en la base de datos.
            $result = $conexion->execute();
            
            // 4)Se obtiene la primera fila del resultado como un arreglo asociativo.
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            $this->cambiar_db_negocio();

            if ($result == true) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_BANCOS, "TODOS LOS BANCOS");//registra cuando se entra al modulo de bancos

                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function consultar_banco(){

            //$this->cambiar_db_seguridad();

            // 1) Sentencia SQL de toda la vida
            $sql = "SELECT * FROM bancos WHERE id_banco = :id_banco";

            // 2) Se prepara la conexion
            $conexion = $this->get_conex()->prepare($sql);

            // 3) Se manda el banco que queremos consultar
            $conexion->bindParam(":id_banco", $this->id_banco);

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

        public function registrar_banco(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO bancos(nombre_banco,codigo,numero_cuenta,telefono_afiliado,cedula_afiliada) VALUES (:nombre_banco,:codigo,:numero_cuenta,:telefono_afiliado,:cedula_afiliada)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nombre_banco", $this->nombre_banco);
            $conexion->bindParam(":codigo", $this->codigo);
            $conexion->bindParam(":numero_cuenta", $this->numero_cuenta);
            $conexion->bindParam(":telefono_afiliado", $this->telefono_afiliado);
            $conexion->bindParam(":cedula_afiliada", $this->cedula_afiliada);
            $result = $conexion->execute();

            $this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastId();//obtenemos el ultimo id
                $this->set_id_banco($id_ultimo["mensaje"]);
                $banco_alterado = $this->consultar_banco();//lo consultamos

                $this->registrar_bitacora(REGISTRAR, GESTIONAR_BANCOS, $banco_alterado["nombre_banco"] . " (" . $banco_alterado["codigo"] . ")");//registramos en la bitacora

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este banco"];
            }
        }

        public function editar_banco(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

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

            $this->cambiar_db_negocio();        
            
            if ($result) {
                $banco_alterado = $this->consultar_banco();
                $this->registrar_bitacora(MODIFICAR, GESTIONAR_BANCOS, $banco_alterado["nombre_banco"] . " (" . $banco_alterado["codigo"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este banco"];
            }
        }

        public function eliminar_banco(){
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            $banco_alterado = $this->consultar_banco();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM bancos WHERE id_banco = :id_banco";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_banco", $this->id_banco);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();
            
            if ($result) {
                $this->registrar_bitacora(ELIMINAR, GESTIONAR_BANCOS, $banco_alterado["nombre_banco"] . " (" . $banco_alterado["codigo"] . ")");

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este banco"];
            }
        }

        public function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_banco) as last_id FROM bancos";
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
                if (!(isset($this->id_banco))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

                if (empty($this->id_banco)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

                if(is_numeric($this->id_banco)){
                    if (!($this->validarClaveForanea("bancos","id_banco",$this->id_banco,true))) {
                        return ["estatus"=>false,"mensaje"=>"El banco seleccionado no existe"];
                    }
                    if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
                }
                else{return ["estatus"=>false,"mensaje"=>"El id del Banco debe ser un valor numerico entero"];}
            } 
            // Validamos que los campos enviados si existan

            if (!(isset($this->nombre_banco) && isset($this->codigo) && isset($this->numero_cuenta) && isset($this->telefono_afiliado) && isset($this->cedula_afiliada))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            // Validamos que los campos enviados no esten vacios

            if (empty($this->nombre_banco) || empty($this->codigo) ||empty($this->numero_cuenta) || empty($this->telefono_afiliado) || empty($this->cedula_afiliada)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            // Verificamos si los valores tienen los datos que deberian
            
            if(!(is_string($this->nombre_banco)) || !(preg_match("/^[A-Za-z \b]{3,30}$/",$this->nombre_banco))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Nombre del Banco' no posee un valor valido"];
            }
            if(!(is_string($this->codigo)) || !(preg_match("/^[0-9\b]{4}$/",$this->codigo))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Codigo' no posee un valor valido"];
            }
            if(!(is_string($this->numero_cuenta)) || !(preg_match("/^[0-9\b]{18,30}$/",$this->numero_cuenta))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Numero de Cuenta' no posee un valor valido"];
            }
            if(!(is_string($this->telefono_afiliado)) || !(preg_match("/^[0-9\b]{11}$/",$this->telefono_afiliado))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Telefono Afiliado' no posee un valor valido"];
            }
            if(!(is_string($this->cedula_afiliada)) || !(preg_match("/^[0-9\b]{7,8}$/",$this->cedula_afiliada))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Cedula Afiliada' no posee un valor valido"];
            }

            if(is_numeric($this->id_banco)){
                if (!($this->validarClaveForanea("bancos","id_banco",$this->id_banco,true))) {
                    return ["estatus"=>false,"mensaje"=>"El banco no posee un valor valido"];
                }            
            }
            else{
                return ["estatus"=>false,"mensaje"=>"El banco no posee un valor valido"];
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