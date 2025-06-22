<?php

require_once "modelo/conexion.php";

class Mensualidad extends Conexion
{
    private $id_mensualidad;
    private $monto;
    private $tasa_dolar;
    private $mes;
    private $anio;
    private $apartamento_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_mensualidad($id_mensualidad)
    {
        $this->id_mensualidad = $id_mensualidad;
    }

    public function get_id_mensualidad()
    {
        return $this->id_mensualidad;
    }

    public function set_monto($monto)
    {
        $this->monto = $monto;
    }

    public function get_monto()
    {
        return $this->monto;
    }

    public function set_tasa_dolar($tasa_dolar)
    {
        $this->tasa_dolar = $tasa_dolar;
    }

    public function get_tasa_dolar()
    {
        return $this->tasa_dolar;
    }

    public function set_mes($mes)
    {
        $this->mes = $mes;
    }

    public function get_mes()
    {
        return $this->mes;
    }

    public function set_anio($anio)
    {
        $this->anio = $anio;
    }

    public function get_anio()
    {
        return $this->anio;
    }

    public function set_apartamento_id($apartamento_id)
    {
        $this->apartamento_id = $apartamento_id;
    }

    public function get_apartamento_id()
    {
        return $this->apartamento_id;
    }

    public function set_gasto_mes_id($gasto_mes_id)
    {
        $this->gasto_mes_id = $gasto_mes_id;
    }

    //HAsta aqui todo normal
    //esta funcion se ejecuta en el login para autenticar
   
    //hace lo que dice
    public function verificarMeses()
    {
        $sql = "SELECT MONTH(gastos.fecha) as mes_gasto, YEAR(gastos.fecha) as anio_gasto, mensualidad.mes as mes_mensualidad FROM gastos LEFT JOIN mensualidad ON mensualidad.mes = MONTH(gastos.fecha) GROUP BY MONTH(gastos.fecha);";

        $conexion = $this->get_conex()->prepare($sql);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        

        if ($result == true) {            
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar()
    {
        $sql = "SELECT mes, anio FROM mensualidad GROUP BY mes, anio ORDER BY mes DESC";

        $conexion = $this->get_conex()->prepare($sql);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        

        if ($result == true) {
            $this->registrar_bitacora(CONSULTAR, GESTIONAR_MENSUALIDAD, "TODOS LAS MENSUALIDADES");//registra cuando se entra al modulo de mensualidad

            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_mensualidad()
    {
        $mes_entero = intval($this->mes);
        $anio_entero = intval($this->anio);
        $sql = "SELECT id_mensualidad, id_apartamento , mensualidad.mes as mes, mensualidad.anio as anio, apartamentos.nro_apartamento as nro_apartamento, personas.nombre as nombre, personas.apellido as apellido, mensualidad.monto as monto FROM mensualidad INNER JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento INNER JOIN personas_apartamentos ON personas_apartamentos.apartamento_id = apartamentos.id_apartamento INNER JOIN personas ON personas_apartamentos.persona_id = personas.id_persona WHERE mensualidad.mes = :mes && mensualidad.anio = :anio";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes_entero,PDO::PARAM_INT);
        $conexion->bindParam(":anio", $anio_entero,PDO::PARAM_INT);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        
        // var_dump($this->mes,$this->anio,$datos);
        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_apartamentos()
    {
        $sql = "SELECT * FROM apartamentos";

        $conexion = $this->get_conex()->prepare($sql);        

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_gastos()
    {
        $mes_entero = intval($this->mes);
        $anio_entero = intval($this->anio);

        $sql = 'SELECT tipo_gasto.nombre_tipo_gasto as nombre, SUM(gastos.monto) as monto, tipo_gasto.id_tipo_gasto as id_tipo_gasto, GROUP_CONCAT(gastos.id_gasto) as id_gastos_asociados FROM gastos INNER JOIN tipo_gasto ON gastos.tipo_gasto_id = tipo_gasto.id_tipo_gasto WHERE MONTH(gastos.fecha) = :mes && YEAR(gastos.fecha) = :anio GROUP BY tipo_gasto.nombre_tipo_gasto';            
        // Que precioso es sql
        $conexion = $this->get_conex()->prepare($sql); 
        $conexion->bindParam(":mes", $mes_entero,PDO::PARAM_INT);
        $conexion->bindParam(":anio", $anio_entero,PDO::PARAM_INT);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_gastos_asociados()
    {
        $sql = "SELECT id_gasto, id_mensualidad FROM gastos INNER JOIN gastos_mensualidades ON gastos.id_gasto = gastos_mensualidades.gasto_id INNER JOIN mensualidad ON gastos_mensualidades.mensualidad_id = mensualidad.id_mensualidad WHERE mensualidad.id_mensualidad = :id_mensualidad";

        $conexion = $this->get_conex()->prepare($sql); 
        $conexion->bindParam(":id_mensualidad", $this->id_mensualidad);
        
        $result = $conexion->execute();
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);        

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function registrar()
    {           
        $sql = "INSERT INTO mensualidad(monto,tasa_dolar,mes,anio,apartamento_id) VALUES (:monto,:tasa_dolar,:mes,:anio,:apartamento_id)";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":tasa_dolar", $this->tasa_dolar);
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);
        $conexion->bindParam(":apartamento_id", $this->apartamento_id);
        $result = $conexion->execute();

        $conexion = $this->get_conex();//otra conexion para buscar el ultimo id
        $res = $conexion->lastInsertId();

        if ($result) {            
            return ["estatus"=>true,"mensaje"=>"OK","lastId"=>$res];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta Mensualidad"];
        }
    }

    public function editar()
    {           
        $sql = "UPDATE mensualidad SET monto = :monto, tasa_dolar = :tasa_dolar, mes = :mes, anio = :anio, apartamento_id = :apartamento_id WHERE id_mensualidad = :id_mensualidad";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":tasa_dolar", $this->tasa_dolar);
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);
        $conexion->bindParam(":apartamento_id", $this->apartamento_id);
        $conexion->bindParam(":id_mensualidad", $this->id_mensualidad);
        $result = $conexion->execute();

        if ($result) {            
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta Mensualidad"];
        }
    }
    public function eliminar()
    {
        $mes_entero = intval($this->mes);
        $anio_entero = intval($this->anio);

        $sql = "DELETE FROM mensualidad WHERE mes = :mes && anio = :anio";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes_entero);
        $conexion->bindParam(":anio", $anio_entero);
        $result = $conexion->execute();
        
        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar esta Mensualidad"];
        }
    }

}
?>