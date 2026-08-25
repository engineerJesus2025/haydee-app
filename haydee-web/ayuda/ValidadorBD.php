<?php
namespace haydee\ayuda;

use haydee\enums\TipoBaseDatos;
use haydee\modelo\Conexion;

/**
 * Clase ValidadorBD
 * Su única responsabilidad es ejecutar consultas de validación en la Base de Datos.
 */
class ValidadorBD extends Conexion {
    private const TABLAS_SEGURIDAD = ['usuarios', 'roles', 'tokens_seguridad', 'cartelera_virtual', 'notificaciones', 'modulos', 'permisos', 'asignacion_permisos', 'bitacora'];
    private const TABLAS_CON_ACTIVO = ['presupuesto', 'tipo_gasto', 'mensualidad', 'apartamentos', 'habitantes', 'usuarios'];
    
    private function obtenerConexionPorTabla($tabla) {
        $tipo = in_array($tabla, self::TABLAS_SEGURIDAD) ? TipoBaseDatos::SEGURIDAD : TipoBaseDatos::NEGOCIO;
        return $this->get_conex($tipo);
    }

    public function existe($tabla, $campo, $valor) {
        $sql = "SELECT COUNT(*) FROM $tabla WHERE $campo = :valor";
        
        if (in_array($tabla, self::TABLAS_CON_ACTIVO)) {
            $sql .= " AND activo = 1";
        }

        $stmt = $this->obtenerConexionPorTabla($tabla)->prepare($sql);
        $stmt->execute([':valor' => $valor]);
        return $stmt->fetchColumn() > 0;
    }

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

        $stmt = $this->obtenerConexionPorTabla($tabla)->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0; 
    }

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

        $stmt = $this->obtenerConexionPorTabla($tabla)->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}