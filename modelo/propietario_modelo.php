<?php 
require_once "modelo/conexion.php";

class Propietario extends Conexion{
    private $id_propietario;
    private $nombre;
    private $apellido;
    private $cedula;
    private $telefono;
    private $correo;

    public function __construct(){
        parent::__construct();
    }

    public function set_id_propietario($id_propietario){
        $this->id_propietario = $id_propietario;
    }
    public function get_id_usuario(){
        return $this->id_propietario;
    }

    public function set_nombre($nombre){
        $this->nombre = $nombre;
    }
    public function get_nombre(){
        return $this->nombre;
    }

    public function set_apellido($apellido){
        $this->apellido = $apellido;
    }   

    public function get_apellido(){
        return $this->apellido;
    }

    public function set_cedula($cedula){
        $this->cedula = $cedula;
    }
    public function get_cedula(){
        return $this->cedula;
    }

    public function set_telefono($telefono){
        $this->telefono = $telefono;
    }
    public function get_telefono(){
        return $this->telefono;
    }
    public function set_correo($correo){
        $this->correo = $correo;
    }
    public function get_correo(){
        return $this->correo;
    }

    public function consultar(){
        $sql = "SELECT * FROM propietarios";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $this->registrar_bitacora(CONSULTAR, GESTIONAR_PROPIETARIOS, "TODOS LOS USUARIOS");//registra cuando se entra al modulo de propietarios

        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if($result == true){
            return $datos;
        }else{
            return ["estatus"=>false, "mensaje"=>"Error al consultar los propietarios"];
        }
    }

