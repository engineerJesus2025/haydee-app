<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Modulos extends Conexion
{
    private $id_modulo;
    private $nombre;
    private $activo;

    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_modulo' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'modulos', 'campo' => 'id_modulo']
            ],
            'nombre' => [
                'regex' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ _\s]{3,30}$/'
            ]
        ];

        $camposPorOperacion = [
            'registrar_modulo' => ['nombre'],
            'modificar_modulo' => ['id_modulo', 'nombre'],
            'eliminar_modulo'  => ['id_modulo'],
            'consultar_modulo' => ['id_modulo']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function set_id_modulo($id) { $this->id_modulo = $id; }
    public function get_id_modulo() { return $this->id_modulo; }
    public function set_nombre($nombre) { $this->nombre = $nombre; }
    public function get_nombre() { return $this->nombre; }
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
        $sql = "SELECT * FROM modulos WHERE activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _registrar_modulo()
    {
        $sql = "INSERT INTO modulos (nombre, activo) VALUES (:nombre, 1)";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':nombre' => $this->nombre]);
        $lastId = $this->get_conex(TipoBaseDatos::SEGURIDAD)->lastInsertId();
        return ['estatus' => true, 'mensaje' => 'Módulo registrado correctamente', 'id' => $lastId];
    }

    private function _modificar_modulo()
    {
        $sql = "UPDATE modulos SET nombre = :nombre WHERE id_modulo = :id";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([
            ':nombre' => $this->nombre,
            ':id'     => $this->id_modulo
        ]);
        return ['estatus' => true, 'mensaje' => 'Módulo actualizado correctamente'];
    }

    private function _eliminar_modulo()
    {
        $sql = "UPDATE modulos SET activo = 0 WHERE id_modulo = :id";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':id' => $this->id_modulo]);
        return ['estatus' => true, 'mensaje' => 'Módulo eliminado correctamente'];
    }

    private function _consultar_modulo()
    {
        $sql = "SELECT * FROM modulos WHERE id_modulo = :id AND activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':id' => $this->id_modulo]);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dato) {
            throw new NegocioException('Módulo no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }
        return ['estatus' => true, 'datos' => $dato];
    }
}