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

    // Reglas de validación centralizadas
    private $reglas = [
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
            'regex' => '/^\d{4}-\d{2}-\d{2}$/',
            'custom' => 'validarFecha'
        ],
        'sexo' => [
            'regex' => '/^(Masculino|Femenino)$/'  // Asumiendo que sexo es M o F, ajustar si hay más opciones
        ]
    ];

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
    // Método de validación centralizado
    // -----------------------------------------------------------------

    private function validar($campos, $contexto = [])
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) {
                return [
                    'estatus' => false,
                    'mensaje' => "No hay reglas de validación definidas para el campo '$campo'."
                ];
            }
            $regla = $this->reglas[$campo];

            $getter = 'get_' . $campo;
            if (!method_exists($this, $getter)) {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' no tiene un getter definido."
                ];
            }
            $valor = $this->$getter();

            // Requerido
            if ($valor === null) {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' es requerido y no se ha establecido."
                ];
            }
            if (is_string($valor) && trim($valor) === '') {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' no puede estar vacío."
                ];
            }

            // Validar con expresión regular
            if (isset($regla['regex'])) {
                if (!preg_match($regla['regex'], (string)$valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no tiene un formato válido."
                    ];
                }
            }

            // Validación personalizada
            if (isset($regla['custom']) && method_exists($this, $regla['custom'])) {
                if (!$this->{$regla['custom']}($valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no es válido."
                    ];
                }
            }

            // Validar existencia en otra tabla
            if (isset($regla['exists'])) {
                $tabla = $regla['exists']['tabla'];
                $campoFor = $regla['exists']['campo'] ?? $campo;
                if (!$this->existeEnTabla($tabla, $campoFor, $valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El valor del campo '$campo' no existe en la tabla $tabla."
                    ];
                }
            }

            // Validar unicidad
            if (isset($regla['unique'])) {
                $tabla = $regla['unique']['tabla'];
                $campoUnico = $regla['unique']['campo'] ?? $campo;
                $excludeField = $regla['unique']['exclude_field'] ?? null;
                $excludeValue = $contexto['exclude_id'] ?? null;
                if (!$this->esUnico($tabla, $campoUnico, $valor, $excludeField, $excludeValue)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El valor del campo '$campo' ya está registrado."
                    ];
                }
            }
        }
        return ['estatus' => true];
    }

    /**
     * Verifica si un valor existe en una tabla específica (usa BD negocio).
     */
    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEnTabla: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un valor es único (exceptuando un ID dado).
     */
    private function esUnico($tabla, $campo, $valor, $excludeField = null, $excludeValue = null)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        if ($excludeField && $excludeValue !== null) {
            $sql .= " AND $excludeField != :exclude_val";
        }
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            if ($excludeField && $excludeValue !== null) {
                $stmt->bindParam(':exclude_val', $excludeValue);
            }
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['total'] == 0;
        } catch (PDOException $e) {
            error_log("Error en esUnico: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validación personalizada para fecha.
     */
    private function validarFecha($fecha)
    {
        $valores = explode('-', $fecha);
        return count($valores) == 3 && checkdate((int)$valores[1], (int)$valores[2], (int)$valores[0]);
    }

    /**
     * Verifica si existe un habitante activo con el ID seteado.
     * @return array ['estatus' => bool, 'existe' => bool, 'mensaje' => string]
     */
    private function _existe_habitante()
    {
        $validacion = $this->validar(['id_habitante']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT 1 FROM habitantes WHERE id_habitante = :id AND activo = 1 LIMIT 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $this->id_habitante]);
            $existe = $stmt->fetchColumn() ? true : false;
            return ['estatus' => true, 'existe' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _existe_habitante: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar habitante'];
        }
    }

    // -----------------------------------------------------------------
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    /**
     * Verifica si existe un habitante por cédula (para validación rápida).
     */
    private function _validar()
    {
        $validacion = $this->validar(['cedula']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT id_habitante FROM habitantes WHERE cedula = :cedula AND activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':cedula', $this->cedula);
            $stmt->execute();
            $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
            return ['estatus' => true, 'existe' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _validar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar habitante'];
        }
    }

    /**
     * Verifica si existe un habitante por correo.
     */
    private function _verificar_correo()
    {
        $validacion = $this->validar(['correo']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT id_habitante FROM habitantes WHERE correo = :correo AND activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':correo', $this->correo);
            $stmt->execute();
            $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
            return ['estatus' => true, 'existe' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _verificar_correo: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar correo'];
        }
    }

    /**
     * Lista todos los habitantes con información de apartamento.
     */
    private function _consultar()
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
                    a.nro_apartamento,
                    ha.tipo_vinculo
                FROM habitantes h
                INNER JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE h.activo = 1
                ORDER BY h.id_habitante";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar habitantes'];
        }
    }

    /**
     * Consulta un habitante específico por ID.
     */
    private function _consultar_habitante()
    {
        $validacion = $this->validar(['id_habitante']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
     * Registra un nuevo habitante.
     */
    private function _registrar()
    {
        $campos = ['cedula', 'nombre', 'apellido', 'telefono', 'correo', 'fecha_nacimiento', 'sexo'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "INSERT INTO habitantes (nombre, apellido, cedula, telefono, correo, fecha_nacimiento, sexo)
                VALUES (:nombre, :apellido, :cedula, :telefono, :correo, :fecha_nacimiento, :sexo)";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':apellido', $this->apellido);
            $stmt->bindParam(':cedula', $this->cedula);
            $stmt->bindParam(':telefono', $this->telefono);
            $stmt->bindParam(':correo', $this->correo);
            $stmt->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
            $stmt->bindParam(':sexo', $this->sexo);
            $stmt->execute();
            $lastId = $this->get_conex('negocio')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Habitante registrado correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el habitante'];
        }
    }

    /**
     * Actualiza un habitante existente.
     */
    private function _editar()
    {
        $campos = ['id_habitante', 'cedula', 'nombre', 'apellido', 'telefono', 'correo', 'fecha_nacimiento', 'sexo'];
        $contexto = ['exclude_id' => $this->id_habitante];
        $validacion = $this->validar($campos, $contexto);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE habitantes SET
                    nombre = :nombre,
                    apellido = :apellido,
                    cedula = :cedula,
                    telefono = :telefono,
                    correo = :correo,
                    fecha_nacimiento = :fecha_nacimiento,
                    sexo = :sexo
                WHERE id_habitante = :id_habitante";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_habitante', $this->id_habitante);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':apellido', $this->apellido);
            $stmt->bindParam(':cedula', $this->cedula);
            $stmt->bindParam(':telefono', $this->telefono);
            $stmt->bindParam(':correo', $this->correo);
            $stmt->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
            $stmt->bindParam(':sexo', $this->sexo);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Habitante actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _editar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el habitante'];
        }
    }

    /**
     * Elimina un habitante (soft delete).
     */
    private function _eliminar()
    {
        $validacion = $this->validar(['id_habitante']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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

    /**
     * Último ID insertado.
     */
    private function _lastId()
    {
        $sql = "SELECT MAX(id_habitante) as last_id FROM habitantes";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _lastId: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener último ID'];
        }
    }

    // -----------------------------------------------------------------
    // Métodos públicos auxiliares (reportes, etc.)
    // -----------------------------------------------------------------

    /**
     * Consulta personas solventes (propietarios sin deuda)
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
     */
    private function _obtener_datos_habitantes()
    {
        $f = $this->filtros_reporte;
        $rango_edades = $f['rango_edades'] ?? 'todos';
        $edad_minima = $f['edad_minima'] ?? null;
        $edad_maxima = $f['edad_maxima'] ?? null;
        $tipo_residente = $f['tipo_residente'] ?? 'todos';
        $servicios = $f['servicios'] ?? [];

        $sql = "SELECT 
                    h.sexo, 
                    a.alquilado, 
                    TIMESTAMPDIFF(YEAR, h.fecha_nacimiento, CURDATE()) AS edad
                FROM habitantes h
                JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE 1=1";

        $params = [];

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

        if ($tipo_residente == 'propietarios') {
            $sql .= " AND a.alquilado = 0";
        } elseif ($tipo_residente == 'arrendatarios') {
            $sql .= " AND a.alquilado = 1";
        }

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