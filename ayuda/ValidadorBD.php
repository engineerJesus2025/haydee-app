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
        $tablasSeguridad = ['usuarios', 'roles', 'tokens_seguridad', 'cartelera_virtual', 'notificaciones', 'modulos', 'permisos', 'asignacion_permisos', 'bitacora'];
        $tipo = in_array($tabla, $tablasSeguridad) ? 'seguridad' : 'negocio';
        return $this->get_conex($tipo);
    }

    /**
     * Verifica si un valor existe en una tabla específica.
     */
    public function existe($tabla, $campo, $valor) {
        // SOLUCIÓN: Quitamos 'detalles_presupuesto' de esta lista porque no tiene columna 'activo'
        $tablasConActivo = ['presupuesto', 'tipo_gasto', 'mensualidad', 'apartamentos', 'habitantes'];
        
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

    /**
     * Verifica la existencia de un registro basándose en múltiples condiciones.
     * Ideal para tablas puente o validaciones complejas en AJAX.
     * @param string $tabla Nombre de la tabla.
     * @param array $condiciones Arreglo asociativo ['campo' => 'valor', 'campo2' => 'valor2'].
     * @return bool True si existe al menos un registro que cumpla todas las condiciones.
     */
    public function existeConCondicion($tabla, $condiciones) {
        $sql = "SELECT COUNT(*) FROM $tabla WHERE 1=1";
        $params = [];
        
        foreach ($condiciones as $campo => $valor) {
            $sql .= " AND $campo = :$campo";
            $params[":$campo"] = $valor;
        }

        // Agregar lógica de activo = 1 si la tabla lo maneja
        $tablasConActivo = ['apartamentos', 'usuarios', 'habitantes', 'presupuesto', 'tipo_gasto', 'mensualidad']; 
        if (in_array($tabla, $tablasConActivo)) {
            $sql .= " AND activo = 1";
        }

        try {
            $stmt = $this->obtenerConexionPorTabla($tabla)->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error ValidadorBD -> existeConCondicion: " . $e->getMessage());
            return false;
        }
    }
}
?>