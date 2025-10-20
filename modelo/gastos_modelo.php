<?php
require_once("modelo/conexion.php");

class Gastos extends Conexion
{
    private $id_gasto;
    private $tipo;
    private $metodo_pago;
    private $descripcion_gasto;
    private $solicitud_id;
    private $tipo_gasto_id;
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

    public function set_tipo($tipo)
    {
        $this->tipo = $tipo;
    }
    public function get_tipo()
    {
        return $this->tipo;
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


    public function realizar_consulta($accion)
    {
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();
                if ($respuesta["resultado"]) {
                    $this->registrar_bitacora(CONSULTAR, GESTIONAR_GASTOS, "TODOS LOS GASTOS");
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
                }
            case 'consultar_gasto':
                return $this->consultar_gasto();


            case 'registrar':
                $validacion = $this->validar_datos('registrar');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si falla, retorna el mensaje de error
                }
                $respuesta = $this->registrar();
                if ($respuesta) {

                    $this->registrar_bitacora(REGISTRAR, GESTIONAR_GASTOS, "Gasto de " . $this->descripcion_gasto);
                    return ["estatus" => true, "mensaje" => "OK"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con el registro"];
                }


            case 'editar_gasto':
                $validacion = $this->validar_datos('editar_gasto');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si falla, retorna el mensaje de error
                }
                $respuesta = $this->editar_gasto();
                if ($respuesta) {
                    $this->registrar_bitacora(MODIFICAR, GESTIONAR_GASTOS, "Gasto de " . $this->descripcion_gasto);
                    return ["estatus" => true, "mensaje" => "OK"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la edición"];
                }


            case 'eliminar_gasto':
                return $this->eliminar_gasto();
            case 'lastId':
                return $this->lastId();
            default:
                return ["estatus" => false, "mensaje" => "Acción no reconocida"];
        }
    }


