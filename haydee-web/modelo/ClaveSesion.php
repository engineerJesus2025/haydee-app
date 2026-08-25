<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;

class ClaveSesion extends Conexion {
    
    public function guardarClave($dispositivo_id, $usuario_id, $clave_aes) {
        $sql = "INSERT INTO claves_sesion (dispositivo_id, usuario_id, clave_aes) 
                VALUES (:disp, :usu, :clave)
                ON DUPLICATE KEY UPDATE usuario_id = :usu2, clave_aes = :clave2, ultima_actividad = NOW()";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        
        return $stmt->execute([
            ':disp'   => $dispositivo_id,
            ':usu'    => $usuario_id,
            ':clave'  => $clave_aes,
            ':usu2'   => $usuario_id, 
            ':clave2' => $clave_aes   
        ]);
    }

    public function obtenerClave($dispositivo_id) {
        $sql = "SELECT clave_aes FROM claves_sesion WHERE dispositivo_id = :disp";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':disp' => $dispositivo_id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $fila['clave_aes'] : null;
    }

    public function eliminarClave($dispositivo_id) {
        $sql = "DELETE FROM claves_sesion WHERE dispositivo_id = :disp";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        return $stmt->execute([':disp' => $dispositivo_id]);
    }
}