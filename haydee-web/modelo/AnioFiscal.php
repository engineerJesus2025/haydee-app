<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\EstadoPeriodo; 
use haydee\enums\TipoBaseDatos;
use haydee\excepciones\NegocioException;

class AnioFiscal extends Conexion
{
    private const DIAS_MINIMOS_PERIODO = 364;
    private const DIAS_MAXIMOS_PERIODO = 366;

    private $id_anio_fiscal;
    private $fecha_inicio;
    private $fecha_cierre;
    private $estado;
    private $descripcion;
    private $activo;

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

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    public static function obtenerReglas($operacion) {
        $estadosValidos = implode('|', array_column(EstadoPeriodo::cases(), 'value'));
        $reglasGenerales = [
            'id_anio_fiscal' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'anio_fiscal', 'campo' => 'id_anio_fiscal']
            ],
            'fecha_inicio' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'fecha_cierre' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/',
                'fecha_posterior_a' => 'fecha_inicio'
            ],
            'estado' => [
                'regex' => "/^($estadosValidos)$/"
            ],
            'descripcion' => [
                'regex' => '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]{0,50}$/',
                'opcional' => true
            ]
        ];

        $camposPorOperacion = [
            'registrar'  => ['fecha_inicio', 'fecha_cierre', 'estado', 'descripcion'],
            'modificar' => ['id_anio_fiscal', 'fecha_inicio', 'fecha_cierre', 'estado', 'descripcion'],
            'eliminar'  => ['id_anio_fiscal']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }

        return [];
    }

    /**
     * Verifica que no coexistan dos periodos abiertos simultáneamente.
     */
    private function validarAnioActivoUnico($idIgnorar = null)
    {
        if ($this->estado !== EstadoPeriodo::ABIERTO->value) {
            return true;
        }

        $sql = "SELECT id_anio_fiscal FROM anio_fiscal WHERE estado = :estado AND activo = 1";
        if ($idIgnorar !== null) {
            $sql .= " AND id_anio_fiscal != :id_ignorar";
        }

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $estadoAbierto = EstadoPeriodo::ABIERTO->value;
        $stmt->bindParam(':estado', $estadoAbierto);
        
        if ($idIgnorar !== null) {
            $stmt->bindParam(':id_ignorar', $idIgnorar);
        }
        
        $stmt->execute();
        
        if ($stmt->fetch()) {
            throw new NegocioException('Ya existe un año fiscal ABIERTO en el sistema. Debe cerrarlo antes de activar uno nuevo.', 400);
        }

        return true;
    }

    private function validarRangoFechas()
    {
        $inicio_dt = new \DateTime($this->fecha_inicio);
        $cierre_dt = new \DateTime($this->fecha_cierre);
        $intervalo = $inicio_dt->diff($cierre_dt);
        $dias = (int)$intervalo->format('%a');

        if ($dias < self::DIAS_MINIMOS_PERIODO || $dias > self::DIAS_MAXIMOS_PERIODO) {
            throw new NegocioException("El período debe ser de aproximadamente un año (" . self::DIAS_MINIMOS_PERIODO . "-" . self::DIAS_MAXIMOS_PERIODO . " días). Días calculados: $dias.", 400);
        }

        return true;
    }

    private function _consultar()
    {
        $sql = "SELECT * FROM anio_fiscal WHERE activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_anio_fiscal()
    {
        $sql = "SELECT * FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal AND activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
        $stmt->execute();
        
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos) {
            throw new NegocioException('Año fiscal no encontrado', 404);
        }
        
        return ['estatus' => true, 'datos' => $datos];
    }

    private function _registrar()
    {
        $this->validarRangoFechas();
        $this->validarAnioActivoUnico();

        $sql = "INSERT INTO anio_fiscal (fecha_inicio, fecha_cierre, estado, descripcion) 
                VALUES (:fecha_inicio, :fecha_cierre, :estado, :descripcion)";
                
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':fecha_inicio', $this->fecha_inicio);
        $stmt->bindParam(':fecha_cierre', $this->fecha_cierre);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->execute();
        
        $lastId = $this->get_conex(TipoBaseDatos::NEGOCIO)->lastInsertId();
        return ['estatus' => true, 'mensaje' => 'Año fiscal registrado correctamente', 'lastId' => $lastId];
    }

    private function _modificar()
    {
        $conexion = $this->get_conex(TipoBaseDatos::NEGOCIO);

        try {
            $conexion->beginTransaction();

            $sqlActual = "SELECT estado FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal AND activo = 1 FOR UPDATE";
            $stmtActual = $conexion->prepare($sqlActual);
            $stmtActual->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmtActual->execute();
            $anioBD = $stmtActual->fetch(PDO::FETCH_ASSOC);

            if (!$anioBD) {
                throw new NegocioException('El año fiscal no existe o fue eliminado.', 404);
            }

            $estadoActualBD = strtoupper($anioBD['estado']);
            $nuevoEstado = strtoupper($this->estado);

            if ($estadoActualBD === EstadoPeriodo::CERRADO->value && $nuevoEstado === EstadoPeriodo::ABIERTO->value) {
                throw new NegocioException('No se puede reabrir un año fiscal histórico que ya ha sido cerrado.', 400);
            }

            if ($estadoActualBD === EstadoPeriodo::ABIERTO->value && $nuevoEstado === EstadoPeriodo::CERRADO->value) {
                throw new NegocioException('No puede cerrar el año fiscal actual editando el registro. El sistema lo cerrará automáticamente al cumplirse la fecha o mediante el proceso formal de Cierre.', 400);
            }

            $this->validarRangoFechas();
            $this->validarAnioActivoUnico($this->id_anio_fiscal);

            $sql = "UPDATE anio_fiscal SET 
                        fecha_inicio = :fecha_inicio, 
                        fecha_cierre = :fecha_cierre, 
                        estado = :estado, 
                        descripcion = :descripcion 
                    WHERE id_anio_fiscal = :id_anio_fiscal";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmt->bindParam(':fecha_inicio', $this->fecha_inicio);
            $stmt->bindParam(':fecha_cierre', $this->fecha_cierre);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->execute();

            $conexion->commit();
            return ['estatus' => true, 'mensaje' => 'Año fiscal actualizado correctamente'];

        } catch (\Throwable $e) { 
            $conexion->rollBack();
            throw $e; // Relanzamos para que suba
        }
    }

    private function _eliminar()
    {
        $conexion = $this->get_conex(TipoBaseDatos::NEGOCIO);

        try {
            $conexion->beginTransaction();

            $sqlCheck = "SELECT estado FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal AND activo = 1 FOR UPDATE";
            $stmtCheck = $conexion->prepare($sqlCheck);
            $stmtCheck->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmtCheck->execute();
            $anio = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$anio) {
                throw new NegocioException('El año fiscal no existe o ya fue eliminado.', 404);
            }

            $sqlDependencias = "SELECT COUNT(*) FROM caja_chica WHERE anio_fiscal_id = :id_anio_fiscal AND activo = 1";
            $stmtDep = $conexion->prepare($sqlDependencias);
            $stmtDep->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmtDep->execute();
            
            if ($stmtDep->fetchColumn() > 0) {
                throw new NegocioException('No se puede eliminar este periodo porque ya existen cajas chicas operando con él.', 400);
            }

            $estadoCerrado = EstadoPeriodo::CERRADO->value;
            $sql = "UPDATE anio_fiscal SET activo = 0, estado = :estado WHERE id_anio_fiscal = :id_anio_fiscal";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':estado', $estadoCerrado);
            $stmt->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmt->execute();

            $conexion->commit();
            return ['estatus' => true, 'mensaje' => 'Año fiscal eliminado del sistema correctamente.'];

        } catch (\Throwable $e) {
            $conexion->rollBack();
            throw $e;
        }
    }    

    private function _gestionar_periodos()
    {
        $sql = "CALL sp_gestionar_periodos_automaticos()";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return ['estatus' => true, 'mensaje' => $res['mensaje'] ?? 'Procedimiento ejecutado'];
    }
}