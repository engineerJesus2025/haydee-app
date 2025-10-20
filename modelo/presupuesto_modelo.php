<?php

require_once "modelo/conexion.php";

class Presupuesto extends Conexion
{
    private $id_presupuesto;
    private $fecha;
    private $cuota_reserva;
    private $observacion; 

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_presupuesto($id_presupuesto)
    {
        $this->id_presupuesto = $id_presupuesto;
    }

    public function get_id_presupuesto()
    {
        return $this->id_presupuesto;
    }

    public function set_fecha($fecha)
    {
        $this->fecha = $fecha;
    }

    public function get_fecha()
    {
        return $this->fecha;
    }

    public function set_cuota_reserva($cuota_reserva)
    {
        $this->cuota_reserva = $cuota_reserva;
    }

    public function get_cuota_reserva()
    {
        return $this->cuota_reserva;
    }

    public function set_observacion($observacion)
    {
        $this->observacion = $observacion;
    }

    public function get_observacion()
    {
        return $this->observacion;
    }

    public function realizar_consulta($accion){
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                if ($respuesta["resultado"]) {                        
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_meses_faltantes':
                $respuesta = $this->consultar_meses_faltantes();
                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_presupuesto':
                $respuesta = $this->consultar_presupuesto();
                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_presupuestos_mensualidades':
                $respuesta = $this->consultar_presupuestos_mensualidades();
                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'registrar':
                $validaciones = $this->validarDatos();
                if(!($validaciones["estatus"])){return $validaciones;} 

                $respuesta = $this->registrar();

                if ($respuesta) {                    
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este Presupuesto"];
                }

            case 'editar':
                $validaciones = $this->validarDatos("editar");
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este Presupuesto"];
                }

            case 'eliminar':
                $validaciones = $this->validarDatos("eliminar");
                if(!($validaciones["estatus"])){return $validaciones;}
                
                $respuesta = $this->eliminar();
                
                if ($respuesta) {                    
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este Presupuesto"];
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
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    private function consultar()
    {
        $sql = "SELECT id_presupuesto, MONTH(fecha) as mes_fecha, YEAR(fecha) as anio_fecha, SUM(detalles_presupuesto.monto_detalle) as monto_estimado, presupuesto.cuota_reserva ,presupuesto.observacion FROM presupuesto INNER JOIN detalles_presupuesto ON presupuesto.id_presupuesto = detalles_presupuesto.presupuesto_id GROUP BY presupuesto.id_presupuesto";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_meses_faltantes()
    {
        $sql = "WITH RECURSIVE MesesDelCalendario (anio, mes) AS (
    
        SELECT anio, mes FROM (
            
            SELECT YEAR(presupuesto.fecha) as anio, MONTH(presupuesto.fecha) mes, 1 AS sort_order FROM presupuesto
            UNION ALL
            
            SELECT YEAR(CURDATE()), 1, 2 AS sort_order
            WHERE NOT EXISTS (SELECT 1 FROM presupuesto)
            
            ORDER BY sort_order
            LIMIT 1
        ) AS PuntoDePartida

            UNION ALL
            
            SELECT
                CASE WHEN mes = 12 THEN anio + 1 ELSE anio END,
                CASE WHEN mes = 12 THEN 1 ELSE mes + 1 END
            FROM MesesDelCalendario
            
            WHERE anio < YEAR(CURDATE()) OR (anio = YEAR(CURDATE()) AND mes < MONTH(CURDATE()))
        )
        SELECT
            c.anio AS anio_faltante,
            c.mes AS mes_faltante
        FROM
            MesesDelCalendario c
        LEFT JOIN
            presupuesto p ON c.anio = YEAR(p.fecha) AND c.mes = MONTH(p.fecha)
        WHERE
            p.id_presupuesto IS NULL
        ORDER BY
            anio_faltante, mes_faltante;";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_presupuesto()
    {
        $sql = "SELECT id_presupuesto, MONTH(fecha) as mes_fecha, YEAR(fecha) as anio_fecha, presupuesto.cuota_reserva ,presupuesto.observacion, fecha FROM presupuesto WHERE id_presupuesto = :id_presupuesto";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_presupuesto", $this->id_presupuesto);

        $result = $conexion->execute();        
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }
    
    private function consultar_presupuestos_mensualidades()
    {
        $sql = "SELECT tipo_gasto.nombre_tipo_gasto as nombre, SUM(detalles_presupuesto.monto_detalle) as monto, GROUP_CONCAT(detalles_presupuesto.id_detalle_presupuesto) as id_presupuestos_asociados, id_presupuesto FROM tipo_gasto INNER JOIN detalles_presupuesto ON detalles_presupuesto.tipo_gasto_id = tipo_gasto.id_tipo_gasto INNER JOIN presupuesto ON detalles_presupuesto.presupuesto_id = presupuesto.id_presupuesto
            WHERE presupuesto.fecha = :fecha
            GROUP BY tipo_gasto.nombre_tipo_gasto";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":fecha", $this->fecha);

        $result = $conexion->execute();        
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function registrar()
    {
        $sql = "INSERT INTO presupuesto(fecha,cuota_reserva,observacion) VALUES (:fecha,:cuota_reserva,:observacion)";
            
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":cuota_reserva", $this->cuota_reserva);
        $conexion->bindParam(":observacion", $this->observacion);
        $result = $conexion->execute();

        return $result;
    }

    private function editar()
    {
        $sql = "UPDATE presupuesto SET fecha=:fecha,cuota_reserva=:cuota_reserva,observacion=:observacion WHERE id_presupuesto=:id_presupuesto";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":cuota_reserva", $this->cuota_reserva);        
        $conexion->bindParam(":observacion", $this->observacion);
        $conexion->bindParam(":id_presupuesto", $this->id_presupuesto);

        $result = $conexion->execute();
        
        return $result;
    }

    private function eliminar()
    {
        $sql = "DELETE FROM presupuesto WHERE id_presupuesto = :id_presupuesto";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_presupuesto", $this->id_presupuesto);
        $result = $conexion->execute();
 
        return $result;
    }

    private function lastId()
    {
        $sql = "SELECT MAX(id_presupuesto) as last_id FROM presupuesto";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos["last_id"]];        
    }
    
    private function validarDatos($consulta = "registrar")
    {   
        // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_presupuesto))) {return ["estatus"=>false,"mensaje"=>"El id del Presupuesto requerido no se recibio correctamente"];}

            if (empty($this->id_presupuesto)) {return ["estatus"=>false,"mensaje"=>"El id del Presupuesto requerido esta vacio"];}

            if(is_numeric($this->id_presupuesto)){
                if (!($this->validarClaveForanea("presupuesto","id_presupuesto",$this->id_presupuesto))) {
                    return ["estatus"=>false,"mensaje"=>"El presupuesto mensual seleccionado no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Presupuesto tiene debe ser un valor numerico entero"];}
        }
        // Validamos que los campos enviados si existan

        if (!(isset($this->fecha) && isset($this->cuota_reserva))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        // Validamos que los campos enviados no esten vacios

        if (empty($this->fecha) || empty($this->cuota_reserva)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(!(is_string($this->fecha)) || !($this->validarFecha($this->fecha))){
            return ["estatus"=>false,"mensaje"=>"El campo 'fecha' no posee un valor valido"];
        }

        if(!(is_string($this->cuota_reserva)) || !(preg_match("/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/",$this->cuota_reserva))){
            return ["estatus"=>false,"mensaje"=>"El campo 'cuota_reserva' no posee un valor valido"];
        }
        if (!empty($this->observacion)) {
            if(!(is_string($this->observacion)) || !(preg_match("/^[A-Za-z0-9,. ñÑ]*$/",$this->observacion))){
                return ["estatus"=>false,"mensaje"=>"El campo 'observacion' no posee un valor valido"];
            }
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarClaveForanea($tabla,$nombreClave,$valor)
    {

        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        return ($result)?true:false;
    }

    private function validarFecha($fecha){
        $valores = explode('-', $fecha);
        if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
            return true;
        }
        return false;
    }
}
?>