<?php
namespace haydee\ayuda;

use PDO;
use PDOException;
use haydee\modelo\Conexion;
use haydee\enums\TipoBaseDatos;
/**
 * Clase ValidadorBD
 * Su única responsabilidad es ejecutar consultas de validación en la Base de Datos.
 */
class ValidadorBD extends Conexion {
    private const TABLAS_SEGURIDAD = ['usuarios', 'roles', 'tokens_seguridad', 'cartelera_virtual', 'notificaciones', 'modulos', 'permisos', 'asignacion_permisos', 'bitacora'];
    private const TABLAS_CON_ACTIVO = ['presupuesto', 'tipo_gasto', 'mensualidad', 'apartamentos', 'habitantes', 'usuarios'];
    /**
     * Detecta qué base de datos usar según la tabla.
     */
    private function obtenerConexionPorTabla($tabla) {
        $tipo = in_array($tabla, self::TABLAS_SEGURIDAD) ? TipoBaseDatos::SEGURIDAD : TipoBaseDatos::NEGOCIO;
        return $this->get_conex($tipo);
    }

    /**
     * Verifica si un valor existe en una tabla específica.
     */
    public function existe($tabla, $campo, $valor) {
        $sql = "SELECT COUNT(*) FROM $tabla WHERE $campo = :valor";
        
        if (in_array($tabla, self::TABLAS_CON_ACTIVO)) {
            $sql .= " AND activo = 1";
        }

        try {
            $stmt = $this->obtenerConexionPorTabla($tabla)->prepare($sql);
            $stmt->execute([':valor' => $valor]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error ValidadorBD -> existe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica que un dato no se repita en la base de datos (Unique).
     */
    public function esUnico($tabla, $campo, $valor, $excludeField = null, $excludeValue = null) {
        $sql = "SELECT COUNT(*) FROM $tabla WHERE $campo = :valor";
        $params = [':valor' => $valor];

        if ($excludeField && $excludeValue !== null) {
            $sql .= " AND $excludeField != :exclude_value";
            $params[':exclude_value'] = $excludeValue;
        }

        if (in_array($tabla, self::TABLAS_CON_ACTIVO)) {
            $sql .= " AND activo = 1";
        }

        try {
            $stmt = $this->obtenerConexionPorTabla($tabla)->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() == 0; // Es único si el conteo es 0
        } catch (PDOException $e) {
            error_log("Error ValidadorBD -> esUnico: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica la existencia de un registro basándose en múltiples condiciones.
     */
    public function existeConCondicion($tabla, $condiciones) {
        $sql = "SELECT COUNT(*) FROM $tabla WHERE 1=1";
        $params = [];
        
        foreach ($condiciones as $campo => $valor) {
            $sql .= " AND $campo = :$campo";
            $params[":$campo"] = $valor;
        }

        if (in_array($tabla, self::TABLAS_CON_ACTIVO)) {
            $sql .= " AND activo = 1";
        }

        try {
            $stmt = $this->obtenerConexionPorTabla($tabla)->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error ValidadorBD -> existeConCondicion: " . $e->getMessage());
            return false;
        }
    }
}
