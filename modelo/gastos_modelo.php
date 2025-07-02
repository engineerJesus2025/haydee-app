<?php
require_once("modelo/conexion.php");

class Gastos extends Conexion
{
    private $id_gasto;
    private $fecha;
    private $monto;
    private $tipo;
    private $tasa_dolar;
    private $metodo_pago;
    private $descripcion_gasto;
    private $solicitud_id;
    private $tipo_gasto_id;
    private $caja_id;
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
    public function set_tipo($tipo)
    {
        $this->tipo = $tipo;
    }
    public function get_tipo()
    {
        return $this->tipo;
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

    public function set_proveedor_id($proveedor_id)
    {
        $this->proveedor_id = $proveedor_id;
    }
    public function get_proveedor_id()
    {
        return $this->proveedor_id;
    }

    public function set_descripcion_gasto($descripcion_gasto)
    {
        $this->descripcion_gasto = $descripcion_gasto;
    }
    public function get_descripcion_gasto()
    {
        return $this->descripcion_gasto;
    }

    public function set_solicitud_id($solicitud_id)
    {
        $this->solicitud_id = $solicitud_id;
    }
    public function get_solicitud_id()
    {
        return $this->solicitud_id;
    }

    public function set_tipo_gasto_id($tipo_gasto_id)
    {
        $this->tipo_gasto_id = $tipo_gasto_id;
    }
    public function get_tipo_gasto_id()
    {
        return $this->tipo_gasto_id;
    }

    public function set_caja_id($caja_id)
    {
        $this->caja_id = $caja_id;
    }
    public function get_caja_id()
    {
        return $this->caja_id;
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

    public function consultar_gasto_id()
    {
        $sql = "SELECT 
    g.*,
    tg.id_tipo_gasto,
    tg.nombre_tipo_gasto,
    sg.*,
    b.id_banco,
    b.nombre_banco,
    p.id_proveedor,
    p.nombre_proveedor,
    bt.referencia,
    bt.imagen
FROM gastos g
LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto
LEFT JOIN solicitudes_gasto sg ON g.solicitud_id = sg.id_solicitud
LEFT JOIN banco_transacciones bt ON g.id_gasto = bt.gasto_id
LEFT JOIN bancos b ON bt.banco_id = b.id_banco
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

    public function registrar()
    {
        $sql = "INSERT INTO gastos (fecha, monto, tipo, metodo_pago,  descripcion_gasto, tipo_gasto_id, solicitud_id, caja_id, proveedor_id) VALUES (:fecha, :monto, :tipo, :metodo_pago, :descripcion_gasto, :tipo_gasto_id, :solicitud_id, :caja_id, :proveedor_id)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":tipo", $this->tipo);
        $conexion->bindParam(":metodo_pago", $this->metodo_pago);
        $conexion->bindParam(":tipo_gasto_id", $this->tipo_gasto_id);
        $conexion->bindParam(":solicitud_id", $this->solicitud_id);
        $conexion->bindParam(":caja_id", $this->caja_id);
        $conexion->bindParam(":descripcion_gasto", $this->descripcion_gasto);
        $conexion->bindParam(":proveedor_id", $this->proveedor_id);
        $result = $conexion->execute();
        if ($result) {
            return $this->get_conex()->lastInsertId(); // ← ESTE es el ID que necesitas
        } else {
            return false;
        }
    }

    public function editar_gasto()
    {
        $sql = "UPDATE gastos SET fecha = :fecha, monto = :monto, tipo = :tipo, metodo_pago = :metodo_pago, descripcion_gasto = :descripcion_gasto, tipo_gasto_id = :tipo_gasto_id, solicitud_id = :solicitud_id, caja_id = :caja_id, proveedor_id = :proveedor_id WHERE id_gasto = :id_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_gasto", $this->id_gasto);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":tipo", $this->tipo);
        $conexion->bindParam(":metodo_pago", $this->metodo_pago);
        $conexion->bindParam(":tipo_gasto_id", $this->tipo_gasto_id);
        $conexion->bindParam(":solicitud_id", $this->solicitud_id);
        $conexion->bindParam(":caja_id", $this->caja_id);
        $conexion->bindParam(":descripcion_gasto", $this->descripcion_gasto);
        $conexion->bindParam(":proveedor_id", $this->proveedor_id);
        $result = $conexion->execute();
        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar editar este gasto"];
        }
    }

