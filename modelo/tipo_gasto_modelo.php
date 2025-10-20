<?php
require_once("modelo/conexion.php");

class Tipo_gasto extends Conexion
{
    // Atributos
    private $id_tipo_gasto;
    private $nombre_tipo_gasto;

    public function __construct()
    {
        parent::__construct();
    }

    // Metodos setter y getter
    public function set_id_tipo_gasto($id_tipo_gasto)
    {
        $this->id_tipo_gasto = $id_tipo_gasto;
    }

    public function get_id_tipo_gasto()
    {
        return $this->id_tipo_gasto;
    }

    public function set_nombre_tipo_gasto($nombre_tipo_gasto)
    {
        $this->nombre_tipo_gasto = $nombre_tipo_gasto;
    }

    public function get_nombre_tipo_gasto()
    {
        return $this->nombre_tipo_gasto;
    }

    public function realizar_consulta($accion)
    {
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                if ($respuesta["resultado"]) {
                    $this->registrar_bitacora(CONSULTAR, GESTIONAR_TIPO_GASTO, "TODOS LOS TIPOS DE GASTO");//registra cuando se entra al modulo de tipos de gasto
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
                }


            case 'consultar_tipo_gasto':
                $respuesta = $this->consultar_tipo_gasto();
                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
                }

            case 'registrar':
                $validacion = $this->validar_datos('registrar');
                if (!$validacion["estatus"]) {
                    return $validacion; // Si la validación falla, devuelve el error y termina
                }

                $respuesta = $this->registrar_tipo_gasto();

                if ($respuesta) {

                    $this->registrar_bitacora(REGISTRAR, GESTIONAR_TIPO_GASTO, "Tipo Gasto " . $this->nombre_tipo_gasto);//registramos en la bitacora

                    return ["estatus" => true, "mensaje" => "OK"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar registrar este tipo de gasto"];
                }

            case 'modificar':
            $validacion = $this->validar_datos('modificar');
            if (!$validacion["estatus"]) {
                return $validacion; // Si la validación falla, devuelve el error y termina
            }

                $respuesta = $this->editar_tipo_gasto();
                if ($respuesta) {
                    $this->registrar_bitacora(MODIFICAR, GESTIONAR_TIPO_GASTO, "Tipo Gasto " . $this->nombre_tipo_gasto);

                    return ["estatus" => true, "mensaje" => "OK"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar editar este tipo de gasto"];
                }

            case 'eliminar':
                $respuesta = $this->eliminar_tipo_gasto();
                if ($respuesta) {
                    $this->registrar_bitacora(ELIMINAR, GESTIONAR_TIPO_GASTO, "Tipo Gasto " . $this->nombre_tipo_gasto);

                    return ["estatus" => true, "mensaje" => "OK"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error al intentar eliminar este tipo de gasto"];
                }

            case 'lastId':
                $respuesta = $this->lastId();

                if ($respuesta["resultado"]) {
                    return $respuesta["datos"];
                } else {
                    return ["estatus" => false, "mensaje" => "Ha ocurrido un error con la consulta"];
                }
            default:
                return ["estatus" => false, "mensaje" => "Ha ocurrido un error en la consulta"];
                break;
        }
    }


    // Metodos CRUD entre otro

    private function consultar()
    {
        $sql = "SELECT * FROM `tipo_gasto` ORDER BY id_tipo_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado" => $result, "datos" => $datos];
    }

    private function consultar_tipo_gasto()
    {

        $sql = "SELECT * FROM tipo_gasto WHERE id_tipo_gasto = :id_tipo_gasto";

        $conexion = $this->get_conex()->prepare($sql);

        $conexion->bindParam(":id_tipo_gasto", $this->id_tipo_gasto);

        $result = $conexion->execute();

        $datos = $conexion->fetch(PDO::FETCH_ASSOC);

        return ["resultado" => $result, "datos" => $datos];

    }

    private function registrar_tipo_gasto()
    {
        //Validamos los datos obtenidos del controlador (validaciones back-end)

        //$this->cambiar_db_seguridad();

        $sql = "INSERT INTO tipo_gasto(nombre_tipo_gasto) VALUES (:nombre_tipo_gasto)";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre_tipo_gasto", $this->nombre_tipo_gasto);
        $result = $conexion->execute();

        return $result;
    }

    private function editar_tipo_gasto()
    {
        $sql = "UPDATE tipo_gasto SET nombre_tipo_gasto=:nombre_tipo_gasto WHERE id_tipo_gasto=:id_tipo_gasto";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_tipo_gasto", $this->id_tipo_gasto);
        $conexion->bindParam(":nombre_tipo_gasto", $this->nombre_tipo_gasto);

        $result = $conexion->execute();
        return $result;
    }

    private function eliminar_tipo_gasto()
    {
        $sql = "DELETE FROM tipo_gasto WHERE id_tipo_gasto = :id_tipo_gasto";

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":id_tipo_gasto", $this->id_tipo_gasto);
        $result = $conexion->execute();

        return $result;
    }

    private function lastId()
    {
        $sql = "SELECT MAX(id_tipo_gasto) as last_id FROM tipo_gasto";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetch(PDO::FETCH_ASSOC);
        return ["resultado" => $result, "datos" => $datos];
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

    public function validar_datos($accion)
    {
        // 1. Validación de campo no vacío
        if (empty(trim($this->nombre_tipo_gasto))) {
            return ["estatus" => false, "mensaje" => "El nombre del tipo de gasto no puede estar vacío."];
        }

        // 2. Validación de longitud
        if (strlen($this->nombre_tipo_gasto) < 3) {
            return ["estatus" => false, "mensaje" => "El nombre debe tener al menos 3 caracteres."];
        }
        if (strlen($this->nombre_tipo_gasto) > 50) {
            return ["estatus" => false, "mensaje" => "El nombre no puede exceder los 50 caracteres."];
        }

        // 3. Validación de formato
        if (!preg_match('/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/u', $this->nombre_tipo_gasto)) {
            return ["estatus" => false, "mensaje" => "El nombre solo puede contener letras y espacios."];
        }

        // 4. Validación de nombre único (que no exista ya en la BD)
        if ($this->verificar_nombre_existente()) {
            return ["estatus" => false, "mensaje" => "Ya existe un tipo de gasto con este nombre."];
        }

        // Si todas las validaciones pasan
        return ["estatus" => true];
    }

    private function verificar_nombre_existente()
    {
        $sql = "SELECT COUNT(*) FROM tipo_gasto WHERE nombre_tipo_gasto = :nombre_tipo_gasto";

        // Si estamos editando, debemos excluir el registro actual de la verificación
        if (!empty($this->id_tipo_gasto)) {
            $sql .= " AND id_tipo_gasto != :id_tipo_gasto";
        }

        $conexion = $this->get_conex()->prepare($sql);
        $conexion->bindParam(":nombre_tipo_gasto", $this->nombre_tipo_gasto);

        if (!empty($this->id_tipo_gasto)) {
            $conexion->bindParam(":id_tipo_gasto", $this->id_tipo_gasto);
        }

        $conexion->execute();
        $count = $conexion->fetchColumn();

        return ($count > 0); // Si es mayor a 0, significa que ya existe
    }
}


?>