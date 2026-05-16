<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\EstadoPeriodo;
use haydee\enums\TipoBaseDatos;

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

    /**
     * Devuelve las reglas de validación según la operación solicitada.
     * @param string $operacion El nombre de la operación (ej: 'insertar', 'modificar')
     * @return array Reglas aplicables a la operación
     */
    public static function obtenerReglas($operacion) {
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
                'fecha_posterior_a' => 'fecha_inicio' // Le decimos contra qué campo compararse
            ],
            'estado' => [
                'regex' => '/^(ABIERTO|CERRADO)$/'
            ],
            'descripcion' => [
                'regex' => '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ,.\s]{0,50}$/',
                'opcional' => true
            ]
        ];

        // Definimos qué campos se validan en cada operación
        $camposPorOperacion = [
            'registrar'  => ['fecha_inicio', 'fecha_cierre', 'estado', 'descripcion'],
            'modificar' => ['id_anio_fiscal', 'fecha_inicio', 'fecha_cierre', 'estado', 'descripcion'],
            'eliminar'  => ['id_anio_fiscal']
        ];

        // Si la operación existe en nuestro mapeo, devolvemos solo las reglas de esos campos
        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }

        return [];
    }

    /**
     * Regla de Negocio: El periodo fiscal debe ser de aproximadamente un año.
     * Nota: El controlador ya garantizó que fecha_cierre es mayor a fecha_inicio.
     */
    private function validarRangoFechas()
    {
        $inicio_dt = new \DateTime($this->fecha_inicio);
        $cierre_dt = new \DateTime($this->fecha_cierre);
        $intervalo = $inicio_dt->diff($cierre_dt);
        $dias = (int)$intervalo->format('%a');

        if ($dias < self::DIAS_MINIMOS_PERIODO || $dias > self::DIAS_MAXIMOS_PERIODO) {
            return ['estatus' => false, 'mensaje' => "El período debe ser de aproximadamente un año (" . self::DIAS_MINIMOS_PERIODO . "-" . self::DIAS_MAXIMOS_PERIODO . " días). Días calculados: $dias."];
        }

        return ['estatus' => true];
    }

    // Métodos privados (acciones)

    /**
     * Lista todos los años fiscales activos.
     // SE USA EN EL MODULO
     */
    private function _consultar()
    {
        $sql = "SELECT * FROM anio_fiscal WHERE activo = 1";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
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
     // SE USA EN EL MODULO
     */
    private function _consultar_anio_fiscal()
    {
        $sql = "SELECT * FROM anio_fiscal WHERE id_anio_fiscal = :id_anio_fiscal AND activo = 1";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
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
     // SE USA EN EL MODULO
     */
    private function _registrar()
    {
        // Validación de rango de fechas
        $rango = $this->validarRangoFechas();
        if (!$rango['estatus']) {
            return $rango;
        }

        $sql = "INSERT INTO anio_fiscal (fecha_inicio, fecha_cierre, estado, descripcion) 
                VALUES (:fecha_inicio, :fecha_cierre, :estado, :descripcion)";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $this->fecha_inicio);
            $stmt->bindParam(':fecha_cierre', $this->fecha_cierre);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->execute();
            $lastId = $this->get_conex(TipoBaseDatos::NEGOCIO)->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Año fiscal registrado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar el año fiscal'];
        }
    }

    /**
     * Actualiza un año fiscal existente.
     // SE USA EN EL MODULO
     */
    private function _modificar()
    {
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
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':id_anio_fiscal', $this->id_anio_fiscal);
            $stmt->bindParam(':fecha_inicio', $this->fecha_inicio);
            $stmt->bindParam(':fecha_cierre', $this->fecha_cierre);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Año fiscal actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar el año fiscal'];
        }
    }

    /**
     * Elimina (cierra) un año fiscal (soft delete y cambia estado).
     // SE USA EN EL MODULO
     */
    private function _eliminar()
    {
        $estadoCerrado = EstadoPeriodo::CERRADO->value;
        $sql = "UPDATE anio_fiscal SET activo = 0, estado = :estado WHERE id_anio_fiscal = :id_anio_fiscal";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->bindParam(':estado', $estadoCerrado);
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
     // SE USA EN EL SCRIPT AUTOMATICO
     */
    private function _verificar_anio_fiscal()
    {
        $sql = "CALL gestionar_anio_fiscal()";

        try {
            $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Procedimiento ejecutado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _verificar_anio_fiscal: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al ejecutar el procedimiento de verificación'];
        }
    }
}