    public function eliminar_gasto()
    {
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

    public function lastId()
    {
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

    public function validarClaveForanea($tabla, $nombreClave, $valor)
    {
        $sql = "SELECT * FROM $tabla WHERE $nombreClave =:valor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);
        return ($result) ? true : false;
    }


    public function registrar_banco_transacciones($referencia, $imagen, $banco_id, $gasto_id)
    {
        $sql = "INSERT INTO banco_transacciones (referencia, imagen, banco_id, gasto_id) VALUES (:referencia, :imagen, :banco_id, :gasto_id)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":referencia", $referencia);
        $conexion->bindParam(":imagen", $imagen);
        $conexion->bindParam(":banco_id", $banco_id);
        $conexion->bindParam(":gasto_id", $gasto_id);
        $result = $conexion->execute();
        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar este gasto"];
        }
    }

    public function consultar_banco_transacciones($referencia, $imagen, $banco_id, $gasto_id)
    {
        $sql = "SELECT id_banco_transaccion FROM banco_transacciones WHERE referencia = :referencia AND imagen = :imagen AND banco_id = :banco_id AND gasto_id = :gasto_id";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":referencia", $referencia);
        $conexion->bindParam(":imagen", $imagen);
        $conexion->bindParam(":banco_id", $banco_id);
        $conexion->bindParam(":gasto_id", $gasto_id);
        $conexion->execute();
        return $conexion->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminar_banco_transacciones()
    {
        $sql = "DELETE FROM banco_transacciones WHERE gasto_id = :gasto_id";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":gasto_id", $this->id_gasto);
        $result = $conexion->execute();

        if ($result) {
            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al eliminar la transacción bancaria"];
        }
    }

    public function listar_gastos_mes()
    {
        //Cambie la consulta y ahora solo usa el propio gasto
        $sql = "SELECT g.fecha , MONTH(g.fecha) as mes, YEAR(g.fecha) as anio
            FROM gastos g     
            GROUP BY MONTH(g.fecha), YEAR(g.fecha)
            ORDER BY YEAR(g.fecha) DESC,  MONTH(g.fecha) DESC";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->execute();
        return $conexion->fetchAll(PDO::FETCH_ASSOC);
    }



    public function filtrar_por_mes()
    {
        list($anio_buscar, $mes_buscar) = explode('-', $this->fecha);
        // esto de arriba saca el mes y año de la fecha y con eso buscamos
        $sql = "SELECT 
    g.*,
    tg.id_tipo_gasto,
    tg.nombre_tipo_gasto,
    sg.*,
    b.id_banco,
    b.nombre_banco,
    p.id_proveedor,
    p.nombre_proveedor,
    bt.referencia,
    bt.imagen
FROM gastos g
LEFT JOIN tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto
LEFT JOIN solicitudes_gasto sg ON g.solicitud_id = sg.id_solicitud
LEFT JOIN banco_transacciones bt ON g.id_gasto = bt.gasto_id
LEFT JOIN bancos b ON bt.banco_id = b.id_banco
LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
        WHERE MONTH(g.fecha) = :mes && YEAR(g.fecha) = :anio";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes_buscar);
        $conexion->bindParam(":anio", $anio_buscar);
        $conexion->execute();
        return $conexion->fetchAll(PDO::FETCH_ASSOC);
    }

    public function total_por_metodo_pago()
    {
        list($anio_buscar, $mes_buscar) = explode('-', $this->fecha);
        $sql = "SELECT metodo_pago, SUM(monto) as total FROM gastos WHERE MONTH(gastos.fecha) = :mes && YEAR(gastos.fecha) = :anio GROUP BY metodo_pago";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes_buscar);
        $conexion->bindParam(":anio", $anio_buscar);
        $conexion->execute();
        return $conexion->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_imagen_actual()
    {
        $sql = "SELECT imagen FROM banco_transacciones WHERE gasto_id = :id_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_gasto", $this->id_gasto);
        $conexion->execute();

