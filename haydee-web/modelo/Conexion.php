<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\TipoBaseDatos;
use haydee\excepciones\BaseDatosException;

class Conexion extends PDO {
    private $conexNegocio = null;
    private $conexSeguridad = null;

    public function __construct() {
        $conex_string = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        try {
            $this->conexNegocio = new PDO($conex_string, DB_USER, DB_PASS);
            $this->conexNegocio->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexNegocio->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch (PDOException $e) {
            throw new BaseDatosException("Error crítico de conexión a la base de datos del sistema.", 503, $e);
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

    private function crearConexion($nombre_db) {
        $conex_string = "mysql:host=" . DB_HOST . ";dbname=" . $nombre_db . ";charset=utf8";
        try {
            $pdo = new PDO($conex_string, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $pdo;
        } catch (PDOException $e) {
            throw new BaseDatosException("Error crítico de conexión al esquema aislado de datos.", 503, $e);
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