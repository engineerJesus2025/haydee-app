<?php
namespace haydee\modelo;
use haydee\modelo\Conexion;
use PDO;

class PermisosUsuarios extends Conexion
{
    private $id_permiso_usuario;
    private $nombre_accion;
    private $modulo_id;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_id_permiso_usuario($id_permiso_usuario)
    {
        $this->id_permiso_usuario = $id_permiso_usuario;
    }

    public function get_id_permiso_usuario()
    {
        return $this->id_permiso_usuario;
    }

    public function set_nombre_accion($nombre_accion)
    {
        $this->nombre_accion = $nombre_accion;
    }

    public function get_nombre_accion()
    {
        return $this->nombre_accion;
    }

    public function set_modulo_id($modulo_id)
    {
        $this->modulo_id = $modulo_id;
    }

    public function get_modulo_id()
    {
        return $this->modulo_id;
    }

    public function realizar_consulta($accion){
        $this->cambiar_db_seguridad();
        switch ($accion) {
            case 'consultar':
                $respuesta = $this->consultar();

                $this->cambiar_db_negocio();

                if ($respuesta["resultado"] == true) {
                    return $respuesta["datos"];
                } 
                else {
                    return ["estatus"=>false,"mensaje"=>"Ha ocurrido un error con la consulta"];
                }
            case 'validar_permisos_usuarios':
                $respuesta = $this->validar_permisos_usuarios();

                $this->cambiar_db_negocio();

                return $respuesta;

            default:
                return ["estatus"=>false,"mensaje"=>"A ocurrido un error en la consulta"];
                break;
        }
    }

    public function consultar()
    {        
        $sql = "SELECT * FROM permisos_usuarios";
        $conexion = $this->get_conex()->prepare($sql);
        $result = $conexion->execute();
        $datos = $conexion->fetchAll(PDO::FETCH_ASSOC);

        return ["resultado"=>$result,"datos"=>$datos];        
    }

    public function validar_permisos_usuarios()
    {
        $ids_a_validar = $this->id_permiso_usuario;

        if (!is_array($ids_a_validar) || empty($ids_a_validar)) {
            return ["estatus" => false, "mensaje" => "Datos inválidos: Se esperaba un arreglo de IDs."];
        }

        $placeholders = str_repeat('?,', count($ids_a_validar) - 1) . '?';

        $sql = "SELECT id_permiso_usuario FROM permisos_usuarios WHERE id_permiso_usuario IN ($placeholders)";

        // LE meti un try catch porque soy try hard
        try {
            $conexion = $this->get_conex()->prepare($sql);
            
            $conexion->execute($ids_a_validar);
            
            $ids_encontrados = $conexion->fetchAll(PDO::FETCH_COLUMN);

            $ids_faltantes = array_diff($ids_a_validar, $ids_encontrados);

            if (empty($ids_faltantes)) {
                // Si el array de faltantes está vacío, significa que encontró todos
                return [
                    "estatus" => true, 
                    "mensaje" => "ok"
                ];
            } else {
                // Si hay elementos, devolvemos el error y la lista de culpables :)
                return [
                    "estatus" => false, 
                    "mensaje" => "Se detectaron registros inexistentes en la base de datos.",
                    "ids_no_encontrados" => array_values($ids_faltantes) 
                ];
            }

        } catch (\Exception $e) {
            return [
                "estatus" => false, 
                "mensaje" => "Error interno al validar IDs: " . $e->getMessage()
            ];
        }
    }

}
?>