    private function consultar()
    {
        $sql = "SELECT
                g.*,
                SUM(dg.monto) AS monto_total,
                MAX(dg.fecha) AS ultima_fecha,
                tg.nombre_tipo_gasto,
                p.nombre_proveedor,
                (SELECT d.metodo_pago 
                 FROM detalles_gastos d 
                 WHERE d.gasto_id = g.id_gasto 
                 ORDER BY d.fecha DESC, d.id_detalle_gasto DESC 
                 LIMIT 1) AS metodo_pago_predominante
            FROM
                gastos g
            LEFT JOIN
                detalles_gastos dg ON g.id_gasto = dg.gasto_id
            LEFT JOIN
                tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto
            LEFT JOIN
                proveedores p ON g.proveedor_id = p.id_proveedor
            GROUP BY
                g.id_gasto
            ORDER BY
                ultima_fecha DESC
            ";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado" => $result, "datos" => $datos];
    }

    private function consultar_gasto()
    {
        $sql = "SELECT
                g.*,
                p.nombre_proveedor,
                sg.descripcion_necesidad,
                SUM(dg.monto) AS monto_total,
                MAX(dg.fecha) AS ultima_fecha,
                tg.nombre_tipo_gasto,
                (SELECT d.metodo_pago 
                 FROM detalles_gastos d 
                 WHERE d.gasto_id = g.id_gasto 
                 ORDER BY d.fecha DESC, d.id_detalle_gasto DESC 
                 LIMIT 1) AS metodo_pago_predominante
                
            FROM
                gastos g
            LEFT JOIN
                detalles_gastos dg ON g.id_gasto = dg.gasto_id
            LEFT JOIN
                proveedores p ON g.proveedor_id = p.id_proveedor
            LEFT JOIN
                solicitudes_gasto sg ON g.solicitud_id = sg.id_solicitud
            LEFT JOIN
                tipo_gasto tg ON g.tipo_gasto_id = tg.id_tipo_gasto
            WHERE
                g.id_gasto = :id_gasto
            GROUP BY
                g.id_gasto, p.id_proveedor, sg.id_solicitud
            ";

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

    private function registrar()
    {
        // Ya no se insertan metodo_pago, etc.
        $sql = "INSERT INTO gastos (tipo, descripcion_gasto, tipo_gasto_id, solicitud_id, proveedor_id) VALUES (:tipo, :descripcion_gasto, :tipo_gasto_id, :solicitud_id, :proveedor_id)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":tipo", $this->tipo);
        $conexion->bindParam(":descripcion_gasto", $this->descripcion_gasto);
        $conexion->bindParam(":tipo_gasto_id", $this->tipo_gasto_id);
        $conexion->bindParam(":solicitud_id", $this->solicitud_id);
        $conexion->bindParam(":proveedor_id", $this->proveedor_id);
        $result = $conexion->execute();
        return $result;

    }

    private function editar_gasto()
    {
        $sql = "UPDATE gastos SET tipo = :tipo, descripcion_gasto = :descripcion_gasto, tipo_gasto_id = :tipo_gasto_id, solicitud_id = :solicitud_id, proveedor_id = :proveedor_id WHERE id_gasto = :id_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_gasto", $this->id_gasto);
        $conexion->bindParam(":tipo", $this->tipo);
        $conexion->bindParam(":tipo_gasto_id", $this->tipo_gasto_id);
        $conexion->bindParam(":solicitud_id", $this->solicitud_id);
        $conexion->bindParam(":descripcion_gasto", $this->descripcion_gasto);
        $conexion->bindParam(":proveedor_id", $this->proveedor_id);
        $result = $conexion->execute();
        return $result;
    }

    private function eliminar_gasto()
    {
        // Obtener la lista de imágenes ANTES de borrar los registros de la BD.
        $nombres_imagenes = $this->obtener_nombres_imagenes_asociadas();

        // Eliminar el registro del gasto de la base de datos.
        $sql = "DELETE FROM gastos WHERE id_gasto = :id_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_gasto", $this->id_gasto);
        $resultado_db = $conexion->execute();

        // Si la BD se actualizó, borramos los archivos de la ruta.
        if ($resultado_db) {
            $ruta_base = "recursos/img/gastos/";

            foreach ($nombres_imagenes as $nombre_archivo) {
                $ruta_completa = $ruta_base . $nombre_archivo;

                // Verificamos que el archivo exista antes de intentar borrarlo para evitar errores.
                if (file_exists($ruta_completa)) {
                    unlink($ruta_completa);
                }
            }

            // $this->registrar_bitacora(ELIMINAR, GESTIONAR_GASTOS, "Gasto con ID " . $this->id_gasto);

            return ["estatus" => true, "mensaje" => "Gasto y comprobantes eliminados correctamente"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al eliminar el gasto de la base de datos"];
        }
    }

