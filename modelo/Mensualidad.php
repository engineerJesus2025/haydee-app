<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
use PDO;

class Mensualidad extends Conexion
{
    private $id_mensualidad;
    private $monto;
    private $tasa_dolar;
    private $mes;
    private $anio;
    private $apartamento_id;
    private $porcentaje_interes;
    private $limite_mensualidad;

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

    public function set_porcentaje_interes($porcentaje_interes)
    {
        $this->porcentaje_interes = $porcentaje_interes;
    }

    public function get_porcentaje_interes()
    {
        return $this->porcentaje_interes;
    }

    public function set_limite_mensualidad($limite_mensualidad)
    {
        $this->limite_mensualidad = $limite_mensualidad;
    }

    public function get_limite_mensualidad()
    {
        return $this->limite_mensualidad;
    }

    public function realizar_consulta($accion){
        switch ($accion) {
            case 'verificarMeses':
                $respuesta = $this->verificarMeses();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultarPorMeses':
                $respuesta = $this->consultarPorMeses();

                if ($respuesta["resultado"]) {                    
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }

            case 'consultar_mensualidad_apartamentos':
                $validaciones = $this->validarMesAnio();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->consultar_mensualidad_apartamentos();
                
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

                if ($respuesta["resultado"]) {
                    return ["estatus"=>true,"mensaje"=>"OK","lastId"=>$respuesta["lastId"]];
                } else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar esta Mensualidad"];
                }

            case 'editar':
                $validaciones = $this->validarDatos('editar');
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->editar();

                if ($respuesta) {
                    return ["estatus"=>true,"mensaje"=>"OK"];
                }
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar esta Mensualidad"];
                }

            case 'eliminar':
                $validaciones = $this->validarMesAnio();
                if(!($validaciones["estatus"])){return $validaciones;}

                $respuesta = $this->eliminar();

                if ($respuesta) {
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

            case 'consultar_tasa_dolar_mensualidades':
                $respuesta = $this->consultar_tasa_dolar_mensualidades();
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
        $sql = "SELECT
                    GROUP_CONCAT(sub.ids) as ids,
                    GROUP_CONCAT(sub.ids_apartamentos) as ids_apartamentos,
                    SUM(sub.monto_individual) as monto,
                    sub.tasa_dolar,
                    sub.mes,
                    sub.anio,    
                    SUM(LEAST(sub.monto_individual, COALESCE(sub.total_pagado_bruto, 0))) as pagado,
                    sub.porcentaje_interes,
                    sub.limite_mensualidad
                FROM (
                    SELECT
                        m.id_mensualidad as ids,
                        m.apartamento_id as ids_apartamentos,
                        m.monto as monto_individual,
                        m.tasa_dolar,
                        m.mes,
                        m.anio,
                        m.porcentaje_interes,
                        m.limite_mensualidad, 
                        (SELECT SUM(detalles_pagos.monto)
                         FROM detalles_pagos
                         INNER JOIN pagos_mensualidad ON detalles_pagos.id_detalle_pago = pagos_mensualidad.detalle_pago_id
                         WHERE pagos_mensualidad.mensualidad_id = m.id_mensualidad) as total_pagado_bruto
                    FROM mensualidad m
                ) as sub
                GROUP BY sub.mes, sub.anio;";

        $conexion = $this->get_conex()->prepare($sql);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_mensualidad_apartamentos()
    {
        $mes_entero = intval($this->mes);
        $anio_entero = intval($this->anio);
        $sql = "SELECT id_mensualidad, id_apartamento , mensualidad.mes as mes, mensualidad.anio as anio, apartamentos.nro_apartamento as nro_apartamento, habitantes.nombre as nombre, habitantes.apellido as apellido, mensualidad.monto as monto, mensualidad.tasa_dolar as tasa_dolar, 
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
        $sql = "INSERT INTO mensualidad(
        monto,
        tasa_dolar,
        mes,
        anio,
        apartamento_id,
        porcentaje_interes,
        limite_mensualidad) VALUES (
        :monto,
        :tasa_dolar,
        :mes,
        :anio,
        :apartamento_id,
        :porcentaje_interes,
        :limite_mensualidad)";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":tasa_dolar", $this->tasa_dolar);
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);
        $conexion->bindParam(":apartamento_id", $this->apartamento_id);
        $conexion->bindParam(":porcentaje_interes", $this->porcentaje_interes);
        $conexion->bindParam(":limite_mensualidad", $this->limite_mensualidad);
        $result = $conexion->execute();

        $conexion = $this->get_conex();
        $res = $conexion->lastInsertId();

