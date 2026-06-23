<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\TipoBaseDatos;

class ClaveSesion extends Conexion {
    
    // Registra o actualiza la clave AES tras un login exitoso (Inserción Atómica)
    public function guardarClave($dispositivo_id, $usuario_id, $clave_aes) {
        // Le damos nombres únicos a los parámetros del UPDATE (:usu2 y :clave2)
        $sql = "INSERT INTO claves_sesion (dispositivo_id, usuario_id, clave_aes) 
                VALUES (:disp, :usu, :clave)
                ON DUPLICATE KEY UPDATE usuario_id = :usu2, clave_aes = :clave2, ultima_actividad = NOW()";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            
            // Pasamos los 5 parámetros explícitamente
            return $stmt->execute([
                ':disp'   => $dispositivo_id,
                ':usu'    => $usuario_id,
                ':clave'  => $clave_aes,
                ':usu2'   => $usuario_id, 
                ':clave2' => $clave_aes   
            ]);
            // Hoy descubri que pdo no deja repetir marcadores (:usu, :clave, etc.) -_-
        } catch (PDOException $e) {
            error_log("Error guardando clave de sesión: " . $e->getMessage());
            return false;
        }
    }

    // Obtiene la clave AES de un dispositivo ya autenticado
    public function obtenerClave($dispositivo_id) {
        $sql = "SELECT clave_aes FROM claves_sesion WHERE dispositivo_id = :disp";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':disp' => $dispositivo_id]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ? $fila['clave_aes'] : null;
        } catch (PDOException $e) {
            error_log("Error obteniendo clave de sesión: " . $e->getMessage());
            return null;
        }
    }

    /**
     Hace lo que se imaginan -_-
     */
    public function eliminarClave($dispositivo_id) {
        $sql = "DELETE FROM claves_sesion WHERE dispositivo_id = :disp";
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            return $stmt->execute([':disp' => $dispositivo_id]);
        } catch (PDOException $e) {
            error_log("Error eliminando clave de sesión: " . $e->getMessage());
            return false;
        }
    }
}