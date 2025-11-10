<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
use PDO;

class DetallesGasto extends Conexion
{
    private $id_detalle_gasto;
    private $fecha;
    private $monto;
    private $monto_dolar;
    private $metodo_pago;
    private $descripcion_detalle_gasto;
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

    public function set_gasto_id($gasto_id)
    {
        $this->gasto_id = $gasto_id;
    }

        public function realizar_consulta($accion)
    {
        switch ($accion) {
            case 'consultar':
                return $this->consultar();
            
            case 'consultar_detalle_gasto':
                return $this->consultar_detalle_gasto();

            case 'registrar':
                $validacion = $this->validar_datos('registrar');
                if (!$validacion["estatus"]) {
                    return $validacion;
                }
                return $this->registrar_detalle_gasto();

            case 'editar':
                $validacion = $this->validar_datos('editar');
                if (!$validacion["estatus"]) {
                    return $validacion;
                }
                return $this->editar_detalle_gasto();

            case 'eliminar':
                 $validacion = $this->validar_datos('eliminar');
                if (!$validacion["estatus"]) {
                    return $validacion;
                }
                return $this->eliminar_detalle_gasto();

            case 'eliminar_por_gasto':
                return $this->eliminar_detalles_por_gasto();
            
            case 'consultar_por_gasto':
                return $this->consultar_detalles_por_gasto();

            case 'lastId':
                return $this->lastId();

            default:
                return ["estatus" => false, "mensaje" => "Acción no reconocida en Detalles_gasto"];
        }
    }


    public function consultar()
    {
        $sql = "SELECT * FROM detalles_gastos";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return $result ? $datos : ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];

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

        return ($result && $datos) ? $datos : ["estatus" => false, "mensaje" => "No se encontró el detalle del gasto."];

    }

    private function registrar_detalle_gasto()
    {
        // Se quita 'monto_dolar' de la consulta
        $sql = "INSERT INTO detalles_gastos (fecha, monto, metodo_pago, descripcion_detalle_gasto, gasto_id) 
                VALUES (:fecha, :monto, :metodo_pago, :descripcion_detalle_gasto, :gasto_id)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":metodo_pago", $this->metodo_pago);
        $conexion->bindParam(":descripcion_detalle_gasto", $this->descripcion_detalle_gasto);
        $conexion->bindParam(":gasto_id", $this->gasto_id);
        $result = $conexion->execute();


        if ($result) {
            $id_ultimo = $this->lastId();//obtenemos el ultimo id
            $this->set_id_detalle_gasto($id_ultimo["mensaje"]);

            //$this->registrar_notificacion();
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar este detalle pago"];
        }
    }

    private function editar_detalle_gasto()
    {
        $sql = "UPDATE detalles_gastos SET fecha = :fecha, monto = :monto, metodo_pago = :metodo_pago, descripcion_detalle_gasto = :descripcion_detalle_gasto, gasto_id = :gasto_id WHERE id_detalle_gasto = :id_detalle_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_detalle_gasto", $this->id_detalle_gasto);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":metodo_pago", $this->metodo_pago);
        $conexion->bindParam(":descripcion_detalle_gasto", $this->descripcion_detalle_gasto);
        $conexion->bindParam(":gasto_id", $this->gasto_id);
        $result = $conexion->execute();

        return $result ? ["estatus" => true, "mensaje" => "OK"] : ["estatus" => false, "mensaje" => "Error al editar el detalle"];

    }

    private function eliminar_detalle_gasto()
    {
        $sql = "DELETE FROM detalles_gastos WHERE id_detalle_gasto = :id_detalle_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_detalle_gasto", $this->id_detalle_gasto);
        $result = $conexion->execute();
        return $result ? ["estatus" => true, "mensaje" => "OK"] : ["estatus" => false, "mensaje" => "Error al eliminar el detalle"];

    }

    private function eliminar_detalles_por_gasto()
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
        return ["estatus" => true, "mensaje" => "OK"];
    } else {
        return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar eliminar los detalles del gasto."];
    }
}

    private function lastId()
    {
        //$this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_detalle_gasto) as last_id FROM detalles_gastos";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        return $result ? ["estatus" => true, "mensaje" => $datos["last_id"]] : ["estatus" => false, "mensaje" => "Error en la consulta"];

    }

    private function consultar_detalles_por_gasto()
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
        private function obtener_imagen_actual()
    {
        $sql = "SELECT imagen FROM banco_transacciones WHERE detalle_gasto_id = :id_detalle_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_detalle_gasto", $this->id_detalle_gasto);
        $conexion->execute();

        $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado["imagen"] : null;
    }

public function validar_datos($accion)
    {
        if ($accion == "editar" || $accion == "eliminar") {
            
            // Validación 1.1: ID vacío
            if (empty(trim($this->id_detalle_gasto))) {
                return ["estatus" => false, "mensaje" => "El id del detalle de gasto requerido esta vacio"];
            }

            // Validación 1.2: ID no es numérico
            if (!is_numeric($this->id_detalle_gasto)) {
                 return ["estatus"=>false, "mensaje"=>"El id del detalle de gasto debe ser un valor numerico"];
            }

            // Validación 1.3: ID no existe en la BD
            if (!($this->validarClaveForanea("detalles_gastos", "id_detalle_gasto", $this->id_detalle_gasto))) {
                return ["estatus" => false, "mensaje" => "El detalle de gasto seleccionado no existe"];
            }

            // Si la acción es 'eliminar' y pasó las validaciones de ID, terminamos.
            if ($accion == "eliminar") {
                return ["estatus" => true, "mensaje" => "OK"];
            }
        }
        
        if (empty($this->fecha)) {
            return ["estatus" => false, "mensaje" => "La fecha no puede estar vacía."];
        }
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $this->fecha)) {
            return ["estatus" => false, "mensaje" => "El formato de la fecha no es válido (YYYY-MM-DD)."];
        }
        if (!is_numeric($this->monto) || $this->monto <= 0) {
            return ["estatus" => false, "mensaje" => "El monto debe ser un número mayor a cero."];
        }
        if (empty(trim($this->metodo_pago))) {
            return ["estatus" => false, "mensaje" => "Debe seleccionar un método de pago."];
        }
        if (!in_array($this->metodo_pago, ['Efectivo', 'Pago Movil', 'Transferencia'])) {
            return ["estatus" => false, "mensaje" => "El método de pago no es válido."];
        }
        if (empty(trim($this->descripcion_detalle_gasto))) {
            return ["estatus" => false, "mensaje" => "La descripción del detalle no puede estar vacía."];
        }
        if (empty($this->gasto_id)) {
            return ["estatus" => false, "mensaje" => "El detalle debe estar asociado a un gasto principal."];
        }
        if (!$this->validarClaveForanea('gastos', 'id_gasto', $this->gasto_id)) {
            return ["estatus" => false, "mensaje" => "El gasto principal asociado no existe."];
        }

        return ["estatus" => true];
    }

     private function validarClaveForanea($tabla, $nombreClave, $valor)
    {
        $sql = "SELECT 1 FROM $tabla WHERE $nombreClave = :valor LIMIT 1";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        // fetch() devuelve false si no hay filas
        return $conexion->fetch() !== false;
    }


}