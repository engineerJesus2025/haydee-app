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
        $sql = "SELECT * FROM mensualidad INNER JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento INNER JOIN propietarios ON apartamentos.propietario_id = propietarios.id_propietario WHERE mensualidad.mes = :mes && mensualidad.anio = :anio";

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

    public function consultar_ultima_mensualidad()
    {
        $sql = "SELECT * FROM `mensualidad` INNER JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento INNER JOIN propietarios ON apartamentos.propietario_id = propietarios.id_propietario WHERE mensualidad.mes = :mes && mensualidad.anio = :anio";
        // ORDER BY id_mensualidad
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);        

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

        $sql = 'SELECT gastos.id_gasto, gastos.tipo_gasto as tipo_gasto, SUM(gastos.monto) as monto FROM gastos INNER JOIN proveedores ON gastos.proveedor_id = proveedores.id_proveedor WHERE MONTH(gastos.fecha) = :mes && YEAR(gastos.fecha) = :anio && proveedores.servicio != "gas" && gastos.tipo_gasto = "fijo"
            UNION
            SELECT gastos.id_gasto, proveedores.servicio as tipo_gasto, SUM(gastos.monto) as monto FROM gastos INNER JOIN proveedores ON gastos.proveedor_id = proveedores.id_proveedor WHERE MONTH(gastos.fecha) = :mes && YEAR(gastos.fecha) = :anio && proveedores.servicio = "gas"           
            UNION
            SELECT gastos.id_gasto, gastos.descripcion_gasto as tipo_gasto, gastos.monto as monto FROM gastos INNER JOIN proveedores ON gastos.proveedor_id = proveedores.id_proveedor WHERE MONTH(gastos.fecha) = :mes && YEAR(gastos.fecha) = :anio && proveedores.servicio != "gas" && gastos.tipo_gasto = "variable";';
            // Nada de humildad: Me saque la pinga con este sql

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

    public function consultar_gasto_mes()
    {
        $sql = "SELECT * FROM gastos_mes WHERE mes = :mes && anio = :anio";

        $conexion = $this->get_conex()->prepare($sql); 
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);
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
        
        if (true) {

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar esta Mensualidad"];
        }
    }

}
?>