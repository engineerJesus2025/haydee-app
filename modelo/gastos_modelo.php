<?php
require_once("modelo/conexion.php");

class Gastos extends Conexion
{
    private $id_gasto;
    private $fecha;
    private $monto;
    private $tipo_gasto;
    private $tasa_dolar;
    private $metodo_pago;
    private $referencia;
    private $imagen;
    private $descripcion_gasto;
    private $gasto_mes_id;
    private $banco_id;
    private $proveedor_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_gasto($id_gasto)
    {
        $this->id_gasto = $id_gasto;
    }
    public function get_id_gasto()
    {
        return $this->id_gasto;
    }
    public function set_fecha($fecha)
    {
        $this->fecha = $fecha;
    }

    public function get_fecha()
    {
        return $this->fecha;
    }
    public function set_monto($monto)
    {
        $this->monto = $monto;
    }
    public function get_monto()
    {
        return $this->monto;
    }
    public function set_tipo_gasto($tipo_gasto)
    {
        $this->tipo_gasto = $tipo_gasto;
    }
    public function get_tipo_gasto()
    {
        return $this->tipo_gasto;
    }

    public function set_tasa_dolar($tasa_dolar)
    {
        $this->tasa_dolar = $tasa_dolar;
    }
    public function get_tasa_dolar()
    {
        return $this->tasa_dolar;
    }
    public function set_metodo_pago($metodo_pago)
    {
        $this->metodo_pago = $metodo_pago;
    }
    public function get_metodo_pago()
    {
        return $this->metodo_pago;
    }
    public function set_referencia($referencia)
    {
        $this->referencia = $referencia;
    }
    public function get_referencia()
    {
        return $this->referencia;
    }
    public function set_gasto_mes_id($gasto_mes_id)
    {
        $this->gasto_mes_id = $gasto_mes_id;
    }
    public function get_gasto_mes_id()
    {
        return $this->gasto_mes_id;
    }
    public function set_banco_id($banco_id)
    {
        $this->banco_id = $banco_id;
    }
    public function get_banco_id()
    {
        return $this->banco_id;
    }
    public function set_proveedor_id($proveedor_id)
    {
        $this->proveedor_id = $proveedor_id;
    }
    public function get_proveedor_id()
    {
        return $this->proveedor_id;
    }
    public function set_imagen($imagen)
    {
        $this->imagen = $imagen;
    }
    public function get_imagen()
    {
        return $this->imagen;
    }
    public function set_descripcion_gasto($descripcion_gasto)
    {
        $this->descripcion_gasto = $descripcion_gasto;
    }
    public function get_descripcion_gasto()
    {
        return $this->descripcion_gasto;
    }

    public function consultar()
    {
        $sql = "SELECT * FROM gastos";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        if ($result == true) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }

    }

