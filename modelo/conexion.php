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

    public function generarCopiaSeguridad($db)
    {
        $db_copiar = '';

        if ($db == "negocio") {
            $db_copiar = DB_NAME;
        }
        else if($db == "seguridad"){
            $db_copiar = DB_SECURITY;
        }

        $mysqldump_path = '"C:\xampp\mysql\bin\mysqldump.exe"';//Importante por lo visto

        $backup = 'recursos\Backups\backup_' . $db_copiar . '_' . date("Y-m-d-H-i-s") . '.sql';
        $comando = $mysqldump_path . " --host=" . DB_HOST . " --user=". DB_USER . " --password= ". DB_PASS . " " . $db_copiar . " > " . $backup;

        system($comando . " 2>&1", $resultado);
        
        if ($resultado === 0) {
            return ["estatus"=>true,"mensaje"=>"Copia de seguridad creada exitosamente"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Error al crear el backup: " . $backup];
        }
    }

    public function obtenerCopias()
    { 
        $directorio = 'recursos\Backups';
        $ficheros = scandir($directorio);
        $aray_ficheros = [];
        if ($ficheros !== false) {
            foreach ($ficheros as $fichero) {
                if ($fichero != '.' && $fichero != '..') {
                    array_push($aray_ficheros, $fichero);
                }
            }
        } 
        else {
            return ["estatus"=>false,"mensaje"=>"No se pudo abrir el directorio de las copias de seguridad"];
        }
        return ["estatus"=>true,"mensaje"=>$aray_ficheros];
    }

    public function importarCopiaSeguridad($db,$fichero)
    {
        if ($db == "negocio") {
            $this->cambiar_db_negocio();
        }
        else if($db == "seguridad"){
            $this->cambiar_db_seguridad();
        }

        $sql = file_get_contents('recursos/Backups/' . $fichero);
        
        $conexion = $this->get_conex()->prepare($sql);
        
        $result = $conexion->execute(); 

        if ($result) {            
            return ["estatus"=>true,"mensaje"=>"La copia de seguridad se ha importado exitosamente"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar importar la copia de seguridad"];
        }
    }
}

?>