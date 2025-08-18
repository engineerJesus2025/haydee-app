<?php

require_once "modelo/conexion.php";

class Mensualidad extends Conexion
{
    private $id_mensualidad;
    private $monto;
    private $monto_dolar;
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

    public function set_monto_dolar($monto_dolar)
    {
        $this->monto_dolar = $monto_dolar;
    }

    public function get_monto_dolar()
    {
        return $this->monto_dolar;
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

    public function realizar_consulta($accion){
        switch ($accion) {
            case 'verificarMeses':
                $respuesta = $this->verificarMeses();
                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultarPorMeses':
                $respuesta = $this->consultarPorMeses();
                if ($respuesta["resultado"] == true) {
                    $this->registrar_bitacora(CONSULTAR, GESTIONAR_MENSUALIDAD, "TODOS LAS MENSUALIDADES");
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_mensualidad_apartamentos':
                $respuesta = $this->consultar_mensualidad_apartamentos();
                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'registrar':                
                $respuesta = $this->registrar();
                if ($respuesta["resultado"]) {
                    $this->registrar_bitacora(REGISTRAR, GESTIONAR_MENSUALIDAD, "Mensualidad del mes " . $this->mes . " del ". $this->anio);

                    return ["estatus"=>true,"mensaje"=>"OK","lastId"=>$respuesta["lastId"]];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta Mensualidad"];
                }

            case 'editar':
                $respuesta = $this->editar();

                if ($respuesta["resultado"]) {
                    if ($respuesta["fila_afectada"] < 1) {
                        return ["estatus"=>false,"mensaje"=>"No se modificó ningún registro"];
                    }
                    $this->registrar_bitacora(MODIFICAR, GESTIONAR_MENSUALIDAD, "Mensualidad del mes " . $this->mes . " del ". $this->anio);

                    return ["estatus"=>true,"mensaje"=>"OK"];
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta Mensualidad"];
                }

            case 'eliminar':
                $respuesta = $this->eliminar();

                if ($respuesta["resultado"]) {
                    if ($respuesta["fila_afectada"] < 1) {
                        return ["estatus"=>false,"mensaje"=>"No se eliminó ningún registro"];
                    }
                    $this->registrar_bitacora(ELIMINAR, GESTIONAR_MENSUALIDAD, "Mensualidad del mes " . $this->mes . " del ". $this->anio);

                    return ["estatus"=>true,"mensaje"=>"OK"];
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar esta Mensualidad"];
                }

            case 'consultar_estadisticas_inicio':
                $respuesta = $this->consultar_estadisticas_inicio();
                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_meses_mensualidad':
                $respuesta = $this->consultar_meses_mensualidad();
                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_monto_dolar_mensualidades':
                $respuesta = $this->consultar_monto_dolar_mensualidades();
                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_mensualidades_pendientes':
                $respuesta = $this->consultar_mensualidades_pendientes();
                if ($respuesta["resultado"] == true) {
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
    
    private function verificarMeses()
    {
        $sql = "SELECT MONTH(presupuesto.fecha) as mes_presupuesto, YEAR(presupuesto.fecha) as anio_presupuesto FROM presupuesto LEFT JOIN mensualidad ON CAST(CONCAT(mensualidad.anio, '-', mensualidad.mes, '-01') AS DATE) = presupuesto.fecha WHERE mensualidad.id_mensualidad IS NULL;";

        $conexion = $this->get_conex()->prepare($sql);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultarPorMeses()
    {
        $sql = "SELECT GROUP_CONCAT(id_mensualidad) as ids, GROUP_CONCAT(apartamento_id) as ids_apartamentos, SUM(monto) as monto, SUM(monto_dolar) as monto_dolar, mes, anio,
        SUM(COALESCE((SELECT SUM(detalles_pagos.monto) FROM detalles_pagos INNER JOIN pagos_mensualidad ON detalles_pagos.id_detalle_pago = pagos_mensualidad.detalle_pago_id WHERE pagos_mensualidad.mensualidad_id = mensualidad.id_mensualidad),0)) as pagado,
        SUM(COALESCE((SELECT SUM(detalles_pagos.monto_dolar) FROM detalles_pagos INNER JOIN pagos_mensualidad ON detalles_pagos.id_detalle_pago = pagos_mensualidad.detalle_pago_id WHERE pagos_mensualidad.mensualidad_id = mensualidad.id_mensualidad),0)) as pagado_dolar
         FROM mensualidad GROUP BY mes, anio";

        $conexion = $this->get_conex()->prepare($sql);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_mensualidad_apartamentos()
    {
        $mes_entero = intval($this->mes);
        $anio_entero = intval($this->anio);
        $sql = "SELECT id_mensualidad, id_apartamento , mensualidad.mes as mes, mensualidad.anio as anio, apartamentos.nro_apartamento as nro_apartamento, habitantes.nombre as nombre, habitantes.apellido as apellido, mensualidad.monto as monto, mensualidad.monto_dolar as monto_dolar, 
            COALESCE((SELECT SUM(detalles_pagos.monto) FROM detalles_pagos INNER JOIN pagos_mensualidad ON detalles_pagos.id_detalle_pago = pagos_mensualidad.detalle_pago_id WHERE pagos_mensualidad.mensualidad_id = mensualidad.id_mensualidad),0) as pagado,
            COALESCE((SELECT SUM(detalles_pagos.monto_dolar) FROM detalles_pagos INNER JOIN pagos_mensualidad ON detalles_pagos.id_detalle_pago = pagos_mensualidad.detalle_pago_id WHERE pagos_mensualidad.mensualidad_id = mensualidad.id_mensualidad),0) as pagado_dolar 
            FROM mensualidad INNER JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento INNER JOIN habitantes_apartamentos ON habitantes_apartamentos.apartamento_id = apartamentos.id_apartamento INNER JOIN habitantes ON habitantes_apartamentos.habitante_id = habitantes.id_habitante WHERE mensualidad.mes = :mes && mensualidad.anio = :anio && habitantes_apartamentos.tipo_vinculo = 'Propietario'";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes_entero,PDO::PARAM_INT);
        $conexion->bindParam(":anio", $anio_entero,PDO::PARAM_INT);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function registrar()
    {           
        $sql = "INSERT INTO mensualidad(monto,monto_dolar,mes,anio,apartamento_id) VALUES (:monto,:monto_dolar,:mes,:anio,:apartamento_id)";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":monto_dolar", $this->monto_dolar);
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);
        $conexion->bindParam(":apartamento_id", $this->apartamento_id);
        $result = $conexion->execute();

        $conexion = $this->get_conex();//otra conexion para buscar el ultimo id
        $res = $conexion->lastInsertId();

        return ["resultado"=>$result,"lastId"=>$res];        
    }

    private function editar()
    {           
        $sql = "UPDATE mensualidad SET monto = :monto, monto_dolar = :monto_dolar, mes = :mes, anio = :anio, apartamento_id = :apartamento_id WHERE id_mensualidad = :id_mensualidad";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":monto_dolar", $this->monto_dolar);
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);
        $conexion->bindParam(":apartamento_id", $this->apartamento_id);
        $conexion->bindParam(":id_mensualidad", $this->id_mensualidad);
        $result = $conexion->execute();
        $filas_afectadas = $conexion->rowCount();
        
        return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
    }
    private function eliminar()
    {
        $mes_entero = intval($this->mes);
        $anio_entero = intval($this->anio);

        $sql = "DELETE FROM mensualidad WHERE mes = :mes && anio = :anio";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $mes_entero);
        $conexion->bindParam(":anio", $anio_entero);
        $result = $conexion->execute();
        $filas_afectadas = $conexion->rowCount();
        
        return ["resultado"=>$result,"fila_afectada"=>$filas_afectadas];
    }
    
    private function consultar_estadisticas_inicio()
    {
        $sql = "
            WITH TotalFacturado AS (
            SELECT
                apartamento_id,
                SUM(mensualidad.monto) AS monto_total_facturado
            FROM
                mensualidad
            GROUP BY
                apartamento_id
        ),
        TotalPagado AS (
            SELECT
                m.apartamento_id,
                SUM(dp.monto) AS monto_total_pagado
            FROM
                detalles_pagos dp
            JOIN
                pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
            JOIN
                mensualidad m ON pm.mensualidad_id = m.id_mensualidad
            GROUP BY
                m.apartamento_id
        ),
        SaldosFinales AS (
            SELECT
                f.apartamento_id,
                (COALESCE(f.monto_total_facturado, 0) - COALESCE(p.monto_total_pagado, 0)) AS saldo,
                COALESCE(p.monto_total_pagado, 0) AS pagado_individual
            FROM
                TotalFacturado f
            LEFT JOIN
                TotalPagado p ON f.apartamento_id = p.apartamento_id
            UNION
            SELECT
                p.apartamento_id,
                (COALESCE(f.monto_total_facturado, 0) - COALESCE(p.monto_total_pagado, 0)) AS saldo,
                COALESCE(p.monto_total_pagado, 0) AS pagado_individual
            FROM
                TotalFacturado f
            RIGHT JOIN
                TotalPagado p ON f.apartamento_id = p.apartamento_id
            WHERE
                f.apartamento_id IS NULL
        )
        SELECT
            COUNT(CASE WHEN saldo > 0.01 THEN 1 END) AS accion,
            SUM(CASE WHEN saldo > 0.01 THEN saldo ELSE 0 END) AS valor
        FROM
            SaldosFinales
        UNION
        SELECT
            COUNT(CASE WHEN saldo <= 0.01 THEN 1 END) AS accion,
            SUM(CASE WHEN saldo <= 0.01 THEN pagado_individual ELSE 0 END) AS valor
        FROM
            SaldosFinales
            UNION
        SELECT 'total_egresos' as accion, SUM(detalles_gastos.monto) as valor FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE() 
        UNION 
        SELECT 'total_ingresos' as accion, SUM(detalles_pagos.monto) as valor FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()";            
        // Que precioso es sql
        $conexion = $this->get_conex()->prepare($sql);         
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_meses_mensualidad()
    {
        $sql = "SELECT mes, anio FROM mensualidad GROUP BY anio, mes ORDER BY anio, mes DESC";            
        // Que precioso es sql
        $conexion = $this->get_conex()->prepare($sql);         
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }    

    private function consultar_monto_dolar_mensualidades()
    {
        $sql = "SELECT MAX(mes) as mes, MAX(anio) as anio, mensualidad.monto_dolar FROM mensualidad;";
        $conexion = $this->get_conex()->prepare($sql);         
        $result = $conexion->execute();
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);        
        
        $datos["tasa_dolar"] = $this->obtenerTasaDolarAPI();

        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_mensualidades_pendientes()
    {
        //Mis agradecimientos a google geminis
        $sql = "WITH FacturacionMensual AS (
                SELECT
                    apartamento_id,
                    anio,
                    mes,
                    SUM(monto) AS total_facturado
                FROM
                    mensualidad
                GROUP BY
                    apartamento_id, anio, mes
            ),
            PagosMensuales AS (
                SELECT
                    m.apartamento_id,
                    m.anio,
                    m.mes,
                    SUM(dp.monto) AS total_pagado
                FROM
                    detalles_pagos dp
                JOIN
                    pagos_mensualidad pm ON dp.id_detalle_pago = pm.detalle_pago_id
                JOIN
                    mensualidad m ON pm.mensualidad_id = m.id_mensualidad
                GROUP BY
                    m.apartamento_id, m.anio, m.mes
            ),
            BalanceDelMes AS (
                SELECT
                    f.apartamento_id,
                    f.anio,
                    f.mes,
                    (COALESCE(f.total_facturado, 0) - COALESCE(p.total_pagado, 0)) AS cambio_neto_mes
                FROM
                    FacturacionMensual f
                LEFT JOIN
                    PagosMensuales p ON f.apartamento_id = p.apartamento_id AND f.anio = p.anio AND f.mes = p.mes  
                UNION
                SELECT
                    p.apartamento_id,
                    p.anio,
                    p.mes,
                    (COALESCE(f.total_facturado, 0) - COALESCE(p.total_pagado, 0)) AS cambio_neto_mes
                FROM
                    FacturacionMensual f
                RIGHT JOIN
                    PagosMensuales p ON f.apartamento_id = p.apartamento_id AND f.anio = p.anio AND f.mes = p.mes
                WHERE
                    f.apartamento_id IS NULL
            )

            SELECT
                a.nro_apartamento,
                b.anio,
                b.mes,
                b.cambio_neto_mes,
                SUM(b.cambio_neto_mes) OVER (PARTITION BY b.apartamento_id ORDER BY b.anio, b.mes) AS deuda_acumulada
            FROM
                BalanceDelMes b
            JOIN
                apartamentos a ON b.apartamento_id = a.id_apartamento
            ORDER BY
                a.nro_apartamento, b.anio, b.mes;";
        $conexion = $this->get_conex()->prepare($sql);         
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function obtenerTasaDolarAPI()
    {
        $apiUrl = "https://pydolarve.org/api/v2/tipo-cambio?currency=usd";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        curl_close($ch);

        if ($response === false) {
            return 0;
        }

        $data = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['price'])) {
            return (float) $data['price'];
        }

        return 0;
    }

    //Back
}
?>