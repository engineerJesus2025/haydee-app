<?php
require_once "modelo/conexion.php";

class Detalles_gasto extends Conexion
{
    private $id_detalle_gasto;
    private $fecha;
    private $monto;
    private $monto_dolar;
    private $metodo_pago;
    private $descripcion_detalle_gasto;
    private $caja_id;
    private $gasto_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function get_id_detalle_gasto()
    {
        return $this->id_detalle_gasto;
    }
    public function get_fecha()
    {
        return $this->fecha;
    }
    public function get_monto()
    {
        return $this->monto;
    }

    public function get_monto_dolar()
    {
        return $this->monto_dolar;
    }
    public function get_metodo_pago()
    {
        return $this->metodo_pago;
    }
    public function get_descripcion_detalle_gasto()
    {
        return $this->descripcion_detalle_gasto;
    }
    public function get_caja_id()
    {
        return $this->caja_id;
    }
    public function get_gasto_id()
    {
        return $this->gasto_id;
    }

    // --- SETTERS ---
    public function set_id_detalle_gasto($id_detalle_gasto)
    {
        $this->id_detalle_gasto = $id_detalle_gasto;
    }
    public function set_fecha($fecha)
    {
        $this->fecha = $fecha;
    }
    public function set_monto($monto)
    {
        $this->monto = $monto;
    }
    public function set_monto_dolar($monto_dolar)
    {
        $this->monto_dolar = $monto_dolar;
    }
    public function set_metodo_pago($metodo_pago)
    {
        $this->metodo_pago = $metodo_pago;
    }
    public function set_descripcion_detalle_gasto($descripcion_detalle_gasto)
    {
        $this->descripcion_detalle_gasto = $descripcion_detalle_gasto;
    }
    public function set_caja_id($caja_id)
    {
        $this->caja_id = $caja_id;
    }
    public function set_gasto_id($gasto_id)
    {
        $this->gasto_id = $gasto_id;
    }


