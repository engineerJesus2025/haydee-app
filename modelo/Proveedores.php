<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
use PDO;

class Proveedores extends Conexion
{
    private $id_proveedor;
    private $nombre_proveedor;
    private $servicio;
    private $rif;
    private $direccion;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_proveedor($id_proveedor)
    {
        $this->id_proveedor = $id_proveedor;
    }
    public function get_id_proveedor()
    {
        return $this->id_proveedor;
    }
    public function set_nombre_proveedor($nombre_proveedor)
    {
        $this->nombre_proveedor = $nombre_proveedor;
    }
    public function get_nombre_proveedor()
    {
        return $this->nombre_proveedor;
    }
    public function set_servicio($servicio)
    {
        $this->servicio = $servicio;
    }
    public function get_servicio()
    {
        return $this->servicio;
    }
    public function set_rif($rif)
    {
        $this->rif = $rif;
    }
    public function get_rif()
    {
        return $this->rif;
    }
    public function set_direccion($direccion)
    {
        $this->direccion = $direccion;
    }
    public function get_direccion()
    {
        return $this->direccion;
    }

    public function realizar_consulta($accion)
    {
        switch ($accion) {
            case "consultar":
                $respuesta = $this->consultar();
                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Error al consultar los proveedores"];
                }

            case "consultar_proveedor":
                $respuesta = $this->consultar_proveedor();
                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Error al consultar el proveedor"];
                }
            case "registrar":
                $validacion = $this->validar_datos('registrar');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si falla, retorna el mensaje de error
                }
                $respuesta = $this->registrar();
                if ($respuesta) {
                    return ["estatus" => true, "mensaje" => "Proveedor registrado correctamente"];
                } else {
                    return ["estatus" => false, "mensaje" => "Error al registrar el proveedor"];
                }
            case "modificar":
                $validacion = $this->validar_datos('modificar');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si falla, retorna el mensaje de error
                }
                $respuesta = $this->editar_proveedor();
                if ($respuesta) {
                    return ["estatus" => true, "mensaje" => "Proveedor modificado correctamente"];
                } else {
                    return ["estatus" => false, "mensaje" => "Error al modificar el proveedor"];
                }
            case "eliminar":
                $validacion = $this->validar_datos('eliminar');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si falla, retorna el mensaje de error
                }
                $respuesta = $this->eliminar_proveedor();
                if ($respuesta) {
                    return ["estatus" => true, "mensaje" => "Proveedor eliminado correctamente"];
                } else {
                    return ["estatus" => false, "mensaje" => "Error al eliminar el proveedor"];
                }

            case "lastId":
                return $this->lastId();


            default:
                return ["estatus" => false, "mensaje" => "Acción no válida"];
                break;
        }
    }

    private function consultar()
    {
        $sql = "SELECT id_proveedor,nombre_proveedor,servicio,rif,direccion FROM proveedores";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        //$this->registrar_bitacora(CONSULTAR, GESTIONAR_PROPIETARIOS, "TODOS LOS USUARIOS");//registra cuando se entra al modulo de propietarios
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);
        return ["resultado" => $result, "datos" => $datos];
    }

    private function consultar_proveedor()
    {
        $sql = "SELECT id_proveedor,nombre_proveedor,servicio,rif, direccion FROM proveedores WHERE id_proveedor = :id_proveedor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_proveedor", $this->id_proveedor);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        return ["resultado" => $result, "datos" => $datos];
    }

    private function registrar()
    {
        //
        $sql = "INSERT INTO proveedores (nombre_proveedor, servicio, rif, direccion) VALUES (:nombre_proveedor, :servicio, :rif, :direccion)";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre_proveedor", $this->nombre_proveedor);
        $conexion->bindParam(":servicio", $this->servicio);
        $conexion->bindParam(":rif", $this->rif);
        $conexion->bindParam(":direccion", $this->direccion);
        $result = $conexion->execute();
        return $result;

    }

    private function editar_proveedor()
    {
        $sql = "UPDATE proveedores SET nombre_proveedor = :nombre_proveedor, servicio = :servicio, rif = :rif, direccion = :direccion WHERE id_proveedor = :id_proveedor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_proveedor", $this->id_proveedor);
        $conexion->bindParam(":nombre_proveedor", $this->nombre_proveedor);
        $conexion->bindParam(":servicio", $this->servicio);
        $conexion->bindParam(":rif", $this->rif);
        $conexion->bindParam(":direccion", $this->direccion);
        $result = $conexion->execute();
        return $result;
    }

    private function eliminar_proveedor()
    {
        $sql = "DELETE FROM proveedores WHERE id_proveedor = :id_proveedor";
        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_proveedor", $this->id_proveedor);
        $result = $conexion->execute();
        return $result;
        if ($result == true) {
            $this->registrar_bitacora(ELIMINAR, GESTIONAR_PROVEEDORES, "Proveedor " . $this->nombre_proveedor . " de " . $this->servicio);
            return ["estatus" => true, "mensaje" => "Proveedor eliminado correctamente"];
        } else {
            return ["estatus" => false, "mensaje" => "Error al eliminar el proveedor"];
        }
    }