    public function consultar_propietario(){
        $sql = "SELECT * FROM propietarios WHERE id_propietario = :id_propietario";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_propietario", $this->id_propietario);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if($result == true){
            return $datos;
        }else{
            return ["estatus"=>false, "mensaje"=>"Error al consultar el propietario"];
        }
    }

    public function registrar(){
        // Validar duplicados
    if ($this->existe_cedula($this->cedula)) {
        return ["estatus"=>false, "mensaje"=>"La cédula ya está registrada"];
    }
    if ($this->existe_correo($this->correo)) {
        return ["estatus"=>false, "mensaje"=>"El correo ya está registrado"];
    }
        $sql = "INSERT INTO propietarios (nombre, apellido, cedula, telefono, correo) VALUES (:nombre, :apellido, :cedula, :telefono, :correo)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre", $this->nombre);
        $conexion->bindParam(":apellido", $this->apellido);
        $conexion->bindParam(":cedula", $this->cedula);
        $conexion->bindParam(":telefono", $this->telefono);
        $conexion->bindParam(":correo", $this->correo);
        $result = $conexion->execute();

        $this->registrar_bitacora(REGISTRAR, GESTIONAR_PROPIETARIOS, "Propietario: ".$this->nombre." ".$this->apellido); 

        if($result == true){
            return ["estatus"=>true, "mensaje"=>"Propietario registrado correctamente"];
    } else{
            return ["estatus"=>false, "mensaje"=>"Error al registrar el propietario"];
        }
    }

    public function editar_propietario(){
         // Validar duplicados excluyendo el actual
    if ($this->existe_cedula($this->cedula, $this->id_propietario)) {
        return ["estatus"=>false, "mensaje"=>"La cédula ya está registrada"];
    }
    if ($this->existe_correo($this->correo, $this->id_propietario)) {
        return ["estatus"=>false, "mensaje"=>"El correo ya está registrado"];
    }
        $sql = "UPDATE propietarios SET nombre = :nombre, apellido = :apellido, cedula = :cedula, telefono = :telefono, correo = :correo WHERE id_propietario = :id_propietario";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_propietario", $this->id_propietario);
        $conexion->bindParam(":nombre", $this->nombre);
        $conexion->bindParam(":apellido", $this->apellido);
        $conexion->bindParam(":cedula", $this->cedula);
        $conexion->bindParam(":telefono", $this->telefono);
        $conexion->bindParam(":correo", $this->correo);
        $result = $conexion->execute();

        $this->registrar_bitacora(MODIFICAR, GESTIONAR_PROPIETARIOS, "Propietario: ".$this->nombre." ".$this->apellido); 

        if($result == true){
            return ["estatus"=>true, "mensaje"=>"Propietario editado correctamente"];
    } else{
            return ["estatus"=>false, "mensaje"=>"Error al editar el propietario"];
        }
    }

    public function eliminar_propietario(){
        $propietario_alterado = $this->consultar_propietario();
        $sql = "DELETE FROM propietarios WHERE id_propietario = :id_propietario";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_propietario", $this->id_propietario);
        $result = $conexion->execute();
        $this->registrar_bitacora(ELIMINAR, GESTIONAR_PROPIETARIOS, "Propietario: ".$propietario_alterado[0]["nombre"]." ".$propietario_alterado[0]["apellido"]); 

        if($result == true){
            return ["estatus"=>true, "mensaje"=>"Propietario eliminado correctamente"];
    } else{
            return ["estatus"=>false, "mensaje"=>"Error al eliminar el propietario"];
        }
    }

    public function lastId(){
        $sql = "SELECT MAX(id_propietario) as last_id FROM propietarios";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if($result == true){
            return ["estatus"=>true, "mensaje"=>$datos[0]["last_id"]];
    } else {
            return ["estatus"=>false, "mensaje"=>"Error al consultar el último id"];
        }
    }

    public function validarDatos ($consulta = "registrar"){
        // Validamos el id propietario en caso de editar o eliminar porque en registrar no existe
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_propietario) && !empty($this->id_propietario))) {
                return ["estatus"=>false, "mensaje"=>"El id propietario es requerido"];
            }  
        }
        if(is_numeric($this->id_propietario)){
            if (!($this->validarClaveForanea("propietario", "id_propietario", $this->id_propietario))) {
                return ["estatus"=>false, "mensaje"=>"El propietario seleccionado no existe"];
            }
            if ($consulta == "eliminar") {
                return ["estatus"=>true, "mensaje"=>"Propietario valido"];
            }
            else {
                return ["estatus"=>false, "mensaje"=>"El id del propietario no es valido"];
            }
        }

        // Validamos que los campos enviados si existan
        if (!(isset($this->nombre) && !empty($this->nombre))) {
            return ["estatus"=>false, "mensaje"=>"El nombre es requerido"];
        }
        if (!(isset($this->apellido) && !empty($this->apellido))) {
            return ["estatus"=>false, "mensaje"=>"El apellido es requerido"];
        }
        if (!(isset($this->cedula) && !empty($this->cedula))) {
            return ["estatus"=>false, "mensaje"=>"La cedula es requerida"];
        }
        if (!(isset($this->telefono) && !empty($this->telefono))) {
            return ["estatus"=>false, "mensaje"=>"El telefono es requerido"];
        }
        if (!(isset($this->correo) && !empty($this->correo))) {
            return ["estatus"=>false, "mensaje"=>"El correo es requerido"];
        }

        // Verificamos si los valores tienen los datos correctos
        if(!(is_string($this->nombre) && preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $this->nombre))){
            return ["estatus"=>false, "mensaje"=>"El nombre no es valido"];
        }
        if(!(is_string($this->apellido) && preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $this->apellido))){
            return ["estatus"=>false, "mensaje"=>"El apellido no es valido"];
        }
        if(!(is_numeric($this->cedula) && preg_match("/^[0-9]+$/", $this->cedula))){
            return ["estatus"=>false, "mensaje"=>"La cedula no es valida"];
        }
        if(!(is_numeric($this->telefono) && preg_match("/^[0-9]+$/", $this->telefono))){
            return ["estatus"=>false, "mensaje"=>"El telefono no es valido"];
        }
        if(!(is_string($this->correo) && filter_var($this->correo, FILTER_VALIDATE_EMAIL))){
            return ["estatus"=>false, "mensaje"=>"El correo no es valido"];
        }

        return ["estatus"=>true, "mensaje"=>"Propietario valido"];
    }


    //esta funcion es para revisar si una clave foranea existe, porque sino dara error la consulta
    private function validarClaveForanea($tabla,$nombreClave,$valor)
    {
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        return ($result)?true:false;
    }

    public function existe_cedula($cedula, $excluir_id = null) {
    $sql = "SELECT COUNT(*) as total FROM propietarios WHERE cedula = :cedula";
    if ($excluir_id) {
        $sql .= " AND id_propietario != :id";
    }
    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":cedula", $cedula);
    if ($excluir_id) {
        $conexion->bindParam(":id", $excluir_id);
    }
    $conexion->execute();
    $result = $conexion->fetch(PDO::FETCH_ASSOC);
    return $result['total'] > 0;
}

public function existe_correo($correo, $excluir_id = null) {
    $sql = "SELECT COUNT(*) as total FROM propietarios WHERE correo = :correo";
    if ($excluir_id) {
        $sql .= " AND id_propietario != :id";
    }
    $conexion = $this->get_conex()->prepare($sql);
    $conexion->bindParam(":correo", $correo);
    if ($excluir_id) {
        $conexion->bindParam(":id", $excluir_id);
    }
    $conexion->execute();
    $result = $conexion->fetch(PDO::FETCH_ASSOC);
    return $result['total'] > 0;
}
}





?>