    public function consultar()
    {
        $sql = "SELECT * FROM detalles_gastos";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_detalle_gasto()
    {
        $sql = "SELECT
            dg.*,
            bt.id_banco_transaccion,
            bt.referencia,
            bt.imagen,
            b.id_banco,
            b.nombre_banco
        FROM detalles_gastos dg
        LEFT JOIN banco_transacciones bt ON bt.detalle_gasto_id = dg.id_detalle_gasto
        LEFT JOIN bancos b ON bt.banco_id = b.id_banco
        WHERE dg.id_detalle_gasto = :id_detalle_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_detalle_gasto", $this->id_detalle_gasto);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($result == true && $datos) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

    public function registrar_detalle_gasto()
    {
        // Se quita 'monto_dolar' de la consulta
        $sql = "INSERT INTO detalles_gastos (fecha, monto, metodo_pago, descripcion_detalle_gasto, caja_id, gasto_id) 
                VALUES (:fecha, :monto, :metodo_pago, :descripcion_detalle_gasto, :caja_id, :gasto_id)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":metodo_pago", $this->metodo_pago);
        $conexion->bindParam(":descripcion_detalle_gasto", $this->descripcion_detalle_gasto);
        $conexion->bindParam(":caja_id", $this->caja_id);
        $conexion->bindParam(":gasto_id", $this->gasto_id);
        $result = $conexion->execute();


        if ($result) {
            $id_ultimo = $this->lastId();//obtenemos el ultimo id
            $this->set_id_detalle_gasto($id_ultimo["mensaje"]);
            $gasto_alterado = $this->consultar_detalle_gasto();//lo consultamos

            $this->registrar_bitacora(REGISTRAR, GESTIONAR_GASTOS, $gasto_alterado["fecha"] . " (" . "Detalle gasto anexado: " . $gasto_alterado["monto"] . ")");//registramos en la bitacora
            //$this->registrar_notificacion();
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar este detalle pago"];
        }
    }

    public function editar_detalle_gasto()
    {
        $sql = "UPDATE detalles_gastos SET fecha = :fecha, monto = :monto, metodo_pago = :metodo_pago, descripcion_detalle_gasto = :descripcion_detalle_gasto, caja_id = :caja_id, gasto_id = :gasto_id WHERE id_detalle_gasto = :id_detalle_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_detalle_gasto", $this->id_detalle_gasto);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":metodo_pago", $this->metodo_pago);
        $conexion->bindParam(":descripcion_detalle_gasto", $this->descripcion_detalle_gasto);
        $conexion->bindParam(":caja_id", $this->caja_id);
        $conexion->bindParam(":gasto_id", $this->gasto_id);
        $result = $conexion->execute();

        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar editar este detalle pago"];
        }
    }

    public function eliminar_detalle_gasto()
    {
        $sql = "DELETE FROM detalles_gastos WHERE id_detalle_gasto = :id_detalle_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_detalle_gasto", $this->id_detalle_gasto);
        $result = $conexion->execute();

        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar eliminar este detalle pago"];
        }
    }

    public function eliminar_detalles_por_gasto()
{


    // 1. ELIMINAR REGISTROS DE BANCOS_TRANSACCIONES ASOCIADOS PRIMERO
    $sql_transacciones = "DELETE FROM banco_transacciones WHERE detalle_gasto_id IN (SELECT id_detalle_gasto FROM detalles_gastos WHERE gasto_id = :gasto_id)";
    $conexion_trans = $this->get_conex()->prepare($sql_transacciones);
    $conexion_trans->bindParam(":gasto_id", $this->gasto_id);
    $resultado_trans = $conexion_trans->execute();

    // 2. ELIMINAR FINALMENTE LOS REGISTROS DE DETALLES_GASTOS
    $sql_detalles = "DELETE FROM detalles_gastos WHERE gasto_id = :gasto_id";
    $conexion_det = $this->get_conex()->prepare($sql_detalles);
    $conexion_det->bindParam(":gasto_id", $this->gasto_id);
    $resultado_det = $conexion_det->execute();


    if ($resultado_trans && $resultado_det) {
        // 3. REGISTRAR EN BITÁCORA Y DEVOLVER ÉXITO
        $this->registrar_bitacora(ELIMINAR, GESTIONAR_GASTOS, "Eliminados todos los detalles del Gasto ID: " . $this->gasto_id);
        return ["estatus" => true, "mensaje" => "OK"];
    } else {
        return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar eliminar los detalles del gasto."];
    }
}

    public function lastId()
    {
        //$this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_detalle_gasto) as last_id FROM detalles_gastos";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        //$this->cambiar_db_negocio();

        if ($result) {
            return ["estatus" => true, "mensaje" => $datos["last_id"]];
        } else {
            return ["estatus" => false, "mensaje" => "Error en la consulta"];
        }
    }

    public function consultar_detalles_por_gasto()
    {
        $sql = "SELECT
            dg.*,
            bt.referencia,
            bt.imagen,
            b.id_banco,
            b.nombre_banco
        FROM detalles_gastos dg
        LEFT JOIN banco_transacciones bt ON bt.detalle_gasto_id = dg.id_detalle_gasto
        LEFT JOIN bancos b ON bt.banco_id = b.id_banco
        WHERE dg.gasto_id = :gasto_id
        ORDER BY dg.fecha DESC";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":gasto_id", $this->gasto_id);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                $this->registrar_bitacora(CONSULTAR, GESTIONAR_GASTOS, "DETALLES DE GASTO ID " . $this->gasto_id);
                return $datos;
            } else {
                return ["estatus" => false, "mensaje" => "Error al consultar los detalles del gasto."];
            }
    }
        public function obtener_imagen_actual()
    {
        $sql = "SELECT imagen FROM banco_transacciones WHERE detalle_gasto_id = :id_detalle_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_detalle_gasto", $this->id_detalle_gasto);
        $conexion->execute();

        $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado["imagen"] : null;
    }




}