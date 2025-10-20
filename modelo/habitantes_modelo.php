<?php
    require_once("modelo/conexion.php");

    class Habitantes extends Conexion{
        private $id_habitante;
        private $nombre;
        private $apellido;
        private $cedula;
        private $telefono;
        private $correo;
        private $fecha_nacimiento;
        private $sexo;

        public function __construct(){
            parent::__construct();
        }

        // Metodos Setter y Getter
        public function set_id_habitante($id_habitante){
            $this->id_habitante = $id_habitante;
        }

        public function get_id_habitante(){
            return $this->id_habitante;
        }

        public function set_nombre($nombre){
            $this->nombre = $nombre;
        }

        public function get_nombre(){
            return $this->nombre;
        }

        public function set_apellido($apellido){
            $this->apellido = $apellido;
        }

        public function get_apellido(){
            return $this->apellido;
        }

        public function set_cedula($cedula){
            $this->cedula = $cedula;
        }

        public function get_cedula(){
            return $this->cedula;
        }

        public function set_telefono($telefono){
            $this->telefono = $telefono;
        }

        public function get_telefono(){
            return $this->telefono;
        }

        public function set_correo($correo){
            $this->correo = $correo;
        }

        public function get_correo(){
            return $this->correo;
        }

        public function set_fecha_nacimiento($fecha_nacimiento){
            $this->fecha_nacimiento = $fecha_nacimiento;
        }

        public function get_fecha_nacimiento(){
            return $this->fecha_nacimiento;
        }

        public function set_sexo($sexo){
            $this->sexo = $sexo;
        }

        public function get_sexo(){
            return $this->sexo;
        }

        // Metodos CRUD
        public function realizar_consulta($accion){
            switch($accion){
                case 'validar':
                    $respuesta = $this->verificar_habitante();

                    if ($respuesta["resultado"]) {
                        if (isset($respuesta["datos"]["cedula"])) {
                            return ["estatus"=>true,"busqueda"=>"cedula"];
                        } else {
                            return ["estatus"=>false,"busqueda"=>"cedula"];
                        }
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar validar el habitante"];
                    }
                
                case 'consultar':
                    $respuesta = $this->consultar();

                    if ($respuesta["resultado"]) {
                        $this->registrar_bitacora(CONSULTAR, GESTIONAR_HABITANTES, "TODAS LOS HABITANTES");
                        return $respuesta["datos"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar consultar los habitantes de este apartamento"];
                    }

                case 'registrar':
                    // Validaciones
                    $validaciones = $this->validarDatos();
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->registrar_habitante();

                    if ($respuesta) {
                        $id_ultimo = $this->lastId();
                        $this->set_id_habitante($id_ultimo["datos"]["last_id"]);
                        //$habitante_alterado = $this->consultar_habitante();
                        //var_dump("Habitante registrado:", $habitante_alterado);
                        // Por Razones raras del destino la vaina da error con la forma traducional y toco dejarlo así
                        $this->registrar_bitacora(REGISTRAR, GESTIONAR_HABITANTES, $this->cedula . " (" . $this->nombre . ")");
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar a este habitante"];
                    }

                case 'consulta_especifica':
                    $respuesta = $this->consultar_habitante();

                    if ($respuesta["resultado"]) {
                        return $respuesta["datos"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar consultar a este habitante"];
                    }

                case 'modificar':
                    // Validaciones
                    $validaciones = $this->validarDatos("editar");
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->editar_habitante();

                    if ($respuesta["resultado"]) {                       
                       // Aqui también dice eso pero por alguna razon no falla, solo pasa en el registrar
                        //$habitante_alterado = $this->consultar_habitante();
                        $this->registrar_bitacora(MODIFICAR, GESTIONAR_HABITANTES, $this->cedula . " (" . $this->nombre . ")");
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar modificar a este habitante"];
                    }

                case 'eliminar':
                    // Validaciones
                    $validaciones = $this->validarDatos("eliminar");
                    if(!($validaciones["estatus"])){return $validaciones;}

                    //$habitante_alterado = $this->consultar_habitante();

                    $respuesta = $this->eliminar_habitante();

                    if ($respuesta["resultado"]) {
                        if ($respuesta["fila_afectada"] < 1) {
                            return ["estatus"=>false,"mensaje"=>"No se pudo eliminar a este habitante"];
                        }
                        // Aqui también dice eso pero por alguna razon no falla, solo pasa en el registrar
                        $this->registrar_bitacora(ELIMINAR, GESTIONAR_HABITANTES, $this->cedula . " (" . $this->nombre . ")");
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar a este habitante"];
                    }

                case 'lastId':
                    $respuesta = $this->lastId();

                    if ($respuesta["resultado"]) {
                        return $respuesta["datos"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar obtener el ultimo id del habitante"];
                    }

                default:
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error en el proceso"];
                    break;

            }
        }

        private function verificar_habitante(){  

            //$this->cambiar_db_seguridad();

            $sql = "SELECT * FROM habitantes WHERE cedula = :cedula"; 

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":cedula", $this->cedula);
            $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();
        
            return ["resultado"=>true,"datos"=>$datos];
        }

        private function consultar(){

            //$this->cambiar_db_seguridad();

            $sql = "SELECT
                h.id_habitante,
                h.nombre,
                h.apellido,
                h.cedula,
                h.telefono,
                h.correo,
                h.fecha_nacimiento,
                h.sexo,
                a.nro_apartamento,
                ha.tipo_vinculo
            FROM habitantes h
            INNER JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
            INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
            ORDER BY h.id_habitante";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            $this->cambiar_db_negocio();

            return ["resultado"=>$result,"datos"=>$datos];
        }

        private function consultar_habitante(){
            //var_dump("ID recibido:", $this->id_habitantes);

            //$this->cambiar_db_seguridad();
 
            $sql = "SELECT 
                h.id_habitante,
                h.nombre,
                h.apellido,
                h.cedula,
                h.telefono,
                h.correo,
                h.fecha_nacimiento,
                h.sexo,
                a.nro_apartamento AS apartamento,
                ha.tipo_vinculo,
                ha.apartamento_id
            FROM habitantes h
            LEFT JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
            LEFT JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
            WHERE h.id_habitante = :id_habitante";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_habitante", $this->id_habitante);
            $result = $conexion->execute();        
            $filas = $conexion->fetch(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();
            //var_dump("Resultado SQL:", $filas);

            if (!$filas || count($filas) === 0) {
                return ["resultado"=>false,"datos"=>null];
            } 
                
            return ["resultado"=>$result,"datos"=>$filas];
        }

        private function registrar_habitante(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO habitantes(nombre,apellido,cedula,telefono,correo,fecha_nacimiento,sexo) VALUES (:nombre,:apellido,:cedula,:telefono,:correo,:fecha_nacimiento,:sexo)";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":nombre", $this->nombre);
            $conexion->bindParam(":apellido", $this->apellido);
            $conexion->bindParam(":cedula", $this->cedula);
            $conexion->bindParam(":telefono", $this->telefono);
            $conexion->bindParam(":correo", $this->correo);
            $conexion->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
            $conexion->bindParam(":sexo", $this->sexo);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            return $result;
        }

        private function editar_habitante(){
            //Validamos los datos obtenidos del controlador     
            //$this->cambiar_db_seguridad();

            $sql = "UPDATE habitantes SET nombre=:nombre,apellido=:apellido,cedula=:cedula,telefono=:telefono,correo=:correo,fecha_nacimiento=:fecha_nacimiento,sexo=:sexo WHERE id_habitante=:id_habitante";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_habitante", $this->id_habitante);
            $conexion->bindParam(":nombre", $this->nombre);
            $conexion->bindParam(":apellido", $this->apellido);
            $conexion->bindParam(":cedula", $this->cedula);
            $conexion->bindParam(":telefono", $this->telefono);
            $conexion->bindParam(":correo", $this->correo);
            $conexion->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
            $conexion->bindParam(":sexo", $this->sexo);

            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            // $this->cambiar_db_negocio();        
            
            return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
        }

        private function eliminar_habitante(){
            //Validamos los datos obtenidos del controlador
            //$habitante_alterado = $this->consultar_habitante();

            //$this->cambiar_db_seguridad();

            $sql = "DELETE FROM habitantes WHERE id_habitante = :id_habitante";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_habitante", $this->id_habitante);
            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            //$this->cambiar_db_negocio();
            
            return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
        }

        private function lastId(){
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_habitante) as last_id FROM habitantes";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);
            // $this->cambiar_db_negocio();

            return ["resultado"=>$result,"datos"=>$datos]; 
        }

        private function validarDatos($consulta = "registrar"){   
            // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
            if ($consulta == "editar" || $consulta == "eliminar") {
                if (!(isset($this->id_habitante))) {return ["estatus"=>false,"mensaje"=>"El id del Habitante requerido no se recibio correctamente"];}

                if (empty($this->id_habitante)) {return ["estatus"=>false,"mensaje"=>"El id del Habitante requerido esta vacio"];}

                if(is_numeric($this->id_habitante)){
                    if (!($this->validarClaveForanea("habitantes","id_habitante",$this->id_habitante))) {
                        return ["estatus"=>false,"mensaje"=>"El habitante seleccionado no existe"];
                    }
                    if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
                }
                else{return ["estatus"=>false,"mensaje"=>"El id del Habitante tiene debe ser un valor numerico entero"];}
            } 
            // Validamos que los campos enviados si existan

            if (!(isset($this->cedula) && isset($this->nombre) && isset($this->apellido) && isset($this->fecha_nacimiento) && isset($this->sexo) && isset($this->telefono) && isset($this->correo))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            // Validamos que los campos enviados no esten vacios

            if (empty($this->cedula) || empty($this->nombre) ||empty($this->apellido) || empty($this->fecha_nacimiento) || empty($this->sexo) || empty($this->telefono) || empty($this->correo)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            // Verificamos si los valores tienen los datos que deberian
            
            if(!(is_string($this->cedula)) || !(preg_match("/^[0-9\b]{7,8}$/",$this->cedula))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Cedula' no posee un valor valido"];
            }
            if(!(is_string($this->nombre)) || !(preg_match("/^[A-Za-z \b]{3,30}$/",$this->nombre))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Nombre' no posee un valor valido"];
            }
            if(!(is_string($this->apellido)) || !(preg_match("/^[A-Za-z \b]{3,30}$/",$this->apellido))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Apellido' no posee un valor valido"];
            }
            if(!(is_string($this->sexo))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Sexo' no posee un valor valido"];
            }
            if(!(is_numeric($this->telefono)) || !(preg_match("/^[0-9\b]{11}$/",$this->telefono))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Telefono' no posee un valor valido"];
            }
            if(!(is_string($this->correo)) || !(preg_match("/^[-A-Za-z0-9_.]{3,35}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/",$this->correo))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Correo' no posee un valor valido"];        
            }
            if(!(is_string($this->fecha_nacimiento))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Fecha de Nacimiento' no posee un valor valido"];
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



        // Metodo para el reporte estadisitico de habitantes (francisco)
         public function obtenerDatosHabitantes($rango_edades, $edad_minima, $edad_maxima, $tipo_residente, $servicios)
    {
        // 1. Consulta base que une las tablas necesarias
        $sql = "SELECT 
                    h.sexo, 
                    a.alquilado, 
                    TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) AS edad
                FROM 
                    habitantes h
                JOIN 
                    habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                JOIN 
                    apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE 1=1"; // Permite añadir cláusulas AND fácilmente

        // 2. Añadir filtros dinámicamente a la consulta
        
        // ---- Filtro por Rango de Edades ----
        if ($rango_edades != 'todos') {
            switch ($rango_edades) {
                case 'jovenes':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 35";
                    break;
                case 'adultos':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) BETWEEN 36 AND 59";
                    break;
                case 'mayores':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) >= 60";
                    break;
                case 'personalizado':
                    // Solo añade la condición si las edades son válidas
                    if (!empty($edad_minima) && !empty($edad_maxima)) {
                        $sql .= " AND TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) BETWEEN :edad_minima AND :edad_maxima";
                    }
                    break;
            }
        }

        // ---- Filtro por Tipo de Residente ----
        if ($tipo_residente == 'propietarios') {
            $sql .= " AND a.alquilado = 0"; // 0 para false (no está alquilado)
        } elseif ($tipo_residente == 'arrendatarios') {
            $sql .= " AND a.alquilado = 1"; // 1 para true (está alquilado)
        }
        
        // ---- Filtro por Servicios ----
        if (is_array($servicios)) {
            if (in_array('agua', $servicios)) {
                $sql .= " AND a.agua = 1";
            }
            if (in_array('gas', $servicios)) {
                $sql .= " AND a.gas = 1";
            }
        }

        // 3. Preparar la conexión y ejecutar
        try {
            $conexion = $this->get_conex()->prepare($sql);

            // 4. Vincular parámetros de forma segura (solo si es necesario)
            if ($rango_edades == 'personalizado' && !empty($edad_minima) && !empty($edad_maxima)) {
                $conexion->bindParam(":edad_minima", $edad_minima, PDO::PARAM_INT);
                $conexion->bindParam(":edad_maxima", $edad_maxima, PDO::PARAM_INT);
            }

            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                return ["estatus" => true, "mensaje" => $datos];
            } else {
                return ["estatus" => false, "mensaje" => "Error al ejecutar la consulta."];
            }

        } catch (PDOException $e) {
            // Capturar cualquier error de la base de datos
            return ["estatus" => false, "mensaje" => "Error de base de datos: " . $e->getMessage()];
        }
    }

    public function consultar_personas_solvencia(){
        $sql = "SELECT * FROM habitantes INNER JOIN habitantes_apartamentos ON habitantes.id_habitante = habitantes_apartamentos.habitante_id INNER JOIN apartamentos ON apartamentos.id_apartamento = habitantes_apartamentos.apartamento_id WHERE apartamentos.id_apartamento IN 
            (SELECT apartamentos.id_apartamento FROM mensualidad INNER JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento
            WHERE (SELECT SUM(mensualidad.monto) FROM mensualidad WHERE mensualidad.apartamento_id = apartamentos.id_apartamento) <= (SELECT SUM(detalles_pagos.monto) FROM detalles_pagos INNER JOIN pagos_mensualidad ON pagos_mensualidad.detalle_pago_id = detalles_pagos.id_detalle_pago INNER JOIN mensualidad ON mensualidad.apartamento_id = apartamentos.id_apartamento INNER JOIN mensualidad mensualidad_aisgnada ON mensualidad.id_mensualidad = pagos_mensualidad.mensualidad_id WHERE mensualidad_aisgnada.apartamento_id = apartamentos.id_apartamento))";
        $conexion = $this->get_conex()->prepare($sql);        
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if($result == true){
            return $datos;
        }else{
            return ["estatus"=>false, "mensaje"=>"Error al consultar los propietarios"];
        }
    }

    public function consultar_propietarios(){
        $sql = "SELECT * FROM habitantes INNER JOIN habitantes_apartamentos ON habitantes.id_habitante = habitantes_apartamentos.habitante_id INNER JOIN apartamentos ON apartamentos.id_apartamento = habitantes_apartamentos.apartamento_id";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();

        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if($result == true){
            return $datos;
        }else{
            return ["estatus"=>false, "mensaje"=>"Error al consultar los propietarios"];
        }
    }

    }

?>