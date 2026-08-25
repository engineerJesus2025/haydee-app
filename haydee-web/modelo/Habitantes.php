<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\Sexo;
use haydee\enums\TipoVinculo;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Habitantes extends Conexion
{
    private $id_habitante;
    private $nombre;
    private $apellido;
    private $cedula;
    private $telefono;
    private $correo;
    private $fecha_nacimiento;
    private $sexo;
    private $activo;

    private $filtros_reporte = [];

    private $nuevo_apartamento_id;
    private $nuevo_tipo_vinculo;

    public static function obtenerReglas($operacion) {
        $sexosValidos = implode('|', array_column(Sexo::cases(), 'value'));
        $vinculosValidos = implode('|', array_column(TipoVinculo::cases(), 'value'));
        
        $reglasGenerales = [
            'id_habitante' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'habitantes', 'campo' => 'id_habitante']
            ],
            'nombre' => [
                'regex' => '/^[A-Za-z ]{3,30}$/'
            ],
            'apellido' => [
                'regex' => '/^[A-Za-z ]{3,30}$/'
            ],
            'cedula' => [
                'regex' => '/^[VE]{1}[0-9]{7,8}$/',
                'unique' => ['tabla' => 'habitantes', 'campo' => 'cedula', 'exclude_field' => 'id_habitante']
            ],
            'telefono' => [
                'regex' => '/^\d{11}$/'
            ],
            'correo' => [
                'regex' => '/^[-A-Za-z0-9_.]{3,35}@[A-Za-z0-9]{3,10}\.[A-Za-z]{2,3}$/',
                'unique' => ['tabla' => 'habitantes', 'campo' => 'correo', 'exclude_field' => 'id_habitante']
            ],
            'fecha_nacimiento' => [
                'regex' => '/^\d{4}-\d{2}-\d{2}$/'
            ],
            'sexo' => [
                'regex' => "/^($sexosValidos)$/"
            ],
            'nuevo_tipo_vinculo' => [
                'regex' => "/^($vinculosValidos)$/",
                'opcional' => true
            ],
            'apartamento_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
            ]
        ];

        $camposPorOperacion = [
            'registrar_habitantes'          => ['nombre', 'apellido', 'cedula', 'telefono', 'correo', 'fecha_nacimiento', 'sexo', 'apartamento_id', 'tipo_vinculo'],
            'modificar_habitantes'          => ['id_habitante', 'nombre', 'apellido', 'cedula', 'telefono', 'correo', 'fecha_nacimiento', 'sexo', 'apartamento_id', 'tipo_vinculo'],
            'eliminar_habitantes'           => ['id_habitante'],
            'consulta_especifica_habitante' => ['id_habitante']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function set_id_habitante($id) { $this->id_habitante = $id; }
    public function get_id_habitante() { return $this->id_habitante; }
    public function set_nombre($nombre) { $this->nombre = $nombre; }
    public function get_nombre() { return $this->nombre; }
    public function set_apellido($apellido) { $this->apellido = $apellido; }
    public function get_apellido() { return $this->apellido; }
    public function set_cedula($cedula) { $this->cedula = $cedula; }
    public function get_cedula() { return $this->cedula; }
    public function set_telefono($tel) { $this->telefono = $tel; }
    public function get_telefono() { return $this->telefono; }
    public function set_correo($correo) { $this->correo = $correo; }
    public function get_correo() { return $this->correo; }
    public function set_fecha_nacimiento($fecha) { $this->fecha_nacimiento = $fecha; }
    public function get_fecha_nacimiento() { return $this->fecha_nacimiento; }
    public function set_sexo($sexo) { $this->sexo = $sexo; }
    public function get_sexo() { return $this->sexo; }
    public function set_activo($activo) { $this->activo = $activo; }
    public function get_activo() { return $this->activo; }

    public function set_filtros_reporte($filtros) { $this->filtros_reporte = $filtros; }
    public function set_nuevo_apartamento_id($id) { $this->nuevo_apartamento_id = $id; }
    public function get_nuevo_apartamento_id() { return $this->nuevo_apartamento_id; }
    public function set_nuevo_tipo_vinculo($tipo) { $this->nuevo_tipo_vinculo = $tipo; }
    public function get_nuevo_tipo_vinculo() { return $this->nuevo_tipo_vinculo; }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function _consultar_habitante()
    {
        $sql = "SELECT 
                h.id_habitante,
                h.nombre,
                h.apellido,
                h.cedula,
                h.telefono,
                h.correo,
                h.fecha_nacimiento,
                h.sexo,
                a.nro_apartamento AS apartamento,
                a.gas,
                a.agua,
                a.alquilado,
                a.porcentaje_participacion,
                ha.tipo_vinculo,
                ha.apartamento_id
            FROM habitantes h
            LEFT JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
            LEFT JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
            WHERE h.id_habitante = :id_habitante AND h.activo = 1";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_habitante', $this->id_habitante);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('Habitante no encontrado.', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true, 'datos' => $datos];
    }

    private function _registrar_habitantes()
    {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);

        if (!empty($this->nuevo_apartamento_id) && $this->nuevo_tipo_vinculo === 'Propietario') {
            $sqlCheck = "SELECT COUNT(*) FROM habitantes_apartamentos WHERE apartamento_id = :id AND tipo_vinculo = 'Propietario'";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $this->nuevo_apartamento_id]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new NegocioException('Este apartamento ya tiene un propietario asignado.', HttpCodigo::BAD_REQUEST->value);
            }
        }

        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO habitantes (nombre, apellido, cedula, telefono, correo, fecha_nacimiento, sexo)
                    VALUES (:nombre, :apellido, :cedula, :telefono, :correo, :fecha_nacimiento, :sexo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $this->nombre,
                ':apellido' => $this->apellido,
                ':cedula' => $this->cedula,
                ':telefono' => $this->telefono,
                ':correo' => $this->correo,
                ':fecha_nacimiento' => $this->fecha_nacimiento,
                ':sexo' => $this->sexo
            ]);
            $idHabitante = $pdo->lastInsertId();

            if (!empty($this->nuevo_apartamento_id)) {
                $sqlRel = "INSERT INTO habitantes_apartamentos (apartamento_id, habitante_id, tipo_vinculo)
                           VALUES (:aid, :hid, :tipo)";
                $stmtRel = $pdo->prepare($sqlRel);
                $stmtRel->execute([
                    ':aid' => $this->nuevo_apartamento_id,
                    ':hid' => $idHabitante,
                    ':tipo' => $this->nuevo_tipo_vinculo
                ]);
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Habitante registrado correctamente', 'lastId' => $idHabitante];

        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    private function _modificar_habitantes()
    {
        $pdo = $this->get_conex(TipoBaseDatos::NEGOCIO);

        if (!empty($this->nuevo_apartamento_id) && $this->nuevo_tipo_vinculo === 'Propietario') {
            $sqlCheck = "SELECT COUNT(*) FROM habitantes_apartamentos 
                         WHERE apartamento_id = :aid AND tipo_vinculo = 'Propietario' AND habitante_id != :hid";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([':aid' => $this->nuevo_apartamento_id, ':hid' => $this->id_habitante]);
            if ($stmtCheck->fetchColumn() > 0) {
                throw new NegocioException('Este apartamento ya tiene otro propietario asignado.', HttpCodigo::BAD_REQUEST->value);
            }
        }

        try {
            $pdo->beginTransaction();

            $sql = "UPDATE habitantes SET 
                        nombre = :nombre, apellido = :apellido, cedula = :cedula, 
                        telefono = :telefono, correo = :correo, fecha_nacimiento = :fecha_nacimiento, sexo = :sexo
                    WHERE id_habitante = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $this->nombre,
                ':apellido' => $this->apellido,
                ':cedula' => $this->cedula,
                ':telefono' => $this->telefono,
                ':correo' => $this->correo,
                ':fecha_nacimiento' => $this->fecha_nacimiento,
                ':sexo' => $this->sexo,
                ':id' => $this->id_habitante
            ]);

            $sqlDel = "DELETE FROM habitantes_apartamentos WHERE habitante_id = :hid";
            $stmtDel = $pdo->prepare($sqlDel);
            $stmtDel->execute([':hid' => $this->id_habitante]);

            if (!empty($this->nuevo_apartamento_id)) {
                $sqlRel = "INSERT INTO habitantes_apartamentos (apartamento_id, habitante_id, tipo_vinculo)
                           VALUES (:aid, :hid, :tipo)";
                $stmtRel = $pdo->prepare($sqlRel);
                $stmtRel->execute([
                    ':aid' => $this->nuevo_apartamento_id,
                    ':hid' => $this->id_habitante,
                    ':tipo' => $this->nuevo_tipo_vinculo
                ]);
            }

            $pdo->commit();
            return ['estatus' => true, 'mensaje' => 'Habitante actualizado correctamente'];

        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    private function _eliminar_habitantes()
    {
        $sql = "UPDATE habitantes SET activo = 0 WHERE id_habitante = :id_habitante";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_habitante', $this->id_habitante);
        $stmt->execute();
        return ['estatus' => true, 'mensaje' => 'Habitante eliminado correctamente'];
    }

    private function _existe_habitante()
    {
        $sql = "SELECT COUNT(*) FROM habitantes WHERE id_habitante = :id_habitante AND activo = 1";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->bindParam(':id_habitante', $this->id_habitante);
        $stmt->execute();
        $conteo = $stmt->fetchColumn();
        
        return ['estatus' => true, 'existe' => ($conteo > 0)];
    }
}