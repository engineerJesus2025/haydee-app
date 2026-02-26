<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class Apartamento extends Conexion
{
    // ====================================================================
    // PROPIEDADES
    // ====================================================================
    private $id_apartamento;
    private $nro_apartamento;
    private $porcentaje_participacion;
    private $gas;
    private $agua;
    private $alquilado;
    private $activo;

    // Propiedades para gestión de habitantes (tabla puente)
    private $habitante_id;
    private $tipo_vinculo;

    private $correo;

    // ====================================================================
    // REGLAS DE VALIDACIÓN (con opcionales, min, max)
    // ====================================================================
    private $reglas = [
        'id_apartamento' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
        ],
        'nro_apartamento' => [
            'regex' => '/^[0-9\-\b]{1,4}$/',
            'unique' => ['tabla' => 'apartamentos', 'campo' => 'nro_apartamento', 'exclude_field' => 'id_apartamento']
        ],
        'porcentaje_participacion' => [
            'regex' => '/^\d{1,2}(\.\d{1,2})?$/',
            'min' => 0,
            'max' => 100
        ],
        'gas' => [
            'regex' => '/^[12]$/',
            'opcional' => true
        ],
        'agua' => [
            'regex' => '/^[12]$/',
            'opcional' => true
        ],
        'alquilado' => [
            'regex' => '/^[12]$/',
            'opcional' => true
        ],
        'habitante_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'habitantes', 'campo' => 'id_habitante']
        ],
        'tipo_vinculo' => [
            'regex' => '/^(Propietario|Inquilino|Habitante|Otro)$/'
        ],
        'correo' => [
            'regex' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'exists' => ['tabla' => 'habitantes', 'campo' => 'correo']
        ]
    ];

    // ====================================================================
    // GETTERS Y SETTERS
    // ====================================================================
    public function set_id_apartamento($id) { $this->id_apartamento = $id; }
    public function get_id_apartamento() { return $this->id_apartamento; }
    public function set_nro_apartamento($n) { $this->nro_apartamento = $n; }
    public function get_nro_apartamento() { return $this->nro_apartamento; }
    public function set_porcentaje_participacion($p) { $this->porcentaje_participacion = $p; }
    public function get_porcentaje_participacion() { return $this->porcentaje_participacion; }
    public function set_gas($g) { $this->gas = $g; }
    public function get_gas() { return $this->gas; }
    public function set_agua($a) { $this->agua = $a; }
    public function get_agua() { return $this->agua; }
    public function set_alquilado($a) { $this->alquilado = $a; }
    public function get_alquilado() { return $this->alquilado; }
    public function set_activo($a) { $this->activo = $a; }
    public function get_activo() { return $this->activo; }

    public function set_habitante_id($id) { $this->habitante_id = $id; }
    public function get_habitante_id() { return $this->habitante_id; }
    public function set_tipo_vinculo($v) { $this->tipo_vinculo = $v; }
    public function get_tipo_vinculo() { return $this->tipo_vinculo; }

    public function set_correo($correo) { $this->correo = $correo; }
    public function get_correo() { return $this->correo; }

    // ====================================================================
    // ENRUTADOR CON MANEJO DE EXCEPCIONES
    // ====================================================================
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

    // ====================================================================
    // VALIDACIÓN CENTRALIZADA (con opcionales, min, max, unique)
    // ====================================================================
    private function validar($campos, $contexto = [])
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) {
                continue;
            }
            $regla = $this->reglas[$campo];
            $getter = 'get_' . $campo;
            if (!method_exists($this, $getter)) {
                return ['estatus' => false, 'mensaje' => "Error interno: getter no encontrado para $campo."];
            }
            $valor = $this->$getter();

            // Determinar si es opcional
            $opcional = isset($regla['opcional']) && $regla['opcional'] === true;

            // Si es opcional y el valor está vacío, saltamos validaciones
            if ($opcional && ($valor === null || (is_string($valor) && trim($valor) === ''))) {
                continue;
            }

            // Validar requerido (si no es opcional)
            if (!$opcional) {
                if ($valor === null) {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' es requerido y no se ha establecido."];
                }
                if (is_string($valor) && trim($valor) === '') {
                    return ['estatus' => false, 'mensaje' => "El campo '$campo' no puede estar vacío."];
                }
            }

            // Validar regex
            if (isset($regla['regex']) && !preg_match($regla['regex'], (string)$valor)) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' no tiene un formato válido."];
            }

            // Validar min
            if (isset($regla['min']) && $valor < $regla['min']) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' debe ser mayor o igual a " . $regla['min']];
            }

            // Validar max
            if (isset($regla['max']) && $valor > $regla['max']) {
                return ['estatus' => false, 'mensaje' => "El campo '$campo' debe ser menor o igual a " . $regla['max']];
            }

            // Validar existencia en otra tabla
            if (isset($regla['exists'])) {
                if (!$this->existeEnTabla($regla['exists']['tabla'], $regla['exists']['campo'], $valor)) {
                    return ['estatus' => false, 'mensaje' => "El valor del campo '$campo' no existe en la base de datos."];
                }
            }

            // Validar unicidad
            if (isset($regla['unique'])) {
                $tabla = $regla['unique']['tabla'];
                $campoUnico = $regla['unique']['campo'] ?? $campo;
                $excludeField = $regla['unique']['exclude_field'] ?? null;
                $excludeValue = $contexto['exclude_id'] ?? null;
                if (!$this->esUnico($tabla, $campoUnico, $valor, $excludeField, $excludeValue)) {
                    return ['estatus' => false, 'mensaje' => "El valor del campo '$campo' ya está registrado."];
                }
            }
        }
        return ['estatus' => true];
    }

    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor AND activo = 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':valor' => $valor]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEnTabla: " . $e->getMessage());
            return false;
        }
    }

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
            return $stmt->fetchColumn() == 0;
        } catch (PDOException $e) {
            error_log("Error en esUnico: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si ya existe un apartamento con el mismo número (activo).
     * Útil para validación AJAX.
     * @return array ['estatus' => bool, 'existe' => bool, 'mensaje' => string]
     */
    private function _validar()
    {
        $v = $this->validar(['nro_apartamento']);
        if (!$v['estatus']) {
            return $v;
        }

        $sql = "SELECT id_apartamento FROM apartamentos WHERE nro_apartamento = :nro AND activo = 1 LIMIT 1";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':nro' => $this->nro_apartamento]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
            return ['estatus' => true, 'existe' => $existe];
        } catch (PDOException $e) {
            error_log("Error en _validar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar número de apartamento'];
        }
    }

    /**
     * Verifica si es posible asignar un habitante con cierto vínculo al apartamento.
     * Por ahora solo verifica que no haya ya un propietario si se intenta asignar otro.
     * @return array ['estatus' => bool, 'existe' => bool, 'mensaje' => string]
     */
    private function _verificar_vinculo()
    {
        $v = $this->validar(['id_apartamento', 'tipo_vinculo']);
        if (!$v['estatus']) {
            return $v;
        }

        if ($this->tipo_vinculo === 'Propietario') {
            $sql = "SELECT COUNT(*) FROM habitantes_apartamentos WHERE apartamento_id = :id AND tipo_vinculo = 'Propietario'";
            try {
                $stmt = $this->get_conex('negocio')->prepare($sql);
                $stmt->execute([':id' => $this->id_apartamento]);
                $existe = $stmt->fetchColumn() > 0;
                if ($existe) {
                    return ['estatus' => true, 'existe' => true, 'mensaje' => 'Ya existe un propietario en este apartamento'];
                }
            } catch (PDOException $e) {
                error_log("Error en _verificar_vinculo: " . $e->getMessage());
                return ['estatus' => false, 'mensaje' => 'Error al verificar vínculo'];
            }
        }
        return ['estatus' => true, 'existe' => false];
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO: APARTAMENTOS
    // ====================================================================

    private function _registrar_apartamento()
    {
        $campos = ['nro_apartamento', 'porcentaje_participacion'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        // Los campos opcionales se validarán con sus reglas, pero si no vienen, se usarán los valores por defecto.
        $gas = $this->gas ?? 0;
        $agua = $this->agua ?? 0;
        $alquilado = $this->alquilado ?? 0;

        try {
            $sql = "INSERT INTO apartamentos (nro_apartamento, porcentaje_participacion, gas, agua, alquilado, activo) 
                    VALUES (:nro, :porc, :gas, :agua, :alq, 1)";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([
                ':nro'  => $this->nro_apartamento,
                ':porc' => $this->porcentaje_participacion,
                ':gas'  => $gas,
                ':agua' => $agua,
                ':alq'  => $alquilado
            ]);
            $lastId = $this->get_conex('negocio')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Apartamento registrado correctamente', 'id' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar_apartamento: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    private function _modificar_apartamento()
    {
        $campos = ['id_apartamento', 'nro_apartamento', 'porcentaje_participacion'];
        $contexto = ['exclude_id' => $this->id_apartamento];
        $validacion = $this->validar($campos, $contexto);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $gas = $this->gas ?? 0;
        $agua = $this->agua ?? 0;
        $alquilado = $this->alquilado ?? 0;

        try {
            $sql = "UPDATE apartamentos SET 
                        nro_apartamento = :nro,
                        porcentaje_participacion = :porc,
                        gas = :gas,
                        agua = :agua,
                        alquilado = :alq
                    WHERE id_apartamento = :id";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([
                ':nro'  => $this->nro_apartamento,
                ':porc' => $this->porcentaje_participacion,
                ':gas'  => $gas,
                ':agua' => $agua,
                ':alq'  => $alquilado,
                ':id'   => $this->id_apartamento
            ]);
            return ['estatus' => true, 'mensaje' => 'Apartamento actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar_apartamento: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    private function _eliminar_apartamento()
    {
        $validacion = $this->validar(['id_apartamento']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            $sql = "UPDATE apartamentos SET activo = 0 WHERE id_apartamento = :id";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':id' => $this->id_apartamento]);
            return ['estatus' => true, 'mensaje' => 'Apartamento eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_apartamento: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    // ====================================================================
    // LÓGICA DE NEGOCIO: ASIGNACIÓN DE HABITANTES
    // ====================================================================

    private function _asignar_habitante()
    {
        $campos = ['id_apartamento', 'habitante_id', 'tipo_vinculo'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        // Regla de negocio: solo un propietario por apartamento
        if ($this->tipo_vinculo === 'Propietario') {
            $sqlProp = "SELECT COUNT(*) FROM habitantes_apartamentos 
                        WHERE apartamento_id = :id AND tipo_vinculo = 'Propietario'";
            try {
                $stmtProp = $this->get_conex('negocio')->prepare($sqlProp);
                $stmtProp->execute([':id' => $this->id_apartamento]);
                if ($stmtProp->fetchColumn() > 0) {
                    return ['estatus' => false, 'mensaje' => 'Este apartamento ya tiene un propietario asignado.'];
                }
            } catch (PDOException $e) {
                error_log("Error al verificar propietario: " . $e->getMessage());
                return ['estatus' => false, 'mensaje' => 'Error al verificar disponibilidad.'];
            }
        }

        // Verificar que el habitante no esté ya asignado al mismo apartamento
        $sqlCheck = "SELECT COUNT(*) FROM habitantes_apartamentos 
                     WHERE apartamento_id = :aid AND habitante_id = :hid";
        try {
            $stmtCheck = $this->get_conex('negocio')->prepare($sqlCheck);
            $stmtCheck->execute([':aid' => $this->id_apartamento, ':hid' => $this->habitante_id]);
            if ($stmtCheck->fetchColumn() > 0) {
                return ['estatus' => false, 'mensaje' => 'El habitante ya está asignado a este apartamento.'];
            }
        } catch (PDOException $e) {
            error_log("Error al verificar duplicado: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al verificar asignación.'];
        }

        try {
            $sql = "INSERT INTO habitantes_apartamentos (apartamento_id, habitante_id, tipo_vinculo) 
                    VALUES (:aid, :hid, :tipo)";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([
                ':aid'  => $this->id_apartamento,
                ':hid'  => $this->habitante_id,
                ':tipo' => $this->tipo_vinculo
            ]);
            return ['estatus' => true, 'mensaje' => 'Habitante asignado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _asignar_habitante: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al asignar habitante: ' . $e->getMessage()];
        }
    }

    private function _desvincular_habitante()
    {
        $campos = ['id_apartamento', 'habitante_id'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            $sql = "DELETE FROM habitantes_apartamentos 
                    WHERE apartamento_id = :aid AND habitante_id = :hid";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':aid' => $this->id_apartamento, ':hid' => $this->habitante_id]);
            if ($stmt->rowCount() == 0) {
                return ['estatus' => false, 'mensaje' => 'No se encontró la vinculación especificada.'];
            }
            return ['estatus' => true, 'mensaje' => 'Habitante desvinculado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _desvincular_habitante: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al desvincular habitante: ' . $e->getMessage()];
        }
    }

    // ====================================================================
    // CONSULTAS
    // ====================================================================

    private function _consultar_listado()
    {
        try {
            $sql = "SELECT a.*, 
                           (SELECT CONCAT(h.nombre, ' ', h.apellido) 
                            FROM habitantes h 
                            JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id 
                            WHERE ha.apartamento_id = a.id_apartamento AND ha.tipo_vinculo = 'Propietario' 
                            LIMIT 1) as propietario
                    FROM apartamentos a 
                    WHERE a.activo = 1 
                    ORDER BY a.nro_apartamento";
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _consultar_listado: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar apartamentos'];
        }
    }

    private function _consultar_detalle_completo()
    {
        $validacion = $this->validar(['id_apartamento']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        try {
            // Datos del apartamento
            $sqlApto = "SELECT * FROM apartamentos WHERE id_apartamento = :id AND activo = 1";
            $stmtA = $this->get_conex('negocio')->prepare($sqlApto);
            $stmtA->execute([':id' => $this->id_apartamento]);
            $apto = $stmtA->fetch(PDO::FETCH_ASSOC);
            if (!$apto) {
                return ['estatus' => false, 'mensaje' => 'Apartamento no encontrado'];
            }

            // Habitantes asociados
            $sqlHab = "SELECT h.id_habitante, h.nombre, h.apellido, h.cedula, h.telefono, ha.tipo_vinculo, a.nro_apartamento
                       FROM habitantes h
                       JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                       JOIN apartamentos a ON a.id_apartamento = ha.apartamento_id
                       WHERE ha.apartamento_id = :id AND h.activo = 1";
            $stmtH = $this->get_conex('negocio')->prepare($sqlHab);
            $stmtH->execute([':id' => $this->id_apartamento]);
            $apto['habitantes'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

            return ['estatus' => true, 'datos' => $apto];
        } catch (PDOException $e) {
            error_log("Error en _consultar_detalle_completo: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar detalles del apartamento'];
        }
    }

    /**
     * Consulta los apartamentos activos con los campos necesarios para la tabla de asignación de mensualidades.
     * @return array
     */
    public function _consultar_apartamentos_mensualidad() {
        $sql = "SELECT id_apartamento, nro_apartamento, porcentaje_participacion, gas 
                FROM apartamentos 
                WHERE activo = 1 
                ORDER BY nro_apartamento";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en consultar_apartamentos_mensualidad: " . $e->getMessage());
            return [];
        }
    }

    private function _contar_activos()
    {
        try {
            $stmt = $this->get_conex('negocio')->query("SELECT COUNT(*) FROM apartamentos WHERE activo = 1");
            return ['estatus' => true, 'datos' => $stmt->fetchColumn()];
        } catch (PDOException $e) {
            error_log("Error en _contar_activos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al contar apartamentos'];
        }
    }

    /**
     * Obtiene el id y número de apartamento de un habitante a partir de su correo.
     * @return array ['estatus' => bool, 'datos' => array|string, 'mensaje' => string]
     */
    private function _obtener_apartamentos_por_correo()
    {
        $validacion = $this->validar(['correo']);
        if (!$validacion['estatus']) {
            return $validacion; // Retorna el error de validación
        }

        try {
            $sql = "SELECT a.id_apartamento, a.nro_apartamento
                    FROM habitantes h
                    INNER JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                    INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                    WHERE h.correo = :correo
                      AND h.activo = 1
                      AND a.activo = 1";

            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':correo' => $this->correo]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($resultados)) {
                return [
                    'estatus' => true,
                    'datos'   => [],
                    'mensaje' => 'El habitante no está vinculado a ningún apartamento activo.'
                ];
            }

            return [
                'estatus' => true,
                'datos'   => $resultados,
                'mensaje' => 'Apartamentos obtenidos correctamente.'
            ];
        } catch (PDOException $e) {
            error_log("Error en _obtener_apartamentos_por_correo: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar los apartamentos del habitante.'];
        }
    }

    // Dentro de la clase Apartamento, después de los métodos existentes

    /**
     * Valida la existencia de un valor en una tabla externa (para validaciones AJAX)
     * @param string $tabla Nombre de la tabla
     * @param string $campo Nombre del campo
     * @param mixed $valor Valor a buscar
     * @return bool
     */
    public function validarExistenciaExterna($tabla, $campo, $valor)
    {
        $tablasPermitidas = ['apartamentos', 'habitantes'];
        if (!in_array($tabla, $tablasPermitidas)) {
            return false;
        }
        // Determinar qué conexión usar (ambas están en negocio)
        $sql = "SELECT COUNT(*) FROM $tabla WHERE $campo = :valor";
        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute([':valor' => $valor]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en validarExistenciaExterna (Apartamento): " . $e->getMessage());
            return false;
        }
    }

}
?>