        return ["resultado"=>$result,"lastId"=>$res];
    }

    private function editar()
    {           
        $sql = "UPDATE mensualidad SET monto = :monto, tasa_dolar = :tasa_dolar, mes = :mes, anio = :anio, apartamento_id = :apartamento_id, porcentaje_interes=:porcentaje_interes, limite_mensualidad=:limite_mensualidad WHERE id_mensualidad = :id_mensualidad";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":monto", $this->monto);
        $conexion->bindParam(":tasa_dolar", $this->tasa_dolar);
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);
        $conexion->bindParam(":apartamento_id", $this->apartamento_id);
        $conexion->bindParam(":id_mensualidad", $this->id_mensualidad);
        $conexion->bindParam(":porcentaje_interes", $this->porcentaje_interes);
        $conexion->bindParam(":limite_mensualidad", $this->limite_mensualidad);
        $result = $conexion->execute();
  
        return $result;
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

        return $result;
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
                SUM(CASE WHEN saldo > 0.01 THEN saldo ELSE 0 END) AS valor,
                'morosos' as elemento
            FROM
                SaldosFinales
            UNION
            SELECT
                COUNT(CASE WHEN saldo <= 0.01 THEN 1 END) AS accion,
                SUM(CASE WHEN saldo <= 0.01 THEN pagado_individual ELSE 0 END) AS valor,
                'sin deuda' as elemento
            FROM
                SaldosFinales
                UNION
            SELECT 'total_egresos' as accion, SUM(detalles_gastos.monto) as valor, 'total egresos' as elemento FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE() 
            UNION 
            SELECT 'total_ingresos' as accion, SUM(detalles_pagos.monto) as valor, 'total ingresos' as elemento FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()";            
        // Que precioso es sql
        $conexion = $this->get_conex()->prepare($sql);         
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }

    private function consultar_meses_mensualidad()
    {
        $sql = "SELECT mes, anio FROM mensualidad GROUP BY anio, mes ORDER BY anio, mes DESC";            

        $conexion = $this->get_conex()->prepare($sql);         
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado"=>$result,"datos"=>$datos];
    }    

    private function consultar_tasa_dolar_mensualidades()
    {
        $sql = "SELECT MAX(mes) as mes, MAX(anio) as anio, MAX(mensualidad.tasa_dolar) as tasa_dolar FROM mensualidad WHERE anio = :anio && mes = :mes";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":mes", $this->mes);
        $conexion->bindParam(":anio", $this->anio);
        $result = $conexion->execute();
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);        
        
        // $datos["tasa_dolar"] = $this->obtenerTasaDolarAPI();

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

    private function validarDatos($consulta = "registrar")
    {           
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_mensualidad))) {return ["estatus"=>false,"mensaje"=>"El id de la Mensualidad requerida no se recibio correctamente"];}

            if (empty($this->id_mensualidad)) {return ["estatus"=>false,"mensaje"=>"El id de la Mensualidad requerida esta vacio"];}

            if(is_numeric($this->id_mensualidad)){
                if (!($this->validarClaveForanea("mensualidad","id_mensualidad",$this->id_mensualidad))) {
                    return ["estatus"=>false,"mensaje"=>"La mensualidad seleccionada no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id de la mensualidad tiene debe ser un valor numerico entero"];}
        }

        if (!(isset($this->monto) && isset($this->tasa_dolar) && isset($this->mes) && isset($this->anio) && isset($this->apartamento_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        if (empty($this->monto) || empty($this->tasa_dolar) || empty($this->mes) || empty($this->anio) || empty($this->apartamento_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}        

        if(!(is_string($this->monto)) || !(preg_match("/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/",$this->monto))){
            return ["estatus"=>false,"mensaje"=>"Uno de los 'montos' no posee un valor valido"];
        }

        if(!(is_string($this->tasa_dolar)) || !(preg_match("/^[0-9]{0,12}[,.]{0,1}[0-9]{0,2}$/",$this->tasa_dolar))){
            return ["estatus"=>false,"mensaje"=>"Uno de los 'tasa dolar' no posee un valor valido"];
        }

        if(!(is_string($this->mes)) || !(preg_match("/^[0-9]{1,2}$/",$this->mes))){
            return ["estatus"=>false,"mensaje"=>"Uno de los 'meses' no posee un valor valido"];
        }

        if(!(is_string($this->anio)) || !(preg_match("/^[0-9]{4}$/",$this->anio))){
            return ["estatus"=>false,"mensaje"=>"Uno de los 'años' no posee un valor valido"];
        }

        if(is_numeric($this->apartamento_id)){
            if (!($this->validarClaveForanea("apartamentos","id_apartamento",$this->apartamento_id))) {
                return ["estatus"=>false,"mensaje"=>"Uno de los Apartamentos seleccionados no existe"];
            }            
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El campo 'Apartamento' no posee un valor valido"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    private function validarMesAnio()
    {           
        if (!(isset($this->mes) && isset($this->anio))) {return ["estatus"=>false,"mensaje"=>"El mes o año no se recibio correctamente"];}

        if (empty($this->mes) || empty($this->anio)) {return ["estatus"=>false,"mensaje"=>"El mes o año se envio vacios"];}        

        if(!(is_string($this->mes)) || !(preg_match("/^[0-9]{1,2}$/",$this->mes))){
            return ["estatus"=>false,"mensaje"=>"El mes para la consulta no posee un valor valido"];
        }

        if(!(is_string($this->anio)) || !(preg_match("/^[0-9]{4}$/",$this->anio))){
            return ["estatus"=>false,"mensaje"=>"El año para la consulta no posee un valor valido"];
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