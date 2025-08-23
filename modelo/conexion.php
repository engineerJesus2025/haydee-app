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
            $this->conex = new PDO($conex_string, DB_USER, DB_PASS,[PDO::MYSQL_ATTR_FOUND_ROWS => true]);
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

    public function descargarCopiaSeguridad($db)
    {
        $db_copiar = '';

        if ($db == "negocio") {
            $db_copiar = DB_NAME;
        }
        else if($db == "seguridad"){
            $db_copiar = DB_SECURITY;
        }

        $mysqldump_path = '"C:\xampp\mysql\bin\mysqldump.exe"';//Importante por lo visto

        $backup = 'backup_' . $db_copiar . '_' . date("Y-m-d-H-i-s") . '.sql';

        $comando = $mysqldump_path . " --host=" . DB_HOST . " --user=". DB_USER . " --password= ". DB_PASS . " " . $db_copiar . " > " . $backup;

        system($comando, $resultado);

        // Verificamos que el comando se ejecutó sin errores y el archivo existe
        if ($resultado === 0 && file_exists($backup)) {
            // Indica que es un archivo genérico para descargar
            header('Content-Type: application/octet-stream');
            // Le dice al navegador que lo trate como un archivo adjunto y sugiere un nombre
            header('Content-Disposition: attachment; filename="' . basename($backup) . '"');
            // Evita que el navegador guarde el archivo en caché
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            // Indica el tamaño del archivo (importante para la barra de progreso de la descarga)
            header('Content-Length: ' . filesize($backup));

            // Leemos el archivo y lo enviamos al navegador
            // flush() y ob_clean() aseguran que no haya salida de datos previa que corrompa el archivo
            ob_clean();
            flush();
            readfile($backup);

            // Borramos el archivo del servidor una vez descargado
            unlink($backup);

            // 5. Detenemos el script para asegurar que no se envíe nada más
            exit;
        } else {
            // Si hubo un error, nos devolvemos con una var para indicar error
            header("Location:?pagina=mantenimiento_controlador.php&accion=inicio&e=1");
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

    public function importarSQL($sql,$db = 'negocio')
    {
        if ($db == "negocio") {
            $this->cambiar_db_negocio();
        }
        else if($db == "seguridad"){
            $this->cambiar_db_seguridad();
        }
        
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