        $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado["imagen"] : null;
    }


    public function consultarCajaActual($fecha)
    {
        $sql = "SELECT 
        id_caja_chica FROM caja_chica 
        WHERE MONTH(fecha_apertura) = :mes 
        AND YEAR(fecha_apertura) = :anio 
        AND estado = 'Abierta'
        LIMIT 1";

        $mes = date('m', strtotime($fecha)); // Extrae el mes de la fecha proporcionada
        $anio = date('Y', strtotime($fecha)); // Extrae el año de la fecha proporcionada

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes);
        $conexion->bindParam(":anio", $anio);

        $resultado = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($resultado && $datos) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "No hay caja chica abierta para este mes"];
        }

    }



















    //Metodo para el reporte estadistico
    public function obtenerIngresosYEgresos($fecha_inicio, $fecha_fin, $balance, $metodo_pago, $tipo_gasto)
    {
        $sql = '';
        if ($balance != "Todos") {
            if ($balance == "Egresos") {
                $sql .= "SELECT SUM(gastos.monto) as monto, gastos.fecha, 'Egreso' as balance, gastos.metodo_pago, gastos.tipo_gasto FROM gastos WHERE gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin";
            } else if ($balance == "Ingresos") {
                $sql .= "SELECT SUM(pagos.monto) as monto, pagos.fecha, 'Ingreso' as balance, pagos.metodo_pago, 'No tiene' as tipo_gasto FROM pagos WHERE pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin";
                if ($tipo_gasto != "Todos") {
                    if ($tipo_gasto == "Fijo") {
                        $sql .= " && gastos.tipo_gasto = 'fijo'";
                    } else if ($tipo_gasto == "Variable") {
                        $sql .= " && gastos.tipo_gasto = 'variable'";
                    }
                }
            }
            if ($metodo_pago != "Todos") {
                if ($metodo_pago == "Pago Movil") {
                    $sql .= " && metodo_pago = 'pago_movil'";
                } else if ($metodo_pago == "Transferencia") {
                    $sql .= " && metodo_pago = 'transferencia'";
                } else if ($metodo_pago == "Efectivo") {
                    $sql .= " && metodo_pago = 'efectivo'";
                }
            }

            if ($balance == "Egresos") {
                $sql .= " GROUP BY MONTH(gastos.fecha)";
            } else if ($balance == "Ingresos") {
                $sql .= " GROUP BY MONTH(pagos.fecha)";
            }
        } else {
            if ($metodo_pago != "Todos") {
                if ($metodo_pago == "Pago Movil") {
                    $sql .= "SELECT SUM(pagos.monto) as monto, pagos.fecha, 'Ingreso' as balance, pagos.metodo_pago, 'No tiene' as tipo_gasto FROM pagos WHERE pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && metodo_pago = 'pago_movil' GROUP BY MONTH(pagos.fecha)
                UNION
                SELECT SUM(gastos.monto) as monto, gastos.fecha, 'Egreso' as balance, gastos.metodo_pago, gastos.tipo_gasto FROM gastos WHERE gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && metodo_pago = 'pago_movil'";
                } else if ($metodo_pago == "Transferencia") {
                    $sql .= "SELECT SUM(pagos.monto) as monto, pagos.fecha, 'Ingreso' as balance, pagos.metodo_pago, 'No tiene' as tipo_gasto FROM pagos WHERE pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && metodo_pago = 'transferencia' GROUP BY MONTH(pagos.fecha)
                UNION
                SELECT SUM(gastos.monto) as monto, gastos.fecha, 'Egreso' as balance, gastos.metodo_pago, gastos.tipo_gasto FROM gastos WHERE gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && metodo_pago = 'transferencia'";
                } else if ($metodo_pago == "Efectivo") {
                    $sql .= "SELECT SUM(pagos.monto) as monto, pagos.fecha, 'Ingreso' as balance, pagos.metodo_pago, 'No tiene' as tipo_gasto FROM pagos WHERE pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && metodo_pago = 'efectivo' GROUP BY MONTH(pagos.fecha)
                UNION
                SELECT SUM(gastos.monto) as monto, gastos.fecha, 'Egreso' as balance, gastos.metodo_pago, gastos.tipo_gasto FROM gastos WHERE gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && metodo_pago = 'efectivo'";
                }
                if ($tipo_gasto != "Todos") {
                    if ($tipo_gasto == "Fijo") {
                        $sql .= " && gastos.tipo_gasto = 'fijo'";
                    } else if ($tipo_gasto == "Variable") {
                        $sql .= " && gastos.tipo_gasto = 'variable'";
                    }
                }
                $sql .= " GROUP BY MONTH(gastos.fecha)";
            } else {
                $sql .= "SELECT SUM(pagos.monto) as monto, pagos.fecha, 'Ingreso' as balance, pagos.metodo_pago, 'No tiene' as tipo_gasto FROM pagos WHERE pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin GROUP BY MONTH(pagos.fecha)
                UNION
                SELECT SUM(gastos.monto) as monto, gastos.fecha, 'Egreso' as balance, gastos.metodo_pago, gastos.tipo_gasto FROM gastos WHERE gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

                if ($tipo_gasto != "Todos") {
                    if ($tipo_gasto == "Fijo") {
                        $sql .= " && gastos.tipo_gasto = 'fijo'";
                    } else if ($tipo_gasto == "Variable") {
                        $sql .= " && gastos.tipo_gasto = 'variable'";
                    }
                }
                $sql .= " GROUP BY MONTH(gastos.fecha)";
            }
        }

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha_inicio", $fecha_inicio);
        $conexion->bindParam(":fecha_fin", $fecha_fin);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            return ["estatus" => true, "mensaje" => $datos];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_gastos()
    {
        list($dia, $mes, $anio) = explode('/', $this->fecha);
        $mes_entero = intval($mes);
        $anio_entero = intval($anio);

        $sql = 'SELECT tipo_gasto.nombre_tipo_gasto as nombre, SUM(gastos.monto) as monto, tipo_gasto.id_tipo_gasto as id_tipo_gasto, GROUP_CONCAT(gastos.id_gasto) as id_gastos_asociados FROM gastos INNER JOIN tipo_gasto ON gastos.tipo_gasto_id = tipo_gasto.id_tipo_gasto WHERE MONTH(gastos.fecha) = :mes && YEAR(gastos.fecha) = :anio GROUP BY tipo_gasto.nombre_tipo_gasto';
        // Que precioso es sql
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes_entero, PDO::PARAM_INT);
        $conexion->bindParam(":anio", $anio_entero, PDO::PARAM_INT);
        $result = $conexion->execute();

        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

}