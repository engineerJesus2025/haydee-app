<?php

require_once "modelo/conexion.php";

class Conciliacion_bancaria extends Conexion
{
    private $id_conciliacion;
    private $fecha_inicio;
    private $fecha_fin;
    private $estado;    
    private $saldo_inicio;
    private $saldo_fin;
    private $saldo_sistema;
    private $diferencia;
    private $tasa_dolar;
    private $ingreso_banco_no_correspondido;
    private $ingreso_sistema_no_correspondido;
    private $egreso_banco_no_correspondido;
    private $egreso_sistema_no_correspondido;
    private $observaciones;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_conciliacion($id_conciliacion){$this->id_conciliacion = $id_conciliacion;}

    public function get_id_conciliacion(){return $this->id_conciliacion;}

    public function set_fecha_inicio($fecha_inicio){$this->fecha_inicio = $fecha_inicio;}

    public function get_fecha_inicio(){return $this->fecha_inicio;}

    public function set_fecha_fin($fecha_fin){$this->fecha_fin = $fecha_fin;}

    public function get_fecha_fin(){return $this->fecha_fin;}

    public function set_estado($estado){$this->estado = $estado;}

    public function get_estado(){return $this->estado;}

    public function set_saldo_inicio($saldo_inicio){$this->saldo_inicio = $saldo_inicio;}

    public function get_saldo_inicio(){return $this->saldo_inicio;}

    public function set_saldo_fin($saldo_fin){$this->saldo_fin = $saldo_fin;}

    public function get_saldo_fin(){return $this->saldo_fin;}

    public function set_saldo_sistema($saldo_sistema){$this->saldo_sistema = $saldo_sistema;}

    public function get_saldo_sistema(){return $this->saldo_sistema;}

    public function set_diferencia($diferencia){$this->diferencia = $diferencia;}

    public function get_diferencia(){return $this->diferencia;}

    public function set_tasa_dolar($tasa_dolar){$this->tasa_dolar = $tasa_dolar;}

    public function get_tasa_dolar(){return $this->tasa_dolar;}

    public function set_ingreso_banco_no_correspondido($ingreso_banco_no_correspondido){$this->ingreso_banco_no_correspondido = $ingreso_banco_no_correspondido;}

    public function get_ingreso_banco_no_correspondido(){return $this->ingreso_banco_no_correspondido;}

    public function set_ingreso_sistema_no_correspondido($ingreso_sistema_no_correspondido){$this->ingreso_sistema_no_correspondido = $ingreso_sistema_no_correspondido;}

    public function get_ingreso_sistema_no_correspondido(){return $this->ingreso_sistema_no_correspondido;}

    public function set_egreso_banco_no_correspondido($egreso_banco_no_correspondido){$this->egreso_banco_no_correspondido = $egreso_banco_no_correspondido;}

    public function get_egreso_banco_no_correspondido(){return $this->egreso_banco_no_correspondido;}

    public function set_egreso_sistema_no_correspondido($egreso_sistema_no_correspondido){$this->egreso_sistema_no_correspondido = $egreso_sistema_no_correspondido;}

    public function get_egreso_sistema_no_correspondido(){return $this->egreso_sistema_no_correspondido;}

    public function set_observaciones($observaciones){$this->observaciones = $observaciones;}

    public function get_observaciones(){return $this->observaciones;}

    //HAsta aqui todo normal

    public function verificar_conciliacion()
    {
        $mes_buscar = date("m",$this->fecha_inicio);
        $anio_buscar = date("Y",$this->fecha_inicio);
        // $mes_buscar = "02";
    
        $sql = "SELECT * FROM conciliacion_bancaria WHERE YEAR(conciliacion_bancaria.fecha_inicio) = :anio && MONTH(conciliacion_bancaria.fecha_inicio) = :mes";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":anio", $anio_buscar);
        $conexion->bindParam(":mes", $mes_buscar);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function crear_conciliacion()
    {
        $sql = "INSERT INTO conciliacion_bancaria(fecha_inicio,fecha_fin,estado, saldo_inicio, saldo_fin, saldo_sistema, diferencia, tasa_dolar, ingreso_banco_no_correspondido, ingreso_sistema_no_correspondido, egreso_banco_no_correspondido, egreso_sistema_no_correspondido, observaciones) VALUES (:fecha_inicio,:fecha_fin,:estado,0,0,0,0,0,0,0,0,0,'Conciliacion')";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":fecha_inicio", $this->fecha_inicio);
        $conexion->bindParam(":fecha_fin", $this->fecha_fin);
        $conexion->bindParam(":estado", $this->estado);

        $result = $conexion->execute();

