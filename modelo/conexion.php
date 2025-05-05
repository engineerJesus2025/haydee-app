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

    public function get_conex()
    {
        return $this->conex;
    }

    public function registrar_bitacora($accion, $modulo_id){
        $this->cambiar_db_seguridad();

        $sql = "INSERT INTO bitacora(fecha_hora, accion, usuario_id, modulo_id)
        VALUES (:fecha_hora, :accion, :usuario_id, :modulo_id)";
        $conexion = $this->get_conex()->prepare($sql);
        date_default_timezone_set('America/Caracas');
        $timestamp = time();
        $fecha = date("Y-m-d H:i:s", $timestamp); 
        $ip = $this->get_direccion_ip() || "N/A";
        $usuario = $_SESSION["id_usuario"];

        $conexion->bindParam(":fecha_hora",$fecha);
        $conexion->bindParam(":accion", $accion);        
        $conexion->bindParam(":usuario_id", $usuario);
        $conexion->bindParam(":modulo_id", $modulo_id);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        return $result;
    }

    public function get_direccion_ip() {

        if (isset($_SERVER["HTTP_CLIENT_IP"])){

            return $_SERVER["HTTP_CLIENT_IP"];

        }elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){

            return $_SERVER["HTTP_X_FORWARDED_FOR"];

        }elseif (isset($_SERVER["HTTP_X_FORWARDED"])){

            return $_SERVER["HTTP_X_FORWARDED"];

        }elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])){

            return $_SERVER["HTTP_FORWARDED_FOR"];

        }elseif (isset($_SERVER["HTTP_FORWARDED"])){

            return $_SERVER["HTTP_FORWARDED"];

        }else{
            return $_SERVER["REMOTE_ADDR"];
        }
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

    public function cambiar_db_negocio()
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
}