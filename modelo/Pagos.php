<?php    
    namespace haydee\modelo;
    use haydee\modelo\Conexion;
    use PDO;
    
    class Pagos extends Conexion{
        private $id_pago;
        private $monto;
        private $estado;
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

        public function set_monto($monto){
            $this->monto = $monto;
        }

        public function get_monto(){
            return $this->monto;
        }

        public function set_estado($estado){
            $this->estado = $estado;
        }

        public function get_estado(){
            return $this->estado;
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
        public function realizar_consulta($accion,$parametros = []){
            switch($accion){
                case 'consultar':
                    $respuesta = $this->consultar();

                    if ($respuesta["resultado"]) {
                        $this->registrar_bitacora(CONSULTAR, GESTIONAR_PAGOS, "TODOS LOS PAGOS");
                        return $respuesta["datos"];
                    } 
                    else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                    }

                case 'registrar':
                    $validaciones = $this->validarDatos();
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->registrar_pago();

                    if ($respuesta) {
                        $id_ultimo = $this->lastId();//obtenemos el ultimo id
                        $this->set_id_pago($id_ultimo["datos"]["last_id"]);
                        $pago_alterado = $this->consultar_pago();//lo consultamos

                        $this->registrar_bitacora(REGISTRAR, GESTIONAR_PAGOS, $pago_alterado["datos"]["monto_pago"] . " (" . $pago_alterado["datos"]["observacion"] . ")");//registramos en la bitacora
                        //$this->registrar_notificacion();
                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este pago"];
                    }
                
                case 'consulta_especifica':

                    $respuesta = $this->consultar_pago();

                    if ($respuesta["resultado"]){
                        return $respuesta["datos"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"No se encontró el pago"];
                    }

                case 'modificar':
                    $validaciones = $this->validarDatos("editar");
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $respuesta = $this->editar_pago();

                    if ($respuesta["resultado"]){
                        // if ($respuesta["fila_afectada"] < 1){
                        //     return ["estatus"=>false,"mensaje"=>"No se modificó ningún registro"];
                        // }

                        $pago_alterado = $this->consultar_pago();
                        $this->registrar_bitacora(MODIFICAR, GESTIONAR_PAGOS, $pago_alterado["datos"]["monto_pago"] . " (" . $pago_alterado["datos"]["observacion"] . ")");

                        return ["estatus"=>true,"mensaje"=>"OK"];
                    }else{
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar los datos del pago"];
                    }
                
                case 'eliminar':
                    $validaciones = $this->validarDatos("eliminar");
                    if(!($validaciones["estatus"])){return $validaciones;}

                    $pago_alterado = $this->consultar_pago();

                    $respuesta = $this->eliminar_pago();

                    if ($respuesta["resultado"]){ 

                        if ($respuesta["fila_afectada"] < 1){
                            return ["estatus"=>false,"mensaje"=>"No se eliminó ningún registro"];
                        }
                        $this->registrar_bitacora(ELIMINAR, GESTIONAR_PAGOS, $pago_alterado["datos"]["monto_pago"] . " (" . $pago_alterado["datos"]["observacion"] . ")");

                        return ["estatus"=>true,"mensaje"=>"OK"];
                    } else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este pago"];
                    }
                
                case 'lastId':
                    $respuesta = $this->lastId();
                    
                    if ($respuesta["resultado"]) {
                        return $respuesta["datos"];
                    } 
                    else {
                        return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                    }

                case 'validar_clave_foranea':
                $respuesta = $this->validarClaveForanea($parametros['tabla'],$parametros['nombre_clave'],$parametros['valor']);

                $this->cambiar_db_negocio();

                return $respuesta;

                default:
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error en el proceso"];
                    break;
            }
        }

        public function registrar_notificacion() {
            $this->cambiar_db_seguridad();
            $titulo = "Pagos";
            $descripcion = "Se ha registrado un nuevo pago en el modulo, por favor verifique el registro.";
            $fecha = date('Y-m-d'); 
            $activo = "0";

            // Obtener todos los usuarios con rol de propietario (rol_id = 2)
            $sql_usuarios = "SELECT id_usuario FROM usuarios WHERE rol_id = 3";
            $conexion_usuarios = $this->get_conex()->prepare($sql_usuarios);
            $conexion_usuarios->execute();
            $usuarios = $conexion_usuarios->fetchAll(PDO::FETCH_ASSOC);

            $sql = "INSERT INTO notificaciones (titulo, descripcion, fecha, usuario_id, activo) 
                    VALUES (:titulo, :descripcion, :fecha, :usuario_id, :activo)";
            $conexion = $this->get_conex()->prepare($sql);

            $result = true;

            foreach ($usuarios as $usuario) {
                $usuario_id = $usuario['id_usuario'];
                
                $conexion->bindParam(":titulo", $titulo);
                $conexion->bindParam(":descripcion", $descripcion);
                $conexion->bindParam(":fecha", $fecha);
                $conexion->bindParam(":usuario_id", $usuario_id);
                $conexion->bindParam(":activo", $activo);
                $this->cambiar_db_negocio();


                if (!$conexion->execute()) {
                    $result = false;
                }
            }

            return $result;
        }

        public function consultarPropietarios($correo) { // para que el propietario vea el pago

            $sql = "SELECT 
                        a.id_apartamento, 
                        a.nro_apartamento, 
                        m.id_mensualidad, 
                        m.mes, 
                        m.anio, 
                        m.monto
                    FROM propietarios p
                    JOIN apartamentos a ON p.id_propietario = a.propietario_id
                    LEFT JOIN mensualidad m ON a.id_apartamento = m.apartamento_id
                    WHERE p.correo = :correo";

            // Prepara conexión
            $conexion = $this->get_conex()->prepare($sql);

            // Ejecuta consulta con parámetro seguro
            $result = $conexion->execute(['correo' => $correo]);

            // Obtiene resultados
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
            }
        }

        public function consultarPropietariosPagos($correo){ // Para el propietario

            $sql = "SELECT 
                pagos.id_pago, 
                pagos.fecha, 
                pagos.monto, 
                pagos.monto_dolar, 
                pagos.estado, 
                pagos.metodo_pago, 
                bancos.nombre_banco, 
                pagos.referencia, 
                pagos.imagen, 
                pagos.observacion, 
                CONCAT('AP ', apartamentos.nro_apartamento, ': ', 
                    CASE mensualidad.mes
                        WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                    END, ' ', mensualidad.anio
                ) AS mes_anio
            FROM pagos
            JOIN bancos ON pagos.banco_id = bancos.id_banco
            JOIN pagos_mensualidad ON pagos.id_pago = pagos_mensualidad.pago_id
            JOIN mensualidad ON pagos_mensualidad.mensualidad_id = mensualidad.id_mensualidad
            JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento
            JOIN propietarios ON apartamentos.propietario_id = propietarios.id_propietario
            WHERE propietarios.correo = :correo
            AND pagos.estado = 'PENDIENTE'
            ORDER BY pagos.fecha DESC";

            // Prepara conexión
            $conexion = $this->get_conex()->prepare($sql);

            // Ejecuta consulta con parámetro seguro
            $result = $conexion->execute(['correo' => $correo]);

            // Obtiene resultados
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
            }
        }

        private function consultar(){ // SI SE USA

            //$this->cambiar_db_seguridad();

            // Mouseque Herramienta misteriosa que nos ayudara mas tarde
            // 1) Sentencia SQL de toda la vida.
            $sql = "SELECT 
                p.id_pago,
                (SELECT SUM(detalles_pagos.monto) 
                FROM detalles_pagos 
                WHERE detalles_pagos.pago_id = p.id_pago) AS monto,
                (
                    SELECT dp.fecha
                    FROM detalles_pagos dp
                    WHERE dp.pago_id = p.id_pago
                    ORDER BY dp.fecha ASC
                    LIMIT 1
                ) AS primera_fecha_detalle,
                p.estado,
                a.nro_apartamento AS apartamento,
                CONCAT(
                    CASE m.mes
                        WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                    END, ' ', m.anio
                ) AS mensualidad

            FROM pagos p
            LEFT JOIN detalles_pagos dp ON dp.pago_id = p.id_pago
            LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
            LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
            LEFT JOIN apartamentos a ON m.apartamento_id = a.id_apartamento

            GROUP BY p.id_pago
            ORDER BY 
                MAX(CASE WHEN p.estado = 'No verificado' THEN 1 ELSE 0 END) DESC,
                MAX(dp.fecha) DESC";

            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            //$this->cambiar_db_negocio();
            return ["resultado"=>$result,"datos"=>$datos];
        }

        public function consultarPorCorreo($correo) {
            $sql = "SELECT 
                p.id_pago,
                (SELECT SUM(detalles_pagos.monto) 
                FROM detalles_pagos 
                WHERE detalles_pagos.pago_id = p.id_pago) AS monto,
                (
                    SELECT dp.fecha
                    FROM detalles_pagos dp
                    WHERE dp.pago_id = p.id_pago
                    ORDER BY dp.fecha ASC
                    LIMIT 1
                ) AS primera_fecha_detalle,
                p.estado,
                a.nro_apartamento AS apartamento,
                CONCAT(
                    CASE m.mes
                        WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                    END, ' ', m.anio
                ) AS mensualidad

            FROM pagos p
            LEFT JOIN detalles_pagos dp ON dp.pago_id = p.id_pago
            LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
            LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
            LEFT JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
            LEFT JOIN habitantes_apartamentos pa ON pa.apartamento_id = a.id_apartamento
            LEFT JOIN habitantes per ON per.id_habitante = pa.habitante_id
            WHERE per.correo = :correo
            GROUP BY p.id_pago
            ORDER BY 
                MAX(CASE WHEN p.estado = 'No procesado' THEN 1 ELSE 0 END) DESC,
                MAX(dp.fecha) DESC";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(':correo', $correo);
            $resultado = $conexion->execute();

            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($resultado) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_PAGOS, "PAGOS DE HABITANTE CON CORREO: $correo");
                return $datos;
            } else {
                return ["estatus" => false, "mensaje" => "Error al consultar pagos por correo"];
            }
        }

        private function consultar_pago() { // SI SE USA dp.caja_id,
            $sql = "SELECT 
                        p.id_pago,
                        (SELECT SUM(detalles_pagos.monto) FROM detalles_pagos WHERE detalles_pagos.pago_id = p.id_pago) AS monto_pago,
                        p.estado,
                        p.observacion,

                        dp.id_detalle_pago,
                        dp.fecha,
                        dp.monto AS monto,
                        dp.monto_dolar,
                        dp.tipo_pago,
                        
                        pm.mensualidad_id as mensualidad_id,
                        m.apartamento_id,
                        m.mes,
                        m.anio,
                        m.monto as monto_mensualidad,
                        a.nro_apartamento,

                        bt.id_banco_transaccion,
                        bt.referencia,
                        bt.imagen,
                        b.id_banco as banco_id,
                        b.nombre_banco

                    FROM pagos p
                    LEFT JOIN detalles_pagos dp ON p.id_pago = dp.pago_id
                    LEFT JOIN pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                    LEFT JOIN mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                    LEFT JOIN apartamentos a ON m.apartamento_id = a.id_apartamento
                    LEFT JOIN banco_transacciones bt ON dp.id_detalle_pago = bt.detalle_pago_id
                    LEFT JOIN bancos b ON bt.banco_id = b.id_banco
                    WHERE p.id_pago = :id_pago";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_pago", $this->id_pago);
            $result = $conexion->execute();        
            $filas = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if (!$filas || count($filas) === 0) {
                return ["estatus" => false, "mensaje" => "No se encontró el pago"];
            }

            // Extraer la primera fila
            $primera = $filas[0];

            // Armar los datos finales
            $datos_pago = [
                "estatus" => true,
                "id_pago" => $primera["id_pago"],
                "estado" => $primera["estado"],
                "observacion" => $primera["observacion"],
                "monto_pago" => $primera["monto_pago"],
                "apartamento_id" => $primera["apartamento_id"],
                "mensualidad_id" => $primera["mensualidad_id"],
                "monto_mensualidad" => $primera["monto_mensualidad"],
                "nro_apartamento" => $primera["nro_apartamento"],
                "mes_mensualida" => $primera["mes"],
                "detalles" => []
            ];

            // Agregar los detalles
            foreach ($filas as $f) {
                // Puedes verificar si existe un detalle válido
                if ($f["id_detalle_pago"] !== null) {
                    $datos_pago["detalles"][] = [
                        "id_detalle_pago" => $f["id_detalle_pago"],
                        "fecha" => $f["fecha"],
                        "monto" => $f["monto"],
                        "monto_dolar" => $f["monto_dolar"],
                        "tipo_pago" => $f["tipo_pago"],
                        "referencia" => $f["referencia"],
                        "banco_id" => $f["banco_id"],
                        "nombre_banco" => $f["nombre_banco"],
                        "imagen" => $f["imagen"],
                        "id_banco_transaccion" => $f["id_banco_transaccion"]
                    ];
                }
            }

            return ["resultado"=>$result,"datos"=>$datos_pago];
        }

        private function registrar_pago(){ // SI SE USA
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "INSERT INTO pagos(estado,observacion) VALUES (:estado,:observacion)";
        
            $conexion = $this->get_conex()->prepare($sql);            
            $conexion->bindParam(":estado", $this->estado);
            $conexion->bindParam(":observacion", $this->observacion);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            return $result;
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

            //$this->cambiar_db_negocio();

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

        private function editar_pago(){ // SI SE USA
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("editar");
            //if(!($validaciones["estatus"])){return $validaciones;}        

            //$this->cambiar_db_seguridad();

            $sql = "UPDATE pagos SET estado=:estado,observacion=:observacion WHERE id_pago=:id_pago";

            $conexion = $this->get_conex()->prepare($sql);    
            $conexion->bindParam(":id_pago", $this->id_pago);
            $conexion->bindParam(":estado", $this->estado);
            $conexion->bindParam(":observacion", $this->observacion);

            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            //$this->cambiar_db_negocio();   

            return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];  
        }

        private function eliminar_pago(){ // SI SE USA
            //Validamos los datos obtenidos del controlador
            //$validaciones = $this->validarDatos("eliminar");
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$pago_alterado = $this->consultar_pago();

            //$this->cambiar_db_seguridad();

            $nombres_imagenes = $this->obtener_nombres_imagenes_asociadas();

            $sql = "DELETE FROM pagos WHERE id_pago = :id_pago";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_pago", $this->id_pago);
            $result = $conexion->execute();
            $filas_afectadas = $conexion->rowCount();

            $ruta_base = "recursos/img/pagos/";

            foreach ($nombres_imagenes as $nombre_archivo) {
                $ruta_completa = $ruta_base . $nombre_archivo;

                // Verificamos que el archivo exista antes de intentar borrarlo para evitar errores.
                if (file_exists($ruta_completa)) {
                    unlink($ruta_completa);
                }
            }
            
            return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
        }

        public function obtener_imagen_actual(){

            //$this->cambiar_db_seguridad(); 
            $sql = "SELECT imagen FROM pagos WHERE id_pago = :id_pago";
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_pago", $this->id_pago);
            $conexion->execute();
            //$this->cambiar_db_negocio();

            $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado["imagen"] : null;
        }

        private function lastId(){ // SI SE USA
            //$this->cambiar_db_seguridad();
            $sql = "SELECT MAX(id_pago) as last_id FROM pagos";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);
            //$this->cambiar_db_negocio();

            return ["resultado"=>$result,"datos"=>$datos];
        }

        private function obtener_nombres_imagenes_asociadas(){
            $sql = "SELECT bt.imagen
                FROM banco_transacciones bt
                JOIN detalles_pagos dp ON bt.detalle_pago_id = dp.id_detalle_pago
                WHERE dp.pago_id = :id_pago AND bt.imagen IS NOT NULL AND bt.imagen != ''";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_pago", $this->id_pago);
            $conexion->execute();

            // Devuelve un array simple con los nombres de archivo: ['img1.jpg', 'img2.png']
            return $conexion->fetchAll(PDO::FETCH_COLUMN);
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

            //$this->cambiar_db_negocio();

            if ($result) {
                $id_ultimo = $this->lastIdPagoMensualidad();//obtenemos el ultimo id
                $this->set_id_pago_mensualidad($id_ultimo["mensaje"]);

                return ["estatus"=>true,"mensaje"=>"OK"];
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este pago"];
            }
        }

        public function editar_pagos_mensualidad(){
            //Validamos los datos obtenidos del controlador (validaciones back-end)
            //$validaciones = $this->validarDatos();
            //if(!($validaciones["estatus"])){return $validaciones;}
            
            //$this->cambiar_db_seguridad();

            $sql = "UPDATE pagos_mensualidad SET mensualidad_id = :mensualidad_id WHERE pago_id = :pago_id";
        
            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":pago_id", $this->pago_id);
            $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
            $result = $conexion->execute();

            //$this->cambiar_db_negocio();

            if ($result) {
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
            //$this->cambiar_db_negocio();

            if ($result) {
                return ["estatus"=>true,"mensaje"=>$datos["last_id"]];
            } else {
                return ["estatus"=>false,"mensaje"=>"Error en la consulta"];
            } 
        }

        /*public function consultarMensualidad(){
            $sql = "SELECT m.id_mensualidad,m.monto,m.tasa_dolar,m.mes,m.anio,a.nro_apartamento FROM mensualidad m JOIN apartamentos a ON m.apartamento_id = a.id_apartamento ORDER BY a.nro_apartamento, m.anio, m.mes";
            $conexion = $this->get_conex()->prepare($sql);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
            //$this->cambiar_db_negocio();
            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }*/

        public function consultarMensualidadPendiente($id_apartamento) {
            $sql = "SELECT 
                m.id_mensualidad,
                m.monto,
                m.tasa_dolar,
                m.mes,
                m.anio,
                m.porcentaje_interes,
                m.limite_mensualidad,
                COALESCE(SUM(dp.monto), 0) AS total_pagado,
                (m.monto - COALESCE(SUM(dp.monto), 0)) AS pendiente
            FROM mensualidad m
            LEFT JOIN pagos_mensualidad pm ON m.id_mensualidad = pm.mensualidad_id
            LEFT JOIN detalles_pagos dp ON pm.detalle_pago_id = dp.id_detalle_pago
            WHERE m.apartamento_id = :id_apartamento
            GROUP BY 
                m.id_mensualidad, m.monto, m.tasa_dolar, 
                m.mes, m.anio, m.porcentaje_interes, m.limite_mensualidad
            HAVING pendiente > 0
            ORDER BY m.anio, m.mes";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_apartamento", $id_apartamento);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        public function consultarMensualidadEspecifica() {
            $sql = "SELECT 
                m.id_mensualidad,
                m.monto,
                m.tasa_dolar,
                m.mes,
                m.anio,
                m.porcentaje_interes,
                m.limite_mensualidad,
                COALESCE(SUM(dp.monto), 0) AS total_pagado,
                (m.monto - COALESCE(SUM(dp.monto), 0)) AS pendiente
            FROM mensualidad m
            LEFT JOIN pagos_mensualidad pm ON m.id_mensualidad = pm.mensualidad_id
            LEFT JOIN detalles_pagos dp ON pm.detalle_pago_id = dp.id_detalle_pago
            WHERE m.id_mensualidad = :mensualidad_id
            GROUP BY 
                m.id_mensualidad, m.monto, m.tasa_dolar, 
                m.mes, m.anio, m.porcentaje_interes, m.limite_mensualidad
            ORDER BY m.anio, m.mes";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":mensualidad_id", $this->mensualidad_id);
            $result = $conexion->execute();
            $datos = $conexion->fetch(PDO::FETCH_ASSOC);

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }

        /*public function consultarMensualidad($id_apartamento) {
            $sql = "SELECT 
                    id_mensualidad,
                    monto,
                    monto_dolar,
                    mes,
                    anio
                FROM mensualidad
                WHERE apartamento_id = :id_apartamento
                ORDER BY anio, mes";

            $conexion = $this->get_conex()->prepare($sql);
            $conexion->bindParam(":id_apartamento", $id_apartamento);
            $result = $conexion->execute();
            $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($result == true) {
                return $datos;
            } else {
                return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
            }
        }*/

        private function validarDatos($consulta = "registrar"){
            if ($consulta == "editar" || $consulta == "eliminar"){
                if (!(isset($this->id_pago))) {return ["estatus"=>false,"mensaje"=>"El id del Pago requerido no se recibio correctamente"];}

                if (empty($this->id_pago)) {return ["estatus"=>false,"mensaje"=>"El id del Pago requerido esta vacio"];}
                
                if(is_numeric($this->id_pago)){
                    if (!($this->validarClaveForanea("pagos","id_pago",$this->id_pago))) {
                        return ["estatus"=>false,"mensaje"=>"El Pago seleccionado no existe"];
                    }
                    if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
                }
                else{return ["estatus"=>false,"mensaje"=>"El id del Pago debe ser un valor numerico entero"];}
            }

            if (!(isset($this->estado) && isset($this->observacion))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            if (empty($this->estado) || empty($this->observacion)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}
        
            if(!(is_string($this->estado))){
                return ["estatus"=>false,"mensaje"=>"El campo 'Estado' no posee un valor valido"];
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