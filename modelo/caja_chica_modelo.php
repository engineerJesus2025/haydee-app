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

    public function realizar_consulta($accion){
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                if ($respuesta["resultado"] == true) {
                    $this->registrar_bitacora(CONSULTAR, GESTIONAR_CAJA_CHICA, "TODOS LAS CAJAS");
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }
            case 'buscar_mes':
                $respuesta = $this->buscar_mes();
                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'editar_observacion':
                $validaciones = $this->validarDatos();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar_observacion();

                if ($respuesta["resultado"]) {
                    if ($respuesta["fila_afectada"] < 1) {
                        return ["estatus"=>false,"mensaje"=>"No se modificó ningún registro"];
                    }
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar esta observación"];
                }

            case 'verificar_caja_mes':                                
                $respuesta = $this->verificar_caja_mes();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            default:
                return ["estatus"=>false,"mensaje"=>"La operacion fue infructuosa"];
                break;
        }
    }

    private function consultar()
    {
        $sql = "SELECT * FROM caja_chica ORDER BY caja_chica.id_caja_chica DESC";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function buscar_mes()
    {    
        $sql = "SELECT 'Ingreso' as movimiento, detalles_pagos.fecha, SUM(detalles_pagos.monto) as monto, CONCAT('Apartamento Nº ',apartamentos.nro_apartamento,' - ', habitantes.nombre,' ', habitantes.apellido) as remitente FROM detalles_pagos INNER JOIN pagos ON pagos.id_pago = detalles_pagos.pago_id INNER JOIN pagos_mensualidad ON pagos_mensualidad.detalle_pago_id = detalles_pagos.id_detalle_pago INNER JOIN mensualidad ON mensualidad.id_mensualidad = pagos_mensualidad.mensualidad_id INNER JOIN apartamentos ON apartamentos.id_apartamento = mensualidad.apartamento_id INNER JOIN habitantes_apartamentos ON apartamentos.id_apartamento = habitantes_apartamentos.apartamento_id INNER JOIN habitantes ON habitantes_apartamentos.habitante_id = habitantes.id_habitante WHERE detalles_pagos.caja_id = :id_caja &&
            habitantes_apartamentos.tipo_vinculo = 'Propietario'
            GROUP BY pagos.id_pago
            UNION
            SELECT 'Egreso' as movimiento, MAX(detalles_gastos.fecha) as fecha, SUM(detalles_gastos.monto) as monto, CONCAT('Pago de ',proveedores.servicio,' - ', proveedores.nombre_proveedor) as remitente FROM detalles_gastos INNER JOIN gastos ON detalles_gastos.gasto_id = gastos.id_gasto INNER JOIN proveedores ON proveedores.id_proveedor = gastos.proveedor_id WHERE detalles_gastos.caja_id = :id_caja GROUP BY gastos.id_gasto;";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_caja", $this->id_caja_chica);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];        
    }

    private function editar_observacion()
    {
        $sql = "UPDATE caja_chica SET observaciones=:observaciones WHERE id_caja_chica=:id_caja_chica";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":observaciones", $this->observaciones);
        $conexion->bindParam(":id_caja_chica", $this->id_caja_chica);

        $result = $conexion->execute();
        $filas_afectadas = $conexion->rowCount();
        
        return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
    }

    private function verificar_caja_mes()
    {
        $sql = "CALL sp_gestion_caja_chica_mensual();";

        $conexion = $this->get_conex()->prepare($sql);        

        $result = $conexion->execute();   
        return $result;
    }

    private function validarDatos()
    {   
        if (!(isset($this->id_caja_chica))) {return ["estatus"=>false,"mensaje"=>"El ID requerido no se recibio correctamente"];}

        if (empty($this->id_caja_chica)) {return ["estatus"=>false,"mensaje"=>"El ID requerido esta vacío"];}

        if(is_numeric($this->id_caja_chica)){
            if (!($this->validarClaveForanea("caja_chica","id_caja_chica",$this->id_caja_chica))) {
                return ["estatus"=>false,"mensaje"=>"La Caja seleccionada no existe"];
            }
        }
        else{return ["estatus"=>false,"mensaje"=>"La Caja seleccionada tiene debe ser un valor numerico entero"];}

        if (!(isset($this->observaciones))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}


        if (empty($this->observaciones)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        
        if(!(is_string($this->observaciones)) || !(preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]{0,100}$/",$this->observaciones))){
            return ["estatus"=>false,"mensaje"=>"El campo 'observaciones' no posee un valor valido"];
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
}
?>