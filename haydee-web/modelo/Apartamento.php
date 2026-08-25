<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoVinculo;
use haydee\enums\EstadoOcupacion;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class Apartamento extends Conexion
{
    private $id_apartamento;
    private $nro_apartamento;
    private $porcentaje_participacion;
    private $gas;
    private $agua;
    private $alquilado;
    private $activo;

    private $correo;
    
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'id_apartamento' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'apartamentos', 'campo' => 'id_apartamento']
            ],
            'nro_apartamento' => [
                'regex' => '/^[0-9\-\b]{1,4}$/',
                'unique' => ['tabla' => 'apartamentos', 'campo' => 'nro_apartamento', 'exclude_field' => 'id_apartamento']
            ],
            'porcentaje_participacion' => [
                'regex' => '/^\d{1,2}(\.\d{1,2})?$/',
                'min' => 0.01,
                'max' => 100
            ],
            'gas' => [
                'regex' => '/^[12]$/',
            ],
            'agua' => [
                'regex' => '/^[12]$/',
            ],
            'alquilado' => [
                'regex' => '/^[12]$/',
            ],
            'correo' => [
                'regex' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'exists' => ['tabla' => 'habitantes', 'campo' => 'correo']
            ]
        ];

        $camposPorOperacion = [
            'registrar_apartamento'  => ['nro_apartamento', 'porcentaje_participacion', 'gas', 'agua', 'alquilado'],
            'modificar_apartamento'  => ['id_apartamento', 'nro_apartamento', 'porcentaje_participacion', 'gas', 'agua', 'alquilado'],
            'eliminar_apartamento'   => ['id_apartamento'],
            'consulta_especifica'    => ['id_apartamento'],
            'consultar_habitantes'   => ['id_apartamento'],
            'obtener_apartamentos_por_correo' => ['correo']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function set_id_apartamento($id) { $this->id_apartamento = $id; }
    public function get_id_apartamento() { return $this->id_apartamento; }
    public function set_nro_apartamento($n) { $this->nro_apartamento = $n; }
    public function get_nro_apartamento() { return $this->nro_apartamento; }
    public function set_porcentaje_participacion($p) { $this->porcentaje_participacion = $p; }
    public function get_porcentaje_participacion() { return $this->porcentaje_participacion; }
    public function set_gas($g) { $this->gas = $g; }
    public function get_gas() { return $this->gas; }
    public function set_agua($a) { $this->agua = $a; }
    public function get_agua() { return $this->agua; }
    public function set_alquilado($a) { $this->alquilado = $a; }
    public function get_alquilado() { return $this->alquilado; }
    public function set_activo($a) { $this->activo = $a; }
    public function get_activo() { return $this->activo; }

    public function set_correo($correo) { $this->correo = $correo; }
    public function get_correo() { return $this->correo; }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function _registrar_apartamento()
    {
        $gas = $this->gas ?? 0;
        $agua = $this->agua ?? 0;
        $alquilado = $this->alquilado ?? 0;

        $sql = "INSERT INTO apartamentos (nro_apartamento, porcentaje_participacion, gas, agua, alquilado, activo) 
                VALUES (:nro, :porc, :gas, :agua, :alq, 1)";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([
            ':nro'  => $this->nro_apartamento,
            ':porc' => $this->porcentaje_participacion,
            ':gas'  => $gas,
            ':agua' => $agua,
            ':alq'  => $alquilado
        ]);
        $lastId = $this->get_conex(TipoBaseDatos::NEGOCIO)->lastInsertId();
        return ['estatus' => true, 'mensaje' => 'Apartamento registrado correctamente', 'id' => $lastId];
    }

    private function _modificar_apartamento()
    {
        $gas = $this->gas ?? 0;
        $agua = $this->agua ?? 0;
        $alquilado = $this->alquilado ?? 0;

        $sql = "UPDATE apartamentos SET 
                    nro_apartamento = :nro,
                    porcentaje_participacion = :porc,
                    gas = :gas,
                    agua = :agua,
                    alquilado = :alq
                WHERE id_apartamento = :id";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([
            ':nro'  => $this->nro_apartamento,
            ':porc' => $this->porcentaje_participacion,
            ':gas'  => $gas,
            ':agua' => $agua,
            ':alq'  => $alquilado,
            ':id'   => $this->id_apartamento
        ]);
        return ['estatus' => true, 'mensaje' => 'Apartamento actualizado correctamente'];
    }

    private function _eliminar_apartamento()
    {
        $sql = "UPDATE apartamentos SET activo = 0 WHERE id_apartamento = :id";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':id' => $this->id_apartamento]);
        return ['estatus' => true, 'mensaje' => 'Apartamento eliminado correctamente'];
    }

    private function _consultar_listado()
    {
        $sql = "SELECT a.*, 
                       (SELECT CONCAT(h.nombre, ' ', h.apellido) 
                        FROM habitantes h 
                        JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id 
                        WHERE ha.apartamento_id = a.id_apartamento AND ha.tipo_vinculo = 'Propietario' 
                        LIMIT 1) as propietario
                FROM apartamentos a 
                WHERE a.activo = 1 
                ORDER BY a.nro_apartamento";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_detalle_completo()
    {
        $sqlApto = "SELECT * FROM apartamentos WHERE id_apartamento = :id AND activo = 1";
        $stmtA = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlApto);
        $stmtA->execute([':id' => $this->id_apartamento]);
        $apto = $stmtA->fetch(PDO::FETCH_ASSOC);

        if (!$apto) {
            throw new NegocioException('Apartamento no encontrado', HttpCodigo::NO_ENCONTRADO->value);
        }

        $sqlHab = "SELECT h.id_habitante, h.nombre, h.apellido, h.cedula, h.telefono, ha.tipo_vinculo, a.nro_apartamento
                   FROM habitantes h
                   JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                   JOIN apartamentos a ON a.id_apartamento = ha.apartamento_id
                   WHERE ha.apartamento_id = :id AND h.activo = 1";
        $stmtH = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sqlHab);
        $stmtH->execute([':id' => $this->id_apartamento]);
        $apto['habitantes'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        return ['estatus' => true, 'datos' => $apto];
    }

    private function _consultar_apartamentos_mensualidad() {
        $sql = "SELECT id_apartamento, nro_apartamento, porcentaje_participacion, gas 
                FROM apartamentos 
                WHERE activo = 1 
                ORDER BY nro_apartamento";
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function _contar_activos()
    {
        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->query("SELECT COUNT(*) FROM apartamentos WHERE activo = 1");
        return ['estatus' => true, 'datos' => $stmt->fetchColumn()];
    }

    private function _obtener_apartamentos_por_correo()
    {
        $sql = "SELECT a.id_apartamento, a.nro_apartamento
                FROM habitantes h
                INNER JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id
                INNER JOIN apartamentos a ON ha.apartamento_id = a.id_apartamento
                WHERE h.correo = :correo
                  AND h.activo = 1
                  AND a.activo = 1";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute([':correo' => $this->correo]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            return [
                'estatus' => true,
                'datos'   => [],
                'mensaje' => 'El habitante no está vinculado a ningún apartamento activo.'
            ];
        }

        return [
            'estatus' => true,
            'datos'   => $resultados,
            'mensaje' => 'Apartamentos obtenidos correctamente.'
        ];
    }

    private function _consultar_estado_inicio()
    {
        $vinculoPropietario = TipoVinculo::PROPIETARIO->value;
        $estadoOcupado = EstadoOcupacion::OCUPADO->value;
        $estadoLibre = EstadoOcupacion::LIBRE->value;
        
        $sql = "SELECT 
                    a.nro_apartamento,
                    CASE 
                        WHEN (SELECT COUNT(*) 
                              FROM habitantes_apartamentos ha 
                              JOIN habitantes h ON ha.habitante_id = h.id_habitante 
                              WHERE ha.apartamento_id = a.id_apartamento AND h.activo = 1) > 0 
                        THEN '$estadoOcupado'
                        ELSE '$estadoLibre'
                    END as estado,
                    COALESCE(
                        (SELECT CONCAT(h.nombre, ' ', h.apellido) 
                         FROM habitantes h 
                         JOIN habitantes_apartamentos ha ON h.id_habitante = ha.habitante_id 
                         WHERE ha.apartamento_id = a.id_apartamento 
                           AND h.activo = 1 
                         ORDER BY CASE WHEN ha.tipo_vinculo = '$vinculoPropietario' THEN 1 ELSE 2 END 
                         LIMIT 1), 
                    'Sin habitante registrado') as residente_principal,
                    COALESCE(
                        (SELECT SUM(deuda_pendiente) 
                         FROM vw_estado_cuentas_mensualidad 
                         WHERE nro_apartamento = a.nro_apartamento 
                           AND deuda_pendiente > 0), 
                    0.00) as deuda_total
                    
                FROM apartamentos a
                WHERE a.activo = 1
                ORDER BY a.nro_apartamento ASC";

        $stmt = $this->get_conex(TipoBaseDatos::NEGOCIO)->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['estatus' => true, 'datos' => $datos];
    }
}