private function lastId()
    {
        $sql = "SELECT MAX(id_gasto) as last_id FROM gastos";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return $result ? ["estatus" => true, "mensaje" => $datos["last_id"]] : ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
    }

    private function validarClaveForanea($tabla, $nombreClave, $valor)
    {
        $sql = "SELECT 1 FROM $tabla WHERE $nombreClave = :valor LIMIT 1";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        return $conexion->fetch() !== false;
    }

    private function obtener_nombres_imagenes_asociadas()
    {
        $sql = "SELECT bt.imagen
            FROM banco_transacciones bt
            JOIN detalles_gastos dg ON bt.detalle_gasto_id = dg.id_detalle_gasto
            WHERE dg.gasto_id = :id_gasto AND bt.imagen IS NOT NULL AND bt.imagen != ''";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_gasto", $this->id_gasto);
        $conexion->execute();

        // Devuelve un array simple con los nombres de archivo: ['img1.jpg', 'img2.png']
        return $conexion->fetchAll(PDO::FETCH_COLUMN);
    }

    public function validar_datos($accion)
    {
        // 1. Validar campos obligatorios
        if (empty(trim($this->tipo))) {
            return ["estatus" => false, "mensaje" => "El campo 'Tipo' no puede estar vacío."];
        }
        if (empty(trim($this->descripcion_gasto))) {
            return ["estatus" => false, "mensaje" => "La descripción del gasto no puede estar vacía."];
        }
        if (empty($this->tipo_gasto_id)) {
            return ["estatus" => false, "mensaje" => "Debe seleccionar un tipo de gasto."];
        }

        // 2. Validar valores específicos
        if (!in_array($this->tipo, ['fijo', 'variable'])) {
            return ["estatus" => false, "mensaje" => "El valor para 'Tipo' no es válido."];
        }

        // 3. Validar claves foráneas obligatorias
        if (!$this->validarClaveForanea('tipo_gasto', 'id_tipo_gasto', $this->tipo_gasto_id)) {
            return ["estatus" => false, "mensaje" => "El tipo de gasto seleccionado no existe."];
        }

        if ($this->proveedor_id) {
            if (!$this->validarClaveForanea('proveedores', 'id_proveedor', $this->proveedor_id)) {
                return ["estatus" => false, "mensaje" => "El proveedor seleccionado no existe."];
            }
        }
        if ($this->solicitud_id) {
            if (!$this->validarClaveForanea('solicitudes_gasto', 'id_solicitud', $this->solicitud_id)) {
                return ["estatus" => false, "mensaje" => "La solicitud de gasto seleccionada no existe."];
            }
        }

        return ["estatus" => true]; // Si todo está bien
    }



    // METODO PARA EL REPORTE DE GASTOS (FRANCISCO)
    public function obtenerDatosReporteMensual($mes, $anio)
    {
        try {
            $db = $this->get_conex();

            //  1. OBTENER GASTOS FIJOS DEL MES (DESDE DETALLES)
            $sql_fijos = "SELECT 
                        g.descripcion_gasto, 
                        dg.monto 
                      FROM detalles_gastos dg
                      JOIN gastos g ON dg.gasto_id = g.id_gasto
                      WHERE MONTH(dg.fecha) = :mes AND YEAR(dg.fecha) = :anio AND g.tipo = 'Fijo'";

            $stmt_fijos = $db->prepare($sql_fijos);
            $stmt_fijos->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt_fijos->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt_fijos->execute();
            $gastos_fijos = $stmt_fijos->fetchAll(PDO::FETCH_ASSOC);

            //  2. OBTENER GASTOS VARIABLES DEL MES (EXCLUYENDO GAS LARA)
            $sql_variables = "SELECT 
                            g.descripcion_gasto, 
                            dg.monto 
                          FROM detalles_gastos dg
                          JOIN gastos g ON dg.gasto_id = g.id_gasto
                          LEFT JOIN proveedores p ON g.proveedor_id = p.id_proveedor
                          WHERE MONTH(dg.fecha) = :mes AND YEAR(dg.fecha) = :anio 
                          AND g.tipo = 'Variable' 
                          AND (p.nombre_proveedor != 'Gas Lara' OR p.nombre_proveedor IS NULL)";

            $stmt_variables = $db->prepare($sql_variables);
            $stmt_variables->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt_variables->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt_variables->execute();
            $gastos_variables = $stmt_variables->fetchAll(PDO::FETCH_ASSOC);

            //  3. OBTENER GASTO DE GAS LARA POR SEPARADO (SUMANDO SUS POSIBLES DETALLES)
            $sql_gas = "SELECT 
                        g.descripcion_gasto, 
                        SUM(dg.monto) as monto 
                    FROM detalles_gastos dg
                    JOIN gastos g ON dg.gasto_id = g.id_gasto
                    JOIN proveedores p ON g.proveedor_id = p.id_proveedor
                    WHERE MONTH(dg.fecha) = :mes AND YEAR(dg.fecha) = :anio 
                    AND p.nombre_proveedor = 'Gas Lara'
                    GROUP BY g.id_gasto";

            $stmt_gas = $db->prepare($sql_gas);
            $stmt_gas->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt_gas->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt_gas->execute();
            $gasto_gas = $stmt_gas->fetch(PDO::FETCH_ASSOC);

            // 4. OBTENER TASA DEL DÓLAR (sin cambios)
            $tasa_dolar = $this->obtenerTasaDolarAPI();

            // 5. OBTENER NÚMERO TOTAL DE APARTAMENTOS (sin cambios)
            $sql_aptos = "SELECT COUNT(*) as total FROM apartamentos";
            $stmt_aptos = $db->query($sql_aptos);
            $total_aptos = $stmt_aptos->fetch(PDO::FETCH_ASSOC)['total'];

            // Devolvemos todos los datos juntos
            return [
                'gastos_fijos' => $gastos_fijos,
                'gastos_variables' => $gastos_variables,
                'gasto_gas' => $gasto_gas ?: ['monto' => 0], // Si no hay gasto de gas, devuelve 0
                'tasa_dolar' => $tasa_dolar,
                'total_aptos' => $total_aptos
            ];

        } catch (PDOException $e) {
            // Manejo de errores
            error_log("Error en el reporte de gastos: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtiene la tasa del dólar desde la API pydolarve.org usando cURL.
     * @return float La tasa de cambio, o 0 si falla.
     */
    private function obtenerTasaDolarAPI()
    {
        // 1. La URL exacta de la API
        $apiUrl = "https://pydolarve.org/api/v2/tipo-cambio?currency=usd";

        // 2. Inicializar cURL
        $ch = curl_init();

        // 3. Configurar las opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve la respuesta como string
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);   // Tiempo de espera para la conexión
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // Tiempo de espera para la respuesta

        // 4. Ejecutar la petición
        $response = curl_exec($ch);

        // 5. Cerrar la conexión
        curl_close($ch);

        // 6. Verificar y procesar la respuesta
        if ($response === false) {
            // La petición cURL falló
            return 0;
        }

        // Decodificar la respuesta JSON
        $data = json_decode($response, true);

        // Verificar si el JSON es válido y si existe el campo "price"
        if (json_last_error() === JSON_ERROR_NONE && isset($data['price'])) {
            // ¡Éxito! Devolvemos el valor del campo "price"
            return (float) $data['price'];
        }

        // Si la respuesta no es la esperada, devuelve 0
        return 0;
    }

    public function listar_meses_con_gastos()
    {
        $sql = "SELECT DISTINCT YEAR(fecha) as anio, MONTH(fecha) as mes 
                FROM detalles_gastos 
                ORDER BY anio DESC, mes DESC";
        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->execute();
        return $conexion->fetchAll(PDO::FETCH_ASSOC);
    }

    //Metodo para el reporte estadistico (jesus)

    public function obtenerIngresosYEgresos($balance, $metodo_pago, $tipo_gasto, $filtro, $fecha_inicio = '', $fecha_fin = '')
    {
        $sql = '';
        if ($balance != "Todos") {
            if ($balance == "Egresos") {
                $sql .= "SELECT SUM(detalles_gastos.monto) as monto, detalles_gastos.fecha, 'Egreso' as balance, detalles_gastos.metodo_pago, gastos.tipo FROM gastos INNER JOIN detalles_gastos ON detalles_gastos.gasto_id = gastos.id_gasto WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

                if ($tipo_gasto != "Todos") {
                    $sql .= " && gastos.tipo = :tipo_gasto";
                }
                if ($metodo_pago != "Todos") {
                    $sql .= " && detalles_gastos.metodo_pago = :metodo_pago";
                }

                if ($filtro == "mes" || $filtro == "trimestre") {
                    $sql .= " GROUP BY YEAR(detalles_gastos.fecha), WEEK(detalles_gastos.fecha) ORDER BY YEAR(detalles_gastos.fecha), WEEK(detalles_gastos.fecha)";
                } else if ($filtro == "año" || $filtro == "semestre") {
                    $sql .= " GROUP BY MONTH(detalles_gastos.fecha) ORDER BY MONTH(detalles_gastos.fecha)";
                } else if ($filtro == "Otro") {
                    $sql .= " GROUP BY DAY(detalles_gastos.fecha) ORDER BY DAY(detalles_gastos.fecha)";
                }
            } else if ($balance == "Ingresos") {
                $sql .= "SELECT SUM(detalles_pagos.monto) as monto, detalles_pagos.fecha, 'Ingreso' as balance, detalles_pagos.tipo_pago, 'No Tiene' as tipo_gasto FROM detalles_pagos INNER JOIN pagos ON detalles_pagos.pago_id = pagos.id_pago WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

                if ($metodo_pago != "Todos") {
                    $sql .= " && tipo_pago = :metodo_pago";
                }

                if ($filtro == "mes" || $filtro == "trimestre") {
                    $sql .= " GROUP BY YEAR(detalles_pagos.fecha), WEEK(detalles_pagos.fecha) ORDER BY YEAR(detalles_pagos.fecha), WEEK(detalles_pagos.fecha)";
                } else if ($filtro == "año" || $filtro == "semestre") {
                    $sql .= " GROUP BY MONTH(detalles_pagos.fecha) ORDER BY MONTH(detalles_pagos.fecha)";
                } else if ($filtro == "Otro") {
                    $sql .= " GROUP BY DAY(detalles_pagos.fecha) ORDER BY DAY(detalles_pagos.fecha)";
                }
            }
        } else {
            $sql .= "SELECT SUM(detalles_gastos.monto) as monto, detalles_gastos.fecha, 'Egreso' as balance, detalles_gastos.metodo_pago, gastos.tipo FROM gastos INNER JOIN detalles_gastos ON detalles_gastos.gasto_id = gastos.id_gasto WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

            if ($tipo_gasto != "Todos") {
                $sql .= " && gastos.tipo = :tipo_gasto";
            }
            if ($metodo_pago != "Todos") {
                $sql .= " && detalles_gastos.metodo_pago = :metodo_pago";
            }
            if ($filtro == "mes" || $filtro == "trimestre") {
                $sql .= " GROUP BY YEAR(detalles_gastos.fecha), WEEK(detalles_gastos.fecha)";
            } else if ($filtro == "año" || $filtro == "semestre") {
                $sql .= " GROUP BY MONTH(detalles_gastos.fecha)";
            } else if ($filtro == "Otro") {
                $sql .= " GROUP BY DAY(detalles_gastos.fecha)";
            }

            $sql .= " UNION ";

            $sql .= "SELECT SUM(detalles_pagos.monto) as monto, detalles_pagos.fecha, 'Ingreso' as balance, detalles_pagos.tipo_pago, 'No Tiene' as tipo_gasto FROM detalles_pagos INNER JOIN pagos ON detalles_pagos.pago_id = pagos.id_pago WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

            if ($metodo_pago != "Todos") {
                $sql .= " && tipo_pago = :metodo_pago";
            }

            if ($filtro == "mes" || $filtro == "trimestre") {
                $sql .= " GROUP BY YEAR(detalles_pagos.fecha), WEEK(detalles_pagos.fecha) ORDER BY YEAR(fecha), WEEK(fecha)";
            } else if ($filtro == "año" || $filtro == "semestre") {
                $sql .= " GROUP BY MONTH(detalles_pagos.fecha) ORDER BY MONTH(fecha);";
            } else if ($filtro == "Otro") {
                $sql .= " GROUP BY DAY(detalles_pagos.fecha) ORDER BY DAY(fecha);";
            }
        }

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":fecha_inicio", $fecha_inicio);
        $conexion->bindParam(":fecha_fin", $fecha_fin);

        if ($tipo_gasto != "Todos" && $balance != "Ingresos") {
            $conexion->bindParam(":tipo_gasto", $tipo_gasto);
        }
        if ($metodo_pago != "Todos") {
            $conexion->bindParam(":metodo_pago", $metodo_pago);
        }

        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            return ["estatus" => true, "mensaje" => $datos, "sql" => $sql];
        } else {
            return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
        }
    }

    public function estadisticasIngresosYEgresos($balance, $metodo_pago, $tipo_gasto, $filtro, $fecha_inicio = '', $fecha_fin = '')
    {
        $sql = '';
        if ($balance != "Todos") {
            if ($balance == "Egresos") {
                $sql .= "SELECT SUM(detalles_gastos.monto), 'total_gastos' as estadistica FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

                $sql .= " UNION ";

                if ($metodo_pago != "Todos") {
                    $sql .= " SELECT SUM(detalles_gastos.monto), 'gastos_seleccionado' FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_gastos.metodo_pago = :metodo_pago";
                } else {
                    $sql .= " SELECT SUM(detalles_gastos.monto), 'gastos_efectivo' FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_gastos.metodo_pago = 'Efectivo'
                        UNION
                        SELECT SUM(detalles_gastos.monto), 'gastos_transferencia' FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_gastos.metodo_pago = 'Transferencia'
                        UNION
                        SELECT SUM(detalles_gastos.monto), 'gastos_pago_movil' FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_gastos.metodo_pago = 'Pago Movil'";
                }

                $sql .= " UNION ";

                if ($filtro == "mes" || $filtro == "trimestre") {
                    $sql .= " SELECT SUM(detalles_gastos.monto), CONCAT('gastos_semana_',WEEK(detalles_gastos.fecha)) FROM detalles_gastos WHERE WEEK(detalles_gastos.fecha) >= WEEK(:fecha_inicio) && WEEK(detalles_gastos.fecha) <= WEEK(:fecha_fin) GROUP BY WEEK(detalles_gastos.fecha)";
                } else {
                    $sql .= " SELECT SUM(detalles_gastos.monto), CONCAT('gastos_mes_',MONTH(detalles_gastos.fecha)) FROM detalles_gastos WHERE MONTH(detalles_gastos.fecha) >= MONTH(:fecha_inicio) && MONTH(detalles_gastos.fecha) <= MONTH(:fecha_fin) GROUP BY MONTH(detalles_gastos.fecha)";
                }
            } else if ($balance == "Ingresos") {
                $sql .= "SELECT SUM(detalles_pagos.monto), 'total_pagos' as estadistica FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

                $sql .= " UNION ";

                if ($metodo_pago != "Todos") {
                    $sql .= " SELECT SUM(detalles_pagos.monto), 'pagos_seleccionado' FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_pagos.tipo_pago = :metodo_pago";
                } else {
                    $sql .= " SELECT SUM(detalles_pagos.monto), 'pagos_efectivo' FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_pagos.tipo_pago = 'Efectivo'
                        UNION
                        SELECT SUM(detalles_pagos.monto), 'pagos_transferencia' FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_pagos.tipo_pago = 'Transferencia'
                        UNION
                        SELECT SUM(detalles_pagos.monto), 'pagos_pago_movil' FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_pagos.tipo_pago = 'Pago Movil'";
                }

                $sql .= " UNION ";

                if ($filtro == "mes" || $filtro == "trimestre") {
                    $sql .= " SELECT SUM(detalles_pagos.monto), CONCAT('pagos_semana_',WEEK(detalles_pagos.fecha)) FROM detalles_pagos WHERE WEEK(detalles_pagos.fecha) >= WEEK(:fecha_inicio) && WEEK(detalles_pagos.fecha) <= WEEK(:fecha_fin) GROUP BY WEEK(detalles_pagos.fecha)";
                } else {
                    $sql .= " SELECT SUM(detalles_pagos.monto), CONCAT('pagos_mes_',MONTH(detalles_pagos.fecha)) FROM detalles_pagos WHERE MONTH(detalles_pagos.fecha) >= MONTH(:fecha_inicio) && MONTH(detalles_pagos.fecha) <= MONTH(:fecha_fin) GROUP BY MONTH(detalles_pagos.fecha)";
                }
            }
        } else {
            $sql .= "SELECT SUM(detalles_gastos.monto), 'total_gastos' as estadistica FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

            $sql .= ' UNION ';

            if ($metodo_pago != "Todos") {
                $sql .= " SELECT SUM(detalles_gastos.monto), 'gastos_seleccionado' FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_gastos.metodo_pago = :metodo_pago";
            } else {
                $sql .= " SELECT SUM(detalles_gastos.monto), 'gastos_efectivo' FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_gastos.metodo_pago = 'Efectivo'
                    UNION
                    SELECT SUM(detalles_gastos.monto), 'gastos_transferencia' FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_gastos.metodo_pago = 'Transferencia'
                    UNION
                    SELECT SUM(detalles_gastos.monto), 'gastos_pago_movil' FROM detalles_gastos WHERE detalles_gastos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_gastos.metodo_pago = 'Pago Movil'";
            }

            $sql .= ' UNION ';

            if ($filtro == "mes" || $filtro == "trimestre") {
                $sql .= " SELECT SUM(detalles_gastos.monto), CONCAT('gastos_semana_',WEEK(detalles_gastos.fecha)) FROM detalles_gastos WHERE WEEK(detalles_gastos.fecha) >= WEEK(:fecha_inicio) && WEEK(detalles_gastos.fecha) <= WEEK(:fecha_fin) GROUP BY WEEK(detalles_gastos.fecha)";
            } else {
                $sql .= " SELECT SUM(detalles_gastos.monto), CONCAT('gastos_mes_',MONTH(detalles_gastos.fecha)) FROM detalles_gastos WHERE MONTH(detalles_gastos.fecha) >= MONTH(:fecha_inicio) && MONTH(detalles_gastos.fecha) <= MONTH(:fecha_fin) GROUP BY MONTH(detalles_gastos.fecha)";
            }

            $sql .= ' UNION ';

            $sql .= "SELECT SUM(detalles_pagos.monto), 'total_pagos' as estadistica FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin";

            $sql .= " UNION ";

            if ($metodo_pago != "Todos") {
                $sql .= " SELECT SUM(detalles_pagos.monto), 'pagos_seleccionado' FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_pagos.tipo_pago = :metodo_pago";
            } else {
                $sql .= " SELECT SUM(detalles_pagos.monto), 'pagos_efectivo' FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_pagos.tipo_pago = 'Efectivo'
                    UNION
                    SELECT SUM(detalles_pagos.monto), 'pagos_transferencia' FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_pagos.tipo_pago = 'Transferencia'
                    UNION
                    SELECT SUM(detalles_pagos.monto), 'pagos_pago_movil' FROM detalles_pagos WHERE detalles_pagos.fecha BETWEEN :fecha_inicio AND :fecha_fin && detalles_pagos.tipo_pago = 'Pago Movil'";
            }

            $sql .= ' UNION ';

            if ($filtro == "mes" || $filtro == "trimestre") {
                $sql .= " SELECT SUM(detalles_pagos.monto), CONCAT('pagos_semana_',WEEK(detalles_pagos.fecha)) FROM detalles_pagos WHERE WEEK(detalles_pagos.fecha) >= WEEK(:fecha_inicio) && WEEK(detalles_pagos.fecha) <= WEEK(:fecha_fin) GROUP BY WEEK(detalles_pagos.fecha)";
            } else {
                $sql .= " SELECT SUM(detalles_pagos.monto), CONCAT('pagos_mes_',MONTH(detalles_pagos.fecha)) FROM detalles_pagos WHERE MONTH(detalles_pagos.fecha) >= MONTH(:fecha_inicio) && MONTH(detalles_pagos.fecha) <= MONTH(:fecha_fin) GROUP BY MONTH(detalles_pagos.fecha)";
            }

        }
        // echo $sql;
        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":fecha_inicio", $fecha_inicio);
        $conexion->bindParam(":fecha_fin", $fecha_fin);

        if ($tipo_gasto != "Todos" && $balance != "Ingresos") {
            $conexion->bindParam(":tipo_gasto", $tipo_gasto);
        }
        if ($metodo_pago != "Todos") {
            $conexion->bindParam(":metodo_pago", $metodo_pago);
        }

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

        $sql = 'SELECT tipo_gasto.nombre_tipo_gasto as nombre, SUM(detalles_gastos.monto) as monto, tipo_gasto.id_tipo_gasto as id_tipo_gasto, GROUP_CONCAT(DISTINCT gastos.id_gasto) as id_gastos_asociados FROM gastos INNER JOIN tipo_gasto ON gastos.tipo_gasto_id = tipo_gasto.id_tipo_gasto INNER JOIN detalles_gastos ON detalles_gastos.gasto_id = gastos.id_gasto WHERE MONTH(detalles_gastos.fecha) = :mes && YEAR(detalles_gastos.fecha) = :anio GROUP BY tipo_gasto.id_tipo_gasto';
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