<?php

require_once "modelo/conexion.php";

class Caja_chica extends Conexion
{
    private $id_caja_chica;
    private $fecha_apertura;
    private $monto_inicial;
    private $saldo_actual;
    private $estado;
    private $observaciones;
    private $anio_fiscal_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_caja_chica($id_caja_chica){$this->id_caja_chica = $id_caja_chica;}

    public function get_id_caja_chica(){return $this->id_caja_chica;}

    public function set_fecha_apertura($fecha_apertura){$this->fecha_apertura = $fecha_apertura;}

    public function get_fecha_apertura(){return $this->fecha_apertura;}

    public function set_monto_inicial($monto_inicial){$this->monto_inicial = $monto_inicial;}

    public function get_monto_inicial(){return $this->monto_inicial;}

    public function set_saldo_actual($saldo_actual){$this->saldo_actual = $saldo_actual;}

    public function get_saldo_actual(){return $this->saldo_actual;}

    public function set_estado($estado){$this->estado = $estado;}

    public function get_estado(){return $this->estado;}

    public function set_observaciones($observaciones){$this->observaciones = $observaciones;}

    public function get_observaciones(){return $this->observaciones;}

    public function set_anio_fiscal_id($anio_fiscal_id){$this->anio_fiscal_id = $anio_fiscal_id;}

    public function get_anio_fiscal_id(){return $this->anio_fiscal_id;}


    public function consultar()
    {
        $sql = "SELECT * FROM caja_chica";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function buscar_mes()
    {
        $mes_buscar = intval(date("m",$this->fecha_apertura));
        $anio_buscar = intval(date("Y",$this->fecha_apertura));
        // $mes_buscar = "02";
    
        $sql = "SELECT 'Ingreso' as movimiento, detalles_pagos.fecha, detalles_pagos.monto, CONCAT('Apartamento Nº ',apartamentos.nro_apartamento,' - ', personas.nombre,' ', personas.apellido) as remitente FROM detalles_pagos INNER JOIN pagos_mensualidad ON pagos_mensualidad.detalle_pago_id = detalles_pagos.id_detalle_pago INNER JOIN mensualidad ON mensualidad.id_mensualidad = pagos_mensualidad.mensualidad_id INNER JOIN apartamentos ON apartamentos.id_apartamento = mensualidad.apartamento_id INNER JOIN personas_apartamentos ON apartamentos.id_apartamento = personas_apartamentos.apartamento_id INNER JOIN personas ON personas_apartamentos.persona_id = personas.id_persona WHERE MONTH(detalles_pagos.fecha) = :mes && YEAR(detalles_pagos.fecha) = :anio && mensualidad.mes = :mes
            UNION
            SELECT 'Egreso' as movimiento, gastos.fecha, gastos.monto, CONCAT('Pago de ',proveedores.servicio,' - ', proveedores.nombre_proveedor) as remitente FROM gastos INNER JOIN proveedores ON proveedores.id_proveedor = gastos.proveedor_id WHERE MONTH(gastos.fecha) = :mes && YEAR(gastos.fecha) = :anio;";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":anio", $anio_buscar);
        $conexion->bindParam(":mes", $mes_buscar);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function editar_observacion()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos();
        if(!($validaciones["estatus"])){return $validaciones;}        

        $sql = "UPDATE caja_chica SET observaciones=:observaciones WHERE id_caja_chica=:id_caja_chica";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":observaciones", $this->observaciones);
        $conexion->bindParam(":id_caja_chica", $this->id_caja_chica);

        $result = $conexion->execute();
        
        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar las observaciones"];
        }
    }


    public function guardar_conciliacion()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos();
        if(!($validaciones["estatus"])){return $validaciones;}        

        $sql = "UPDATE conciliacion_bancaria SET estado=:estado, saldo_inicio=:saldo_inicio, saldo_fin=:saldo_fin, saldo_sistema=:saldo_sistema, diferencia=:diferencia, tasa_dolar=:tasa_dolar, ingreso_banco_no_correspondido=:ingreso_banco_no_correspondido, ingreso_sistema_no_correspondido=:ingreso_sistema_no_correspondido, egreso_banco_no_correspondido=:egreso_banco_no_correspondido, egreso_sistema_no_correspondido=:egreso_sistema_no_correspondido, observaciones=:observaciones WHERE id_conciliacion=:id_conciliacion";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":estado", $this->estado);
        $conexion->bindParam(":saldo_inicio", $this->saldo_inicio);   
        $conexion->bindParam(":saldo_fin", $this->saldo_fin);
        $conexion->bindParam(":saldo_sistema", $this->saldo_sistema);
        $conexion->bindParam(":diferencia", $this->diferencia);
        $conexion->bindParam(":tasa_dolar", $this->tasa_dolar);
        $conexion->bindParam(":ingreso_banco_no_correspondido", $this->ingreso_banco_no_correspondido);
        $conexion->bindParam(":ingreso_sistema_no_correspondido", $this->ingreso_sistema_no_correspondido);
        $conexion->bindParam(":egreso_banco_no_correspondido", $this->egreso_banco_no_correspondido);
        $conexion->bindParam(":egreso_sistema_no_correspondido", $this->egreso_sistema_no_correspondido);
        $conexion->bindParam(":observaciones", $this->observaciones);
        $conexion->bindParam(":id_conciliacion", $this->id_conciliacion);

        $result = $conexion->execute();
        
        if ($result) {
            $this->registrar_bitacora(REGISTRAR, GESTIONAR_CONCILIACION_BANCARIA, "Conciliacion " . $this->fecha_inicio);
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar Guardar esta Conciliacion"];
        }
    }
    //Pendiente
    // validaciones back end (se llaman al registrar o modificar)
    private function validarDatos($consulta = "registrar")
    {   
        // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
        // if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_caja_chica))) {return ["estatus"=>false,"mensaje"=>"El ID requerido no se recibio correctamente"];}

            if (empty($this->id_caja_chica)) {return ["estatus"=>false,"mensaje"=>"El ID requerido esta vacío"];}

            if(is_numeric($this->id_caja_chica)){
                if (!($this->validarClaveForanea("caja_chica","id_caja_chica",$this->id_caja_chica))) {
                    return ["estatus"=>false,"mensaje"=>"La Caja seleccionada no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"La Caja seleccionada tiene debe ser un valor numerico entero"];}
        // }
        // Validamos que los campos enviados si existan

        if (!(isset($this->observaciones))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        // Validamos que los campos enviados no esten vacios

        if (empty($this->observaciones)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        // // Verificamos si los valores tienen los datos que deberian
        
        if(!(is_string($this->observaciones)) || !(preg_match("/^[0-9a-zA-Z ñÑ]*$/",$this->observaciones))){
            return ["estatus"=>false,"mensaje"=>"El campo 'observaciones' no posee un valor valido"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    //esta funcion es para revisar si una clave foranea existe, porque sino dara error la consulta
    private function validarClaveForanea($tabla,$nombreClave,$valor)
    { 
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);
        return ($result)?true:false;
    }
}
?>