public function consultar_gasto_id() {
    $sql = "SELECT 
                g.*,
                b.id_banco,
                b.nombre_banco,
                p.id_proveedor,
                p.nombre_proveedor
            FROM gastos g
            LEFT JOIN bancos b ON g.banco_id = b.id_banco
            LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
            WHERE g.id_gasto = :id_gasto";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":id_gasto", $this->id_gasto);
    $result = $conexion->execute();
    $datos = $conexion->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $datos;
    } else {
        return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
    }
}

    public function registrar(){
        $sql = "INSERT INTO gastos (fecha, monto, tipo_gasto, metodo_pago, referencia, imagen, descripcion_gasto, gasto_mes_id, banco_id, proveedor_id) VALUES (:fecha, :monto, :tipo_gasto, :metodo_pago, :referencia, :imagen, :descripcion_gasto, :gasto_mes_id, :banco_id, :proveedor_id)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":tipo_gasto", $this->tipo_gasto);
        $conexion->bindParam(":metodo_pago", $this->metodo_pago);
        $conexion->bindParam(":referencia", $this->referencia);
        $conexion->bindParam(":imagen", $this->imagen);
        $conexion->bindParam(":descripcion_gasto", $this->descripcion_gasto);
        $conexion->bindParam(":gasto_mes_id", $this->gasto_mes_id);
        $conexion->bindParam(":banco_id", $this->banco_id);
        $conexion->bindParam(":proveedor_id", $this->proveedor_id);
        $result = $conexion->execute();
        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar este gasto"];
        }
    }

    public function editar_gasto(){
        $sql = "UPDATE gastos SET fecha = :fecha, monto = :monto, tipo_gasto = :tipo_gasto, tasa_dolar = :tasa_dolar, metodo_pago = :metodo_pago, referencia = :referencia, imagen = :imagen, descripcion_gasto = :descripcion_gasto, gasto_mes_id = :gasto_mes_id, banco_id = :banco_id, proveedor_id = :proveedor_id WHERE id_gasto = :id_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_gasto", $this->id_gasto);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":tipo_gasto", $this->tipo_gasto);
        $conexion->bindParam(":tasa_dolar", $this->tasa_dolar);
        $conexion->bindParam(":metodo_pago", $this->metodo_pago);
        $conexion->bindParam(":referencia", $this->referencia);
        $conexion->bindParam(":imagen", $this->imagen);
        $conexion->bindParam(":descripcion_gasto", $this->descripcion_gasto);
        $conexion->bindParam(":gasto_mes_id", $this->gasto_mes_id);
        $conexion->bindParam(":banco_id", $this->banco_id);
        $conexion->bindParam(":proveedor_id", $this->proveedor_id);
        $result = $conexion->execute();
        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar editar este gasto"];
        }
    }

    public function eliminar_gasto(){
        $sql = "DELETE FROM gastos WHERE id_gasto = :id_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_gasto", $this->id_gasto);
        $result = $conexion->execute();
        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar eliminar este gasto"];
        }
    }

    public function lastId(){
        $sql = "SELECT MAX(id_gasto) as last_id FROM gastos";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        if ($result == true) {
            return ["estatus" => true, "mensaje" => $datos["last_id"]];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

    public function validarClaveForanea($tabla,$nombreClave,$valor){
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);
        return ($result)?true:false;
    }
    

public function registrar_gasto_mes($mes, $anio) {
    $sql = "INSERT INTO gastos_mes (mes, anio) VALUES (:mes, :anio)";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":mes", $mes);
    $conexion->bindParam(":anio", $anio);
    $result = $conexion->execute();
    if ($result) {
        return ["estatus" => true, "mensaje" => "OK"];
    } else {
        return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar este gasto"];
    }
}

public function consultar_gasto_mes($mes, $anio) {
    $sql = "SELECT id_gasto_mes FROM gastos_mes WHERE mes = :mes AND anio = :anio";
    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":mes", $mes);
    $conexion->bindParam(":anio", $anio);
    $conexion->execute();
    return $conexion->fetch(PDO::FETCH_ASSOC);
}

public function listar_gastos_mes() {
    $sql = "SELECT gm.id_gasto_mes, gm.mes, gm.anio
            FROM gastos_mes gm
            INNER JOIN gastos g ON g.gasto_mes_id = gm.id_gasto_mes
            GROUP BY gm.id_gasto_mes, gm.mes, gm.anio
            ORDER BY gm.anio DESC, gm.mes DESC";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->execute();
    return $conexion->fetchAll(PDO::FETCH_ASSOC);
}

public function filtrar_por_mes($gasto_mes_id) {
    $sql = "SELECT 
                g.id_gasto,
                g.fecha,
                g.monto,
                g.tipo_gasto,
                g.metodo_pago,
                g.referencia,
                g.descripcion_gasto,
                g.imagen,
                b.nombre_banco AS banco,
                p.nombre_proveedor AS proveedor
            FROM gastos g
            LEFT JOIN bancos b ON g.banco_id = b.id_banco
            LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
            WHERE g.gasto_mes_id = :id";

    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":id", $gasto_mes_id);
    $conexion->execute();
    return $conexion->fetchAll(PDO::FETCH_ASSOC);
}
public function total_por_metodo_pago($gasto_mes_id) {
    $sql = "SELECT metodo_pago, SUM(monto) as total FROM gastos WHERE gasto_mes_id = :id GROUP BY metodo_pago";
    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":id", $gasto_mes_id);
    $conexion->execute();
    return $conexion->fetchAll(PDO::FETCH_ASSOC);
}

    public function obtener_imagen_actual()
    {
        $sql = "SELECT imagen FROM gastos WHERE id_gasto = :id_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_gasto", $this->id_gasto);
        $conexion->execute();

        $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado["imagen"] : null;
    }


}