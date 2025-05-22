<?php

//NO MODIFICAR ESTO, POR FAVOR

require_once "config/config.php";

class Conexion extends PDO
{
    private $conex;    

    public function __construct()
    {
        $conex_string = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";

        try {
            $this->conex = new PDO($conex_string, DB_USER, DB_PASS);
            $this->conex->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
            
        } catch (PDOException $e) {
            die("Conexión Fallida" . $e->getMessage());
        }
    }

    public function destruir()
    {
        $this->conex = null;
    }

    public function get_conex()
    {
        return $this->conex;
    }


    public function registrar_bitacora($accion, $modulo_id, $registro_alt){
        $this->cambiar_db_seguridad();

        $sql = "INSERT INTO bitacora(fecha_hora, accion, registro_alterado, usuario_id, modulo_id)
        VALUES (:fecha_hora, :accion, :registro_alterado, :usuario_id, :modulo_id)";
        $conexion = $this->get_conex()->prepare($sql);
        date_default_timezone_set('America/Caracas');
        $timestamp = time();
        $fecha = date("Y-m-d H:i:s", $timestamp);         
        $usuario = $_SESSION["id_usuario"];

        $conexion->bindParam(":fecha_hora",$fecha);
        $conexion->bindParam(":accion", $accion);
        $conexion->bindParam(":registro_alterado", $registro_alt);
        $conexion->bindParam(":usuario_id", $usuario);
        $conexion->bindParam(":modulo_id", $modulo_id);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        return $result;
    }

    protected function cambiar_db_seguridad()
    {
        $conex_string = "mysql:host=" . DB_HOST . ";dbname=" . DB_SECURITY . ";charset=utf8";

        try {
            $this->conex = new PDO($conex_string, DB_USER, DB_PASS);
            $this->conex->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        } catch (PDOException $e) {
            die("Conexión Fallida" . $e->getMessage());
        }
    }

    protected function cambiar_db_negocio()
    {
        $conex_string = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";

        try {
            $this->conex = new PDO($conex_string, DB_USER, DB_PASS);
            $this->conex->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        } catch (PDOException $e) {
            die("Conexión Fallida" . $e->getMessage());
        }
    }

    public static function tiene_permiso($modulo,$accion)
    {
        $permiso =false;

        foreach($_SESSION["permisos"] AS $permisos){

            if($permisos["id_modulo"] == $modulo && $permisos["nombre_permiso"] == $accion){
                $permiso = true;
                break;
            }
        }

        return $permiso;
    }
}