<?php
namespace haydee\modelo;

use PDO;
use PDOException;

class AnioFiscal extends Conexion
{
    private $id_anio_fiscal;
    private $fecha_inicio;
    private $fecha_cierre;
    private $estado;
    private $descripcion;
    private $activo;

    // Reglas de validación centralizadas
    private $reglas = [
        'id_anio_fiscal' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'anio_fiscal', 'campo' => 'id_anio_fiscal']
        ],
        'fecha_inicio' => [
            'regex' => '/^\d{4}-\d{2}-\d{2}$/',
            'custom' => 'validarFecha'
        ],
        'fecha_cierre' => [
            'regex' => '/^\d{4}-\d{2}-\d{2}$/',
            'custom' => 'validarFecha'
        ],
        'estado' => [
            'regex' => '/^[A-Za-z]+$/'
        ],
        'descripcion' => [
            'regex' => '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]{0,50}$/',
            'opcional' => true
        ]
    ];

    // Getters y Setters
    public function set_id_anio_fiscal($id) { $this->id_anio_fiscal = $id; }
    public function get_id_anio_fiscal() { return $this->id_anio_fiscal; }
    public function set_fecha_inicio($fecha) { $this->fecha_inicio = $fecha; }
    public function get_fecha_inicio() { return $this->fecha_inicio; }
    public function set_fecha_cierre($fecha) { $this->fecha_cierre = $fecha; }
    public function get_fecha_cierre() { return $this->fecha_cierre; }
    public function set_estado($estado) { $this->estado = $estado; }
    public function get_estado() { return $this->estado; }
    public function set_descripcion($desc) { $this->descripcion = $desc; }
    public function get_descripcion() { return $this->descripcion; }
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

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

    private function validar($campos)
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

            // Requerido (a menos que sea opcional)
            $requerido = !(isset($regla['opcional']) && $regla['opcional'] === true);
            if ($requerido) {
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
            } else {
                // Si es opcional y está vacío, saltamos validaciones adicionales
                if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                    continue;
                }
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

            // Validación personalizada (método dentro de la clase)
            if (isset($regla['custom']) && method_exists($this, $regla['custom'])) {
                if (!$this->{$regla['custom']}($valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no es válido."
                    ];
                }
            }

            // Validar existencia en otra tabla (foránea)
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
        }
        return ['estatus' => true];
    }

    /**
     * Verifica si un valor existe en una tabla específica.
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
     * Validación personalizada para fecha (usa checkdate)
     */
    private function validarFecha($fecha)
    {
        $valores = explode('-', $fecha);
        return count($valores) == 3 && checkdate((int)$valores[1], (int)$valores[2], (int)$valores[0]);
    }

    /**
     * Validación adicional para el rango de fechas (debe ser aproximadamente un año)
     */
    private function validarRangoFechas()
    {
        $inicio = strtotime($this->fecha_inicio);
        $cierre = strtotime($this->fecha_cierre);

        if ($inicio >= $cierre) {
            return ['estatus' => false, 'mensaje' => 'La fecha de inicio debe ser inferior a la fecha de cierre.'];
        }

        $inicio_dt = new \DateTime($this->fecha_inicio);
        $cierre_dt = new \DateTime($this->fecha_cierre);
        $intervalo = $inicio_dt->diff($cierre_dt);
        $dias = (int)$intervalo->format('%a');

        if ($dias < 364 || $dias > 366) {
            return ['estatus' => false, 'mensaje' => "El período debe ser de aproximadamente un año (364-366 días). Días calculados: $dias."];
        }

        return ['estatus' => true];
    }

    // -----------------------------------------------------------------
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    /**
     * Lista todos los años fiscales activos.
     */
    private function _consultar()
    {
        $sql = "SELECT * FROM anio_fiscal WHERE activo = 1";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar años fiscales'];
        }
    }

    /**
     * Consulta un año fiscal específico por ID.
     */
    private function _consultar_anio_fiscal()
    {
        $validacion = $this->validar(['id_anio_fiscal']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "SELECT * FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal AND activo = 1";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Año fiscal no encontrado'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_anio_fiscal: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar el año fiscal'];
        }
    }

    /**
     * Registra un nuevo año fiscal.
     */
    private function _registrar()
    {
        $campos = ['fecha_inicio', 'fecha_cierre', 'estado'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        // Validación de rango de fechas
        $rango = $this->validarRangoFechas();
        if (!$rango['estatus']) {
            return $rango;
        }

        $sql = "INSERT INTO anio_fiscal (fecha_inicio, fecha_cierre, estado, descripcion) 
                VALUES (:fecha_inicio, :fecha_cierre, :estado, :descripcion)";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $this->fecha_inicio);
            $stmt->bindParam(':fecha_cierre', $this->fecha_cierre);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->execute();
            $lastId = $this->get_conex('negocio')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Año fiscal registrado correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el año fiscal'];
        }
    }

    /**
     * Actualiza un año fiscal existente.
     */
    private function _editar()
    {
        $campos = ['id_anio_fiscal', 'fecha_inicio', 'fecha_cierre', 'estado'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        // Validación de rango de fechas
        $rango = $this->validarRangoFechas();
        if (!$rango['estatus']) {
            return $rango;
        }

        $sql = "UPDATE anio_fiscal SET 
                    fecha_inicio = :fecha_inicio, 
                    fecha_cierre = :fecha_cierre, 
                    estado = :estado, 
                    descripcion = :descripcion 
                WHERE id_anio_fiscal = :id_anio_fiscal";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmt->bindParam(':fecha_inicio', $this->fecha_inicio);
            $stmt->bindParam(':fecha_cierre', $this->fecha_cierre);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Año fiscal actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _editar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el año fiscal'];
        }
    }

    /**
     * Elimina (cierra) un año fiscal (soft delete y cambia estado).
     */
    private function _eliminar()
    {
        $validacion = $this->validar(['id_anio_fiscal']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE anio_fiscal SET activo = 0, estado = 'Cerrada' WHERE id_anio_fiscal = :id_anio_fiscal";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Año fiscal eliminado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar el año fiscal'];
        }
    }

    /**
     * Ejecuta el procedimiento almacenado para verificar/cerrar años fiscales.
     */
    private function _verificar_anio_fiscal()
    {
        $sql = "CALL gestionar_anio_fiscal()";

        try {
            $stmt = $this->get_conex('negocio')->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Procedimiento ejecutado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _verificar_anio_fiscal: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al ejecutar el procedimiento de verificación'];
        }
    }
}
?>