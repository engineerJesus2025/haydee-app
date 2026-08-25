<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;
use haydee\enums\Accion;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Permisos extends Conexion
{
    private $id_permiso;
    private $accion;
    private $activo;

    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_permiso' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'permisos', 'campo' => 'id_permiso']
            ],
            'accion' => [
                'regex' => '/^[A-Za-z_]+$/'
            ]
        ];

        $camposPorOperacion = [
            'registrar_permiso' => ['accion'],
            'modificar_permiso' => ['id_permiso', 'accion'],
            'eliminar_permiso'  => ['id_permiso'],
            'consultar_permiso' => ['id_permiso']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function set_id_permiso($id) { $this->id_permiso = $id; }
    public function get_id_permiso() { return $this->id_permiso; }
    public function set_accion($accion) { $this->accion = $accion; }
    public function get_accion() { return $this->accion; }
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function _consultar()
    {
        $sql = "SELECT * FROM permisos WHERE activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _validar_permisos_usuarios()
    {
        $ids = $this->id_permiso;

        if (!is_array($ids) || empty($ids)) {
            throw new NegocioException('Datos inválidos: se esperaba un arreglo de IDs de permisos.', HttpCodigo::BAD_REQUEST->value);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id_permiso FROM permisos WHERE id_permiso IN ($placeholders)";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute($ids);
        $encontrados = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $faltantes = array_diff($ids, $encontrados);

        if (empty($faltantes)) {
            return ['estatus' => true, 'mensaje' => 'Todos los permisos existen.'];
        } else {
            throw new NegocioException('Se detectaron permisos inexistentes en la solicitud.', HttpCodigo::BAD_REQUEST->value);
        }
    }

    private function _registrar_permiso()
    {
        $sql = "INSERT INTO permisos (accion, activo) VALUES (:accion, 1)";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':accion' => $this->accion]);
        $lastId = $this->get_conex(TipoBaseDatos::SEGURIDAD)->lastInsertId();
        return ['estatus' => true, 'mensaje' => 'Permiso registrado correctamente', 'id' => $lastId];
    }

    private function _modificar_permiso()
    {
        $sql = "UPDATE permisos SET accion = :accion WHERE id_permiso = :id";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([
            ':accion' => $this->accion,
            ':id'     => $this->id_permiso
        ]);
        return ['estatus' => true, 'mensaje' => 'Permiso actualizado correctamente'];
    }

    private function _eliminar_permiso()
    {
        $sql = "UPDATE permisos SET activo = 0 WHERE id_permiso = :id";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':id' => $this->id_permiso]);
        return ['estatus' => true, 'mensaje' => 'Permiso eliminado correctamente'];
    }

    private function _consultar_permiso()
    {
        $sql = "SELECT * FROM permisos WHERE id_permiso = :id AND activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':id' => $this->id_permiso]);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dato) {
            throw new NegocioException('Permiso no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }
        return ['estatus' => true, 'datos' => $dato];
    }
}