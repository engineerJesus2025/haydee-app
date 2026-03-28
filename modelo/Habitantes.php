<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Habitantes extends Conexion
{
    private $id_habitante;
    private $nombre;
    private $apellido;
    private $cedula;
    private $telefono;
    private $correo;
    private $fecha_nacimiento;
    private $sexo;
    private $activo;

    private $filtros_reporte = [];

    private $nuevo_apartamento_id;
    private $nuevo_tipo_vinculo;

    /**
     * Devuelve las reglas de validación según la operación solicitada.
     */
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_habitante' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'habitantes', 'campo' => 'id_habitante']
            ],
            'nombre' => [
                'regex' => '/^[A-Za-z ]{3,30}$/'
            ],
            'apellido' => [
                'regex' => '/^[A-Za-z ]{3,30}$/'
            ],
            'cedula' => [
                'regex' => '/^[VE]{1}[0-9]{7,8}$/',
                'unique' => ['tabla' => 'habitantes', 'campo' => 'cedula', 'exclude_field' => 'id_habitante']
            ],
            'telefono' => [
                'regex' => '/^\d{11}$/'
            ],
            'correo' => [
                'regex' => '/^[-A-Za-z0-9_.]{3,35}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/',
                'unique' => ['tabla' => 'habitantes', 'campo' => 'correo', 'exclude_field' => 'id_habitante']
            ],
            'fecha_nacimiento' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'sexo' => [
                'regex' => '/^(Masculino|Femenino)$/'
            ],
            'nuevo_tipo_vinculo' => [
                'regex' => '/^(Propietario|Inquilino|Habitante|Otro)$/'
            ],
            'apartamento_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
            ],
            'tipo_vinculo' => [
                'regex' => '/^(Propietario|Inquilino|Habitante|Otro)$/'
            ]
        ];

        // Mapeamos las operaciones que involucran a habitantes
        $camposPorOperacion = [
            'registrar_habitantes' => ['nombre', 'apellido', 'cedula', 'telefono', 'correo', 'fecha_nacimiento', 'sexo', 'apartamento_id', 'tipo_vinculo'],
            'modificar_habitantes'          => ['id_habitante', 'nombre', 'apellido', 'cedula', 'telefono', 'correo', 'fecha_nacimiento', 'sexo', 'apartamento_id', 'tipo_vinculo'],
            'eliminar_habitantes'           => ['id_habitante'],
            'consulta_especifica_habitante' => ['id_habitante']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // Getters y Setters
    public function set_id_habitante($id) { $this->id_habitante = $id; }
    public function get_id_habitante() { return $this->id_habitante; }
    public function set_nombre($nombre) { $this->nombre = $nombre; }
    public function get_nombre() { return $this->nombre; }
    public function set_apellido($apellido) { $this->apellido = $apellido; }
    public function get_apellido() { return $this->apellido; }
    public function set_cedula($cedula) { $this->cedula = $cedula; }
    public function get_cedula() { return $this->cedula; }
    public function set_telefono($tel) { $this->telefono = $tel; }
    public function get_telefono() { return $this->telefono; }
    public function set_correo($correo) { $this->correo = $correo; }
    public function get_correo() { return $this->correo; }
    public function set_fecha_nacimiento($fecha) { $this->fecha_nacimiento = $fecha; }
    public function get_fecha_nacimiento() { return $this->fecha_nacimiento; }
    public function set_sexo($sexo) { $this->sexo = $sexo; }
    public function get_sexo() { return $this->sexo; }
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function set_filtros_reporte($filtros) {
        $this->filtros_reporte = $filtros;
    }

    public function set_nuevo_apartamento_id($id) { $this->nuevo_apartamento_id = $id; }
    public function get_nuevo_apartamento_id() { return $this->nuevo_apartamento_id; }
    public function set_nuevo_tipo_vinculo($tipo) { $this->nuevo_tipo_vinculo = $tipo; }
    public function get_nuevo_tipo_vinculo() { return $this->nuevo_tipo_vinculo; }

    /**
     * Enruta la acción al método privado correspondiente.
     */
    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            return ['estatus' => false, 'mensaje' => "La acción '$accion' no está implementada."];
        }

        try {
            return $this->$metodo();
        } catch (\Exception $e) {
            error_log("Error en realizar_consulta ($accion): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Ocurrió un error interno en el servidor.'];
        }
    }

    // -----------------------------------------------------------------
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    /**
     * Consulta un habitante específico por ID.
     // SE USA EN EL MODULO
     */
    private function _consultar_habitante()
    {
        $sql = "SELECT 
                h.id_habitante,
                h.nombre,
                h.apellido,
                h.cedula,
                h.telefono,
                h.correo,
                h.fecha_nacimiento,
                h.sexo,
                a.nro_apartamento AS apartamento,
                a.gas,
                a.agua,
                a.alquilado,
                a.porcentaje_participacion,
                ha.tipo_vinculo,
                ha.apartamento_id
            FROM habitantes h
            LEFT JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
            LEFT JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
            WHERE h.id_habitante = :id_habitante AND h.activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_habitante', $this->id_habitante);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Habitante no encontrado'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_habitante: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el habitante'];
        }
    }

    /**
     * Registra un habitante y su relación con un apartamento en una sola transacción.
     // SE USA EN EL MODULO
     */
    private function _registrar_habitantes()
    {
        $pdo = $this->get_conex('negocio');
        try {
            // Validar regla de negocio estricta (Evitar 2 propietarios por error de concurrencia)
            if (!empty($this->nuevo_apartamento_id) && $this->nuevo_tipo_vinculo === 'Propietario') {
                $sqlCheck = "SELECT COUNT(*) FROM habitantes_apartamentos WHERE apartamento_id = :id AND tipo_vinculo = 'Propietario'";
                $stmtCheck = $pdo->prepare($sqlCheck);
                $stmtCheck->execute([':id' => $this->nuevo_apartamento_id]);
                if ($stmtCheck->fetchColumn() > 0) {
                    return ['estatus' => false, 'mensaje' => 'Este apartamento ya tiene un propietario asignado.'];
                }
            }

            $pdo->beginTransaction();

            //  Insertar habitante
            $sql = "INSERT INTO habitantes (nombre, apellido, cedula, telefono, correo, fecha_nacimiento, sexo)
                    VALUES (:nombre, :apellido, :cedula, :telefono, :correo, :fecha_nacimiento, :sexo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $this->nombre,
                ':apellido' => $this->apellido,
                ':cedula' => $this->cedula,
                ':telefono' => $this->telefono,
                ':correo' => $this->correo,
                ':fecha_nacimiento' => $this->fecha_nacimiento,
                ':sexo' => $this->sexo
            ]);
            $idHabitante = $pdo->lastInsertId();

            //  Insertar relación directamente en la tabla puente (Sin llamar a Apartamento.php)
            if (!empty($this->nuevo_apartamento_id)) {
                $sqlRel = "INSERT INTO habitantes_apartamentos (apartamento_id, habitante_id, tipo_vinculo)
                           VALUES (:aid, :hid, :tipo)";
                $stmtRel = $pdo->prepare($sqlRel);
                $stmtRel->execute([
                    ':aid' => $this->nuevo_apartamento_id,
                    ':hid' => $idHabitante,
                    ':tipo' => $this->nuevo_tipo_vinculo
                ]);
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Habitante registrado correctamente', 'lastId' => $idHabitante];
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _registrar_habitantes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el habitante.'];
        }
    }

    /**
     * Edita un habitante y sincroniza su vínculo usando el patrón "Sync" (Borrar y Recrear).
     // SE USA EN EL MODULO
     */
    private function _modificar_habitantes()
    {
        $pdo = $this->get_conex('negocio');
        try {
            // Validar regla de negocio estricta (Ignorando a sí mismo)
            if (!empty($this->nuevo_apartamento_id) && $this->nuevo_tipo_vinculo === 'Propietario') {
                $sqlCheck = "SELECT COUNT(*) FROM habitantes_apartamentos 
                             WHERE apartamento_id = :aid AND tipo_vinculo = 'Propietario' AND habitante_id != :hid";
                $stmtCheck = $pdo->prepare($sqlCheck);
                $stmtCheck->execute([':aid' => $this->nuevo_apartamento_id, ':hid' => $this->id_habitante]);
                if ($stmtCheck->fetchColumn() > 0) {
                    return ['estatus' => false, 'mensaje' => 'Este apartamento ya tiene otro propietario asignado.'];
                }
            }

            $pdo->beginTransaction();

            // Actualizar datos base del habitante
            $sql = "UPDATE habitantes SET 
                        nombre = :nombre, apellido = :apellido, cedula = :cedula, 
                        telefono = :telefono, correo = :correo, fecha_nacimiento = :fecha_nacimiento, sexo = :sexo
                    WHERE id_habitante = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $this->nombre,
                ':apellido' => $this->apellido,
                ':cedula' => $this->cedula,
                ':telefono' => $this->telefono,
                ':correo' => $this->correo,
                ':fecha_nacimiento' => $this->fecha_nacimiento,
                ':sexo' => $this->sexo,
                ':id' => $this->id_habitante
            ]);

            // Patrón Sync: Borramos cualquier vínculo anterior que tuviera este habitante
            $sqlDel = "DELETE FROM habitantes_apartamentos WHERE habitante_id = :hid";
            $stmtDel = $pdo->prepare($sqlDel);
            $stmtDel->execute([':hid' => $this->id_habitante]);

            // Si el formulario envió un apartamento, creamos el nuevo vínculo
            if (!empty($this->nuevo_apartamento_id)) {
                $sqlRel = "INSERT INTO habitantes_apartamentos (apartamento_id, habitante_id, tipo_vinculo)
                           VALUES (:aid, :hid, :tipo)";
                $stmtRel = $pdo->prepare($sqlRel);
                $stmtRel->execute([
                    ':aid' => $this->nuevo_apartamento_id,
                    ':hid' => $this->id_habitante,
                    ':tipo' => $this->nuevo_tipo_vinculo
                ]);
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Habitante actualizado correctamente'];
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en _modificar_habitantes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al modificar el habitante.'];
        }
    }

    /**
     * Elimina un habitante (soft delete).
     // SE USA EN EL MODULO
     */
    private function _eliminar_habitantes()
    {
        $sql = "UPDATE habitantes SET activo = 0 WHERE id_habitante = :id_habitante";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_habitante', $this->id_habitante);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Habitante eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el habitante'];
        }
    }

    // -----------------------------------------------------------------
    // Métodos públicos auxiliares (reportes, etc.)
    // -----------------------------------------------------------------

    /**
     * Consulta personas solventes (propietarios sin deuda)
     * USADO EN REPORTES
     */
    private function _consultar_personas_solvencia()
    {
        $sql = "SELECT h.*, a.nro_apartamento 
                FROM habitantes h
                INNER JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE a.id_apartamento IN (
                    SELECT apartamentos.id_apartamento 
                    FROM mensualidad 
                    INNER JOIN apartamentos ON mensualidad.apartamento_id = apartamentos.id_apartamento
                    WHERE (SELECT SUM(mensualidad.monto) 
                           FROM mensualidad 
                           WHERE mensualidad.apartamento_id = apartamentos.id_apartamento) 
                          <= (SELECT SUM(detalles_pagos.monto) 
                              FROM detalles_pagos 
                              INNER JOIN pagos_mensualidad ON pagos_mensualidad.detalle_pago_id = detalles_pagos.id_detalle_pago 
                              INNER JOIN mensualidad ON mensualidad.id_mensualidad = pagos_mensualidad.mensualidad_id 
                              WHERE mensualidad.apartamento_id = apartamentos.id_apartamento)
                )";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_personas_solvencia: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar personas solventes'];
        }
    }

    /**
     * Consulta todos los propietarios con sus apartamentos
     * USADO EN REPORTES
     */
    private function _consultar_propietarios()
    {
        $sql = "SELECT h.*, a.nro_apartamento 
                FROM habitantes h
                INNER JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE ha.tipo_vinculo = 'Propietario'";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_propietarios: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar propietarios'];
        }
    }

    /**
     * Obtiene datos para reportes estadísticos de habitantes según filtros.
     * Los filtros se reciben a través de $this->filtros_reporte.
     * USADO EN REPORTES
     */
    private function _obtener_datos_habitantes()
    {
        $f = $this->filtros_reporte;
        $rango_edades = $f['rango_edades'] ?? 'todos';
        $edad_minima = $f['edad_minima'] ?? null;
        $edad_maxima = $f['edad_maxima'] ?? null;
        $tipo_residente = $f['tipo_residente'] ?? 'todos';
        $servicios = $f['servicios'] ?? [];
        
        // Variables de tiempo
        $filtro_tiempo = $f['filtro_tiempo'] ?? 'todo';
        $fecha_inicio = $f['fecha_inicio'] ?? '';
        $fecha_fin = $f['fecha_fin'] ?? '';

        if (is_string($servicios)) {
            $servicios = json_decode($servicios, true) ?? [];
        }

        $sql = "SELECT 
                    h.sexo, 
                    ha.tipo_vinculo, 
                    TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) AS edad
                FROM habitantes h
                JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE 1=1";

        $params = [];

        // 1. FILTRO DE TIEMPO (Fechas de registro)
        if ($filtro_tiempo !== 'todo') {
            switch ($filtro_tiempo) {
                case 'mes':
                    // Registrados este mes y este año
                    $sql .= " AND MONTH(h.fecha_registro) = MONTH(CURDATE()) AND YEAR(h.fecha_registro) = YEAR(CURDATE())";
                    break;
                case 'trimestre':
                    // Registrados en los últimos 3 meses
                    $sql .= " AND h.fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
                    break;
                case 'año':
                    // Registrados este año
                    $sql .= " AND YEAR(h.fecha_registro) = YEAR(CURDATE())";
                    break;
                case 'personalizado':
                    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                        $sql .= " AND DATE(h.fecha_registro) BETWEEN :fecha_inicio AND :fecha_fin";
                        $params[':fecha_inicio'] = $fecha_inicio;
                        $params[':fecha_fin'] = $fecha_fin;
                    }
                    break;
            }
        }

        // 2. FILTRO DE EDADES
        if ($rango_edades != 'todos') {
            switch ($rango_edades) {
                case 'jovenes':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 35";
                    break;
                case 'adultos':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) BETWEEN 36 AND 59";
                    break;
                case 'mayores':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) >= 60";
                    break;
                case 'personalizado':
                    if ($edad_minima !== null && $edad_maxima !== null) {
                        $sql .= " AND TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) BETWEEN :edad_min AND :edad_max";
                        $params[':edad_min'] = $edad_minima;
                        $params[':edad_max'] = $edad_maxima;
                    }
                    break;
            }
        }

        // 3. FILTRO DE TIPO DE RESIDENTE
        if ($tipo_residente == 'propietarios') {
            $sql .= " AND ha.tipo_vinculo = 'Propietario'";
        } elseif ($tipo_residente == 'arrendatarios') {
            $sql .= " AND ha.tipo_vinculo = 'Habitante'"; 
        }

        // 4. FILTRO DE SERVICIOS
        if (is_array($servicios)) {
            if (in_array('agua', $servicios)) {
                $sql .= " AND a.agua = 1";
            }
            if (in_array('gas', $servicios)) {
                $sql .= " AND a.gas = 1";
            }
        }

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute($params);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _obtener_datos_habitantes: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener datos de habitantes'];
        }
    }

}
?>