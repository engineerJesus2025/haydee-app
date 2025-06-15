<?php
require_once "modelo/conexion.php";

class Cartelera_virtual extends Conexion
{
    private $id_cartelera;
    private $titulo;
    private $descripcion;
    private $fecha;
    private $tipo;
    private $imagen;
    private $prioridad;
    private $usuario_id;

    public function __construct()
    {
        parent::__construct();
    }
    public function set_id_cartelera($id_cartelera)
    {
        $this->id_cartelera = $id_cartelera;
    }
    public function get_id_cartelera()
    {
        return $this->id_cartelera;
    }
    public function set_titulo($titulo)
    {
        $this->titulo = $titulo;
    }
    public function get_titulo()
    {
        return $this->titulo;
    }
    public function set_descripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }
    public function get_descripcion()
    {
        return $this->descripcion;
    }
    public function set_fecha($fecha)
    {
        $this->fecha = $fecha;
    }
    public function get_fecha()
    {
        return $this->fecha;
    }
    public function set_tipo($tipo)
    {
        $this->tipo = $tipo;
    }
    public function get_tipo()
    {
        return $this->tipo;
    }
    public function set_imagen($imagen)
    {
        $this->imagen = $imagen;
    }
    public function get_imagen()
    {
        return $this->imagen;
    }
    public function set_prioridad($prioridad)
    {
        $this->prioridad = $prioridad;
    }
    public function get_prioridad()
    {
        return $this->prioridad;
    }
    public function set_usuario_id($usuario_id)
    {
        $this->usuario_id = $usuario_id;
    }
    public function get_usuario_id()
    {
        return $this->usuario_id;
    }

    public function consultar()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, usuarios.nombre as nombre_usuario 
            FROM cartelera_virtual
            INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
            ORDER BY fecha ASC";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();

        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();
        if ($result) {
            $this->registrar_bitacora(CONSULTAR, GESTIONAR_CARTELERA_VIRTUAL, "TODAS LAS PUBLICACIONES");//registra cuando se entra al modulo de cart virtual
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Error al consultar los datos"];
        }
    }

    public function consultar_cartelera_id()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT cv.*, u.nombre AS nombre_usuario
        FROM cartelera_virtual cv
        INNER JOIN usuarios u ON cv.usuario_id = u.id_usuario
        WHERE cv.id_cartelera = :id_cartelera";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_cartelera", $this->id_cartelera);
        $result = $conexion->execute();

        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        $this->cambiar_db_negocio();
        if ($result) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Error al consultar los datos"];
        }
    }

    public function registrar()
    {
        $this->cambiar_db_seguridad();
        $sql = "INSERT INTO cartelera_virtual (titulo, descripcion, fecha, imagen, prioridad, usuario_id) VALUES (:titulo, :descripcion, :fecha, :imagen, :prioridad, :usuario_id)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":titulo", $this->titulo);
        $conexion->bindParam(":descripcion", $this->descripcion);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":imagen", $this->imagen);
        $conexion->bindParam(":prioridad", $this->prioridad);
        $conexion->bindParam(":usuario_id", $this->usuario_id);
        $result = $conexion->execute();
        $this->cambiar_db_negocio();

        if ($result) {
            $id_ultimo = $this->lastId();
            $this->set_id_cartelera($id_ultimo['mensaje']);
            $cartelera_alterada = $this->consultar_cartelera_id();
            $this->registrar_bitacora(REGISTRAR, GESTIONAR_CARTELERA_VIRTUAL, "Titulo: ".$this->titulo); 

            // Notificación
            $this->registrar_notificacion();


            return ["estatus" => true, "mensaje" => "OK"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al registrar los datos"];
        }
    }

  public function registrar_notificacion() {
    $this->cambiar_db_seguridad();
    $titulo = "Cartelera Virtual";
    $descripcion = "Se ha registrado una nueva publicación en la cartelera virtual.";
    $fecha = date('Y-m-d'); 
    $activo = "0";

    // Obtener todos los usuarios con rol de propietario (rol_id = 2)
    $sql_usuarios = "SELECT id_usuario FROM usuarios WHERE rol_id = 3";
    $conexion_usuarios = $this->get_conex()->prepare($sql_usuarios);
    $conexion_usuarios->execute();
    $usuarios = $conexion_usuarios->fetchAll(PDO::FETCH_ASSOC);

    $sql = "INSERT INTO notificaciones (titulo, descripcion, fecha, usuario_id, activo) 
            VALUES (:titulo, :descripcion, :fecha, :usuario_id, :activo)";
    $conexion = $this->get_conex()->prepare($sql);

    $result = true;

    foreach ($usuarios as $usuario) {
        $usuario_id = $usuario['id_usuario'];
        
        $conexion->bindParam(":titulo", $titulo);
        $conexion->bindParam(":descripcion", $descripcion);
        $conexion->bindParam(":fecha", $fecha);
        $conexion->bindParam(":usuario_id", $usuario_id);
        $conexion->bindParam(":activo", $activo);
        $this->cambiar_db_negocio();


        if (!$conexion->execute()) {
            $result = false;
        }
    }

    return $result;
    }

    public function editar_publicacion()
    {
        $this->cambiar_db_seguridad();
        $sql = "UPDATE cartelera_virtual SET titulo = :titulo, descripcion = :descripcion, fecha = :fecha, imagen = :imagen, prioridad = :prioridad, usuario_id = :usuario_id WHERE id_cartelera = :id_cartelera";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_cartelera", $this->id_cartelera);
        $conexion->bindParam(":titulo", $this->titulo);
        $conexion->bindParam(":descripcion", $this->descripcion);
        $conexion->bindParam(":fecha", $this->fecha);
        $conexion->bindParam(":imagen", $this->imagen);
        $conexion->bindParam(":prioridad", $this->prioridad);
        $conexion->bindParam(":usuario_id", $this->usuario_id);
        $result = $conexion->execute();
        $this->cambiar_db_negocio();
        // $this->registrar_bitacora(MODIFICAR, GESTIONAR_CARTELERA_VIRTUAL, "ID: ".$this->id_cartelera);//registra cuando se edita un cartelera

        if ($result) {
            $cartelera_alterada = $this->consultar_cartelera_id();
            $this->registrar_bitacora(MODIFICAR, GESTIONAR_CARTELERA_VIRTUAL, "Titulo: ".$this->titulo);
            return ["estatus" => true, "mensaje" => "Edición exitosa"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al editar los datos"];
        }
    }

    public function eliminar_publicacion()
    {
        $publicacion_alterada = $this->consultar_cartelera_id();
        $this->cambiar_db_seguridad();
        $sql = "DELETE FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_cartelera", $this->id_cartelera);
        $result = $conexion->execute();
        $this->cambiar_db_negocio();
        // $this->registrar_bitacora(ELIMINAR, GESTIONAR_CARTELERA_VIRTUAL, "ID: ".$this->id_cartelera);//registra cuando se elimina un cartelera

        if ($result) {
            $this->registrar_bitacora(ELIMINAR, GESTIONAR_CARTELERA_VIRTUAL, "Titulo: ".$publicacion_alterada[0]["titulo"]);
            return ["estatus" => true, "mensaje" => "Eliminación exitosa"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al eliminar los datos"];
        }
    }

    public function lastId()
    {
        $this->cambiar_db_seguridad();
        $sql = "SELECT MAX(id_cartelera) as last_id FROM cartelera_virtual";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        $this->cambiar_db_negocio();
        if ($result) {
            return ["estatus" => true, "mensaje" => $datos[0]['last_id']];
        } else {
            return ["estatus" => false, "mensaje" => "Error al consultar los datos"];
        }
    }

    private function validarClaveForanea($tabla, $nombreClave, $valor, $seguridad = false)
    {
        if ($seguridad) {
            $this->cambiar_db_seguridad();
        }
        $sql = "SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        if ($seguridad) {
            $this->cambiar_db_negocio();
        }
        return ($result) ? true : false;
    }

    // EL MISMO NOMBRE DE LA FUNCION TE DICE PARA QUE ES XD
    public function obtener_imagen_actual()
    {
        $this->cambiar_db_seguridad(); 
        $sql = "SELECT imagen FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_cartelera", $this->id_cartelera);
        $conexion->execute();
        $this->cambiar_db_negocio();

        $resultado = $conexion->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado["imagen"] : null;
    }

    private function validarDatos($operacion = "registrar")
    {
    // Validar ID de usuario en todos los casos
    if (!isset($this->usuario_id) || empty($this->usuario_id)) {
        return ["estatus" => false, "mensaje" => "El usuario no fue especificado correctamente"];
    }

    if (!is_numeric($this->usuario_id)) {
        return ["estatus" => false, "mensaje" => "El ID del usuario debe ser numérico"];
    }

    if (!$this->validarClaveForanea("usuarios", "id_usuario", $this->usuario_id, true)) {
        return ["estatus" => false, "mensaje" => "El usuario asociado no existe"];
    }

    // Solo validamos el resto de campos si es registrar o editar
    if (in_array($operacion, ["registrar", "editar"])) {

        if (!isset($this->titulo, $this->descripcion, $this->fecha, $this->prioridad)) {
            return ["estatus" => false, "mensaje" => "Uno o varios campos requeridos no se recibieron correctamente"];
        }

        if (empty($this->titulo) || empty($this->descripcion) || empty($this->fecha) || empty($this->prioridad)) {
            return ["estatus" => false, "mensaje" => "Uno o varios campos requeridos están vacíos"];
        }

        // Validación de título y descripción: letras, números, signos básicos, acentos, etc.
        $textoRegex = "/^[A-Za-zÁÉÍÓÚáéíóú0-9.,;()'\"!?¡¿%°\- ]{3,200}$/";

        if (!preg_match($textoRegex, $this->titulo)) {
            return ["estatus" => false, "mensaje" => "El título no posee un formato válido"];
        }

        if (!preg_match($textoRegex, $this->descripcion)) {
            return ["estatus" => false, "mensaje" => "La descripción no posee un formato válido"];
        }

        // Validación de fecha con formato YYYY-MM-DD
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $this->fecha)) {
            return ["estatus" => false, "mensaje" => "La fecha no tiene un formato válido (YYYY-MM-DD)"];
        }

        // Validación de prioridad como numérica (o puedes hacer lista de valores válidos si aplica)
        if (!is_numeric($this->prioridad)) {
            return ["estatus" => false, "mensaje" => "La prioridad debe ser un valor numérico"];
        }
    }

    return ["estatus" => true, "mensaje" => "OK"];
    }

    // Esta te la metio jesus, francisco
    public function consultar_inicio($limite)
    {
        $limite_int = intval($limite);
        $this->cambiar_db_seguridad();
        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, imagen, descripcion, usuarios.nombre as nombre_usuario 
            FROM cartelera_virtual
            INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
            ORDER BY fecha LIMIT 2 OFFSET $limite_int";

        $conexion = $this->get_conex()->prepare($sql);
        // $conexion->bindParam(":limite", $limite_int);
        $result = $conexion->execute();

        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        $this->cambiar_db_negocio();
        if ($result) {
            return $datos;
        } else {
            return ["estatus" => false, "mensaje" => "Error al consultar los datos"];
        }
    }
}
?>