        if ($result) {
            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este usuario"];
        }
    }

    public function verificar_meses_conciliados()
    {
        $sql = "SELECT * FROM conciliacion_bancaria WHERE estado = :estado";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":estado", $this->estado);        

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function buscar_mes()
    {
        $mes_buscar = date("m",$this->fecha_inicio);
        $anio_buscar = date("Y",$this->fecha_inicio);
        // $mes_buscar = "02";
    
        $sql = "SELECT pagos.id_pago as id, pagos.monto, pagos.fecha, pagos.referencia, pagos.estado, apartamentos.nro_apartamento as remitente FROM pagos INNER JOIN pagos_mensualidad ON pagos_mensualidad.pago_id = pagos.id_pago INNER JOIN mensualidad ON mensualidad.id_mensualidad = pagos_mensualidad.mensualidad_id INNER JOIN apartamentos ON apartamentos.id_apartamento = mensualidad.apartamento_id WHERE MONTH(pagos.fecha) = :mes && YEAR(pagos.fecha) = :anio UNION SELECT gastos.id_gasto as id, gastos.monto, gastos.fecha, gastos.referencia, gastos.tipo_gasto, proveedores.nombre_proveedor as remitente FROM gastos INNER JOIN proveedores ON gastos.proveedor_id = proveedores.id_proveedor WHERE MONTH(gastos.fecha) = :mes && YEAR(gastos.fecha) = :anio ORDER BY fecha;";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":anio", $anio_buscar);
        $conexion->bindParam(":mes", $mes_buscar);

        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }




    //hace lo que dice
    public function consultar()
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT `id_usuario`, `apellido`, usuarios.nombre as nombre_usuario, `correo`, `contrasenia`, `rol_id`, roles.nombre as nombre_rol FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol ORDER BY id_usuario";

        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            $this->registrar_bitacora(CONSULTAR, GESTIONAR_USUARIOS, "TODOS LOS USUARIOS");//registra cuando se entra al modulo de ususario

            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function consultar_usuario()
    {
        $this->cambiar_db_seguridad();

        $sql = "SELECT `id_usuario`, `apellido`, usuarios.nombre as nombre_usuario, `correo`, `contrasenia`, `rol_id`, roles.nombre as nombre_rol FROM usuarios INNER JOIN roles ON usuarios.rol_id=roles.id_rol WHERE id_usuario = :usuario";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":usuario", $this->id_usuario);

        $result = $conexion->execute();        
        
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();

        if ($result == true) {
            return $datos;
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
        }
    }

    public function registrar()
    {
        //Validamos los datos obtenidos del controlador (validaciones back-end)
        $validaciones = $this->validarDatos();
        if(!($validaciones["estatus"])){return $validaciones;}
        
        $this->cambiar_db_seguridad();

        $sql = "INSERT INTO usuarios(apellido,nombre,correo,contrasenia,rol_id) VALUES (:apellido,:nombre,:correo,:contrasenia,:rol)";

        $contra_hash = password_hash($this->contra, PASSWORD_DEFAULT);        
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":apellido", $this->apellido);
        $conexion->bindParam(":nombre", $this->nombre);
        $conexion->bindParam(":correo", $this->correo);
        $conexion->bindParam(":contrasenia", $contra_hash);
        $conexion->bindParam(":rol", $this->rol_id);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();

        if ($result) {
            $id_ultimo = $this->lastId();//obtenemos el ultimo id
            $this->set_id_usuario($id_ultimo["mensaje"]);
            $usuario_alterado = $this->consultar_usuario();//lo consultamos

            $this->registrar_bitacora(REGISTRAR, GESTIONAR_USUARIOS, $usuario_alterado["nombre_usuario"] . " (" . $usuario_alterado["nombre_rol"] . ")");//registramos en la bitacora

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar registrar este usuario"];
        }
    }

    public function editar_usuario()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("editar");
        if(!($validaciones["estatus"])){return $validaciones;}        

        $this->cambiar_db_seguridad();

        $sql = "UPDATE usuarios SET apellido=:apellido,nombre=:nombre,correo=:correo,contrasenia=:contrasenia,rol_id=:rol WHERE id_usuario=:id_usuario";

        $contra_hash = password_hash($this->contra, PASSWORD_DEFAULT);


        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_usuario", $this->id_usuario);    
        $conexion->bindParam(":apellido", $this->apellido);
        $conexion->bindParam(":nombre", $this->nombre);
        $conexion->bindParam(":correo", $this->correo);
        $conexion->bindParam(":contrasenia", $contra_hash);
        $conexion->bindParam(":rol", $this->rol_id);

        $result = $conexion->execute();

        $this->cambiar_db_negocio();        
        
        if ($result) {
            $usuario_alterado = $this->consultar_usuario();
            $this->registrar_bitacora(MODIFICAR, GESTIONAR_USUARIOS, $usuario_alterado["nombre_usuario"] . " (" . $usuario_alterado["nombre_rol"] . ")");

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar editar este usuario"];
        }
    }

    public function eliminar_usuario()
    {
        //Validamos los datos obtenidos del controlador
        $validaciones = $this->validarDatos("eliminar");
        if(!($validaciones["estatus"])){return $validaciones;}
        
        $usuario_alterado = $this->consultar_usuario();

        $this->cambiar_db_seguridad();

        $sql = "DELETE FROM usuarios WHERE id_usuario = :id_usuario";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_usuario", $this->id_usuario);
        $result = $conexion->execute();

        $this->cambiar_db_negocio();
        
        if ($result) {
            $this->registrar_bitacora(ELIMINAR, GESTIONAR_USUARIOS, $usuario_alterado["nombre_usuario"] . " (" . $usuario_alterado["nombre_rol"] . ")");

            return ["estatus"=>true,"mensaje"=>"OK"];
        } else {
            return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error al intentar eliminar este usuario"];
        }
    }
    
    public function lastId()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_usuario) as last_id FROM usuarios";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        $this->cambiar_db_negocio();

        if ($result) {
            return ["estatus"=>true,"mensaje"=>$datos["last_id"]];
        } else {
            return ["estatus"=>false,"mensaje"=>"Error en la consulta"];
        } 
    }
    // validaciones back end (se llaman al registrar o modificar)
    private function validarDatos($consulta = "registrar")
    {   
        // Validamos el id usuario en caso de editar o eliminar porque en registrar no existe todavia
        if ($consulta == "editar" || $consulta == "eliminar") {
            if (!(isset($this->id_usuario))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

            if (empty($this->id_usuario)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

            if(is_numeric($this->id_usuario)){
                if (!($this->validarClaveForanea("usuarios","id_usuario",$this->id_usuario,true))) {
                    return ["estatus"=>false,"mensaje"=>"El usuario seleccionado no existe"];
                }
                if ($consulta == "eliminar") {return ["estatus"=>true,"mensaje"=>"OK"];}
            }
            else{return ["estatus"=>false,"mensaje"=>"El id del Usuario tiene debe ser un valor numerico entero"];}
        }
        // Validamos que los campos enviados si existan

        if (!(isset($this->apellido) && isset($this->nombre) && isset($this->correo) && isset($this->contra) && isset($this->rol_id))) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos no se recibieron correctamente"];}

        // Validamos que los campos enviados no esten vacios

        if (empty($this->apellido) || empty($this->nombre) ||empty($this->correo) || empty($this->contra) || empty($this->rol_id)) {return ["estatus"=>false,"mensaje"=>"Uno o varios de los campos requeridos estan vacios"];}

        // Verificamos si los valores tienen los datos que deberian
        
        if(!(is_string($this->apellido)) || !(preg_match("/^[A-Za-z \b]*$/",$this->apellido))){
            return ["estatus"=>false,"mensaje"=>"El campo 'apellido' no posee un valor valido"];
        }
        if(!(is_string($this->nombre)) || !(preg_match("/^[A-Za-z \b]*$/",$this->nombre))){
            return ["estatus"=>false,"mensaje"=>"El campo 'nombre' no posee un valor valido"];
        }
        if(!(is_string($this->correo)) || !(preg_match("/^[-A-Za-z0-9_.]{3,20}[@][A-Za-z0-9]{3,10}[.][A-Za-z]{2,3}$/",$this->correo))){
            return ["estatus"=>false,"mensaje"=>"El campo 'correo' no posee un valor valido"];
        }
        if(!(is_string($this->contra)) || !(preg_match("/^[A-Za-z0-9_.+*$#%&@]*$/",$this->contra))){
            return ["estatus"=>false,"mensaje"=>"El campo 'contraseña' no posee un valor valido"];
        }

        if(is_numeric($this->rol_id)){
            if (!($this->validarClaveForanea("roles","id_rol",$this->rol_id,true))) {
                return ["estatus"=>false,"mensaje"=>"El campo 'Rol' no posee un valor valido"];
            }            
        }
        else{
            return ["estatus"=>false,"mensaje"=>"El campo 'Rol' no posee un valor valido"];
        }
        
        return ["estatus"=>true,"mensaje"=>"OK"];
    }

    //esta funcion es para revisar si una clave foranea existe, porque sino dara error la consulta
    private function validarClaveForanea($tabla,$nombreClave,$valor,$seguridad = false)
    {
        if ($seguridad) {
            $this->cambiar_db_seguridad();
        }
        $sql="SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($seguridad) {
            $this->cambiar_db_negocio();
        }
        return ($result)?true:false;
    }
}
?>