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
    }   
?>