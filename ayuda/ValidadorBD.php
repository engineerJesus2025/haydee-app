<?php
namespace haydee\ayuda;

use haydee\modelo\Conexion;
use PDO;
use PDOException;

/**
 * Clase ValidadorBD
 * Su única responsabilidad es ejecutar consultas de validación en la Base de Datos.
 */
class ValidadorBD extends Conexion {

    /**
     * Detecta qué base de datos usar según la tabla.
     */
    private function obtenerConexionPorTabla($tabla) {
        $tablasSeguridad = ['usuarios', 'roles', 'tokens_seguridad'];
        $tipo = in_array($tabla, $tablasSeguridad) ? 'seguridad' : 'negocio';
        return $this->get_conex($tipo);
    }

    /**
     * Verifica si un valor existe en una tabla específica.
     */
    public function existe($tabla, $campo, $valor) {
        $tablasConActivo = ['presupuesto', 'tipo_gasto', 'mensualidad', 'detalles_presupuesto', 'apartamentos', 'habitantes'];
        
        $sql = "SELECT COUNT(*) FROM $tabla WHERE $campo = :valor";
        if (in_array($tabla, $tablasConActivo)) {
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
        
        $tablasConActivo = ['apartamentos', 'usuarios']; 
        if (in_array($tabla, $tablasConActivo)) {
            $sql .= " AND activo = 1";
        }

        if ($excludeField && $excludeValue !== null) {
            $sql .= " AND $excludeField != :exclude_val";
        }

        try {
            $stmt = $this->obtenerConexionPorTabla($tabla)->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            if ($excludeField && $excludeValue !== null) {
                $stmt->bindParam(':exclude_val', $excludeValue);
            }
            $stmt->execute();
            return $stmt->fetchColumn() == 0; // Es único si el conteo es 0
        } catch (PDOException $e) {
            error_log("Error ValidadorBD -> esUnico: " . $e->getMessage());
            return false;
        }
    }
}
?>