private function lastId()
{
    $sql = "SELECT MAX(id_proveedor) as last_id FROM proveedores";
    $conexion = $this->get_conex()->prepare($sql);
    $conexion->execute();
    $datos = $conexion->fetch(PDO::FETCH_ASSOC);

    return ["estatus" => true, "last_id" => $datos['last_id']];
}

    private function validarClaveForanea($tabla, $nombreClave, $valor)
    {
        $sql = "SELECT * FROM $tabla WHERE $nombreClave =:valor";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);
        $conexion->execute();
        $result = $conexion->fetch(PDO::FETCH_ASSOC);

        return ($result) ? true : false;
    }

    public function validar_datos($accion)
    {
         if ($accion == "modificar" || $accion == "eliminar") {
            
            // Validación 1.1: ID vacío
            if (empty(trim($this->id_proveedor))) {
                return ["estatus" => false, "mensaje" => "El id del proveedor requerido esta vacio"];
            }

            // Validación 1.2: ID no es numérico
            if (!is_numeric($this->id_proveedor)) {
                 return ["estatus"=>false, "mensaje"=>"El id del proveedor debe ser un valor numerico"];
            }

            // Validación 1.3: ID no existe en la BD
            if (!($this->validarClaveForanea("proveedores", "id_proveedor", $this->id_proveedor))) {
                return ["estatus" => false, "mensaje" => "El proveedor seleccionado no existe"];
            }

            // Si la acción es 'eliminar' y pasó las validaciones de ID, terminamos.
            if ($accion == "eliminar") {
                return ["estatus" => true, "mensaje" => "OK"];
            }
        }
        // 1. Validación de campos no vacíos
        if (empty(trim($this->nombre_proveedor))) {
            return ["estatus" => false, "mensaje" => "El nombre del proveedor no puede estar vacío."];
        }
        if (empty(trim($this->servicio))) {
            return ["estatus" => false, "mensaje" => "El servicio no puede estar vacío."];
        }
        if (empty(trim($this->rif))) {
            return ["estatus" => false, "mensaje" => "El RIF no puede estar vacío."];
        }
        if (empty(trim($this->direccion))) {
            return ["estatus" => false, "mensaje" => "La dirección no puede estar vacía."];
        }

        // 3. Validación de unicidad (que no exista otro proveedor con el mismo nombre o RIF)
        $validacion_nombre = $this->verificar_campo_unico('nombre_proveedor', $this->nombre_proveedor);
        if ($validacion_nombre['existe']) {
            return ["estatus" => false, "mensaje" => "Ya existe un proveedor con ese nombre."];
        }

        $validacion_rif = $this->verificar_campo_unico('rif', $this->rif);
        if ($validacion_rif['existe']) {
            return ["estatus" => false, "mensaje" => "Ya existe un proveedor con ese RIF."];
        }

        // Si todas las validaciones pasan
        return ["estatus" => true];
    }

    private function verificar_campo_unico($campo, $valor)
    {
        $sql = "SELECT COUNT(*) FROM proveedores WHERE $campo = :valor";

        // Si estamos modificando, excluimos el registro actual
        if (!empty($this->id_proveedor)) {
            $sql .= " AND id_proveedor != :id_proveedor";
        }

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":valor", $valor);

        if (!empty($this->id_proveedor)) {
            $conexion->bindParam(":id_proveedor", $this->id_proveedor);
        }

        $conexion->execute();
        $count = $conexion->fetchColumn();

        return ["existe" => $count > 0];
    }
}