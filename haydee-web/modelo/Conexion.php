<?php
namespace haydee\modelo;

use \PDO;
use \PDOException;
use haydee\enums\TipoBaseDatos;

class Conexion extends PDO {
    // Guardamos las conexiones independientemente para no estar desconectando y conectando
    private $conexNegocio = null;
    private $conexSeguridad = null;

    public function __construct() {
        // crea una conexion a la bd del negocio al instanciar
        $conex_string = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        try {
            $this->conexNegocio = new PDO($conex_string, DB_USER, DB_PASS);
            $this->conexNegocio->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Desactiva la emulación de sentencias preparadas (seguridad al parecer)
            $this->conexNegocio->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch (PDOException $e) {
            http_response_code(503);
            throw new \Exception("Error de conexión a " . DB_NAME . ": ". $e->getMessage());
        }
    }

    protected function get_conex(TipoBaseDatos $base = TipoBaseDatos::NEGOCIO) {
        if ($base === TipoBaseDatos::SEGURIDAD) {
            if ($this->conexSeguridad === null) {
                $this->conexSeguridad = $this->crearConexion(DB_SECURITY);
            }
            return $this->conexSeguridad;
        }
        
        if ($this->conexNegocio === null) {
            $this->conexNegocio = $this->crearConexion(DB_NAME);
        }
        return $this->conexNegocio;
    }

    
    //privada para crear la instancia PDO
     
    private function crearConexion($nombre_db) {
        $conex_string = "mysql:host=" . DB_HOST . ";dbname=" . $nombre_db . ";charset=utf8";
        try {
            $pdo = new PDO($conex_string, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $pdo;
        } catch (PDOException $e) {
            throw new \Exception("Error de conexión a $nombre_db: " . $e->getMessage());
        }
    }

    public function cerrar($base = null) {
    if ($base === 'negocio' || $base === null) {
        $this->conexNegocio = null;
    }
    if ($base === 'seguridad' || $base === null) {
        $this->conexSeguridad = null;
    }
}
}