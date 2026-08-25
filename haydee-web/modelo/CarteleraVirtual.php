<?php
namespace haydee\modelo;

use PDO;
use haydee\ayuda\GestorImagenes;
use haydee\enums\NivelPrioridad;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class CarteleraVirtual extends Conexion
{
    private const DIAS_RECIENTES_DEFECTO = '-7 days';
    private const PRIORIDAD_DESPLAZADA = 99;
    private const LIMITE_INICIO = 4;
    private const LIMITE_WIDGET = 7;
    
    private $id_cartelera;
    private $titulo;
    private $descripcion;
    private $fecha;
    private $imagen;
    private $prioridad;
    private $usuario_id;

    private $limite_paginacion;
    private $offset_paginacion;

    public static function obtenerReglas($operacion) {
        $prioridadesValidas = implode('|', array_column(NivelPrioridad::cases(), 'value'));

        $reglasCampos = [
            'id_cartelera' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'cartelera_virtual', 'campo' => 'id_cartelera']
            ],
            'titulo' => [
                'regex' => '/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()\'"!?¡¿%°\- ]{3,200}$/'
            ],
            'descripcion' => [
                'regex' => '/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9.,;()\'"!?¡¿%°\- ]{3,200}$/'
            ],
            'imagen' => [
                'regex' => '/^[a-zA-Z0-9_.\- ]+\.(jpg|jpeg|png|gif)$/i',
                'opcional' => true
            ],
            'prioridad' => [
                'regex' => "/^($prioridadesValidas)$/"
            ],
            'usuario_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'usuarios', 'campo' => 'id_usuario']
            ]
        ];

        $configPorOperacion = [
            'consulta' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'consultar_cartelera' => [
                'metodo_http' => ['GET'],
                'campos' => ['id_cartelera']
            ],
            'consultar_paginada' => [
                'metodo_http' => ['GET'],
                'campos' => []
            ],
            'registrar_cartelera' => [
                'metodo_http' => ['POST'],
                'campos' => ['titulo', 'descripcion', 'prioridad', 'usuario_id']
            ],
            'modificar_cartelera' => [
                'metodo_http' => ['PUT', 'POST'],
                'campos' => ['id_cartelera', 'titulo', 'descripcion', 'prioridad', 'usuario_id']
            ],
            'eliminar_cartelera' => [
                'metodo_http' => ['DELETE', 'POST'],
                'campos' => ['id_cartelera']
            ]
        ];

        if (isset($configPorOperacion[$operacion])) {
            $config = $configPorOperacion[$operacion];
            $reglasFiltradas = array_intersect_key($reglasCampos, array_flip($config['campos']));
            $reglasFiltradas['__metodo_http_permitido__'] = $config['metodo_http'];
            return $reglasFiltradas;
        }

        return [];
    }

    public function set_id_cartelera($id) { $this->id_cartelera = $id; }
    public function get_id_cartelera() { return $this->id_cartelera; }
    public function set_titulo($titulo) { $this->titulo = $titulo; }
    public function get_titulo() { return $this->titulo; }
    public function set_descripcion($desc) { $this->descripcion = $desc; }
    public function get_descripcion() { return $this->descripcion; }
    public function set_fecha($fecha) { $this->fecha = $fecha; }
    public function get_fecha() { return $this->fecha; }
    public function set_imagen($img) { $this->imagen = $img; }
    public function get_imagen() { return $this->imagen; }
    public function set_prioridad($pri) { $this->prioridad = $pri; }
    public function get_prioridad() { return $this->prioridad; }
    public function set_usuario_id($id) { $this->usuario_id = $id; }
    public function get_usuario_id() { return $this->usuario_id; }
    public function set_limite_paginacion($l) { $this->limite_paginacion = (int)$l; }
    public function set_offset_paginacion($o) { $this->offset_paginacion = (int)$o; }

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
        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, imagen, descripcion, usuarios.nombre as nombre_usuario 
                FROM cartelera_virtual
                INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
                ORDER BY fecha ASC";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_cartelera()
    {
        $sql = "SELECT cv.*, u.nombre AS nombre_usuario
                FROM cartelera_virtual cv
                INNER JOIN usuarios u ON cv.usuario_id = u.id_usuario
                WHERE cv.id_cartelera = :id_cartelera";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->bindParam(':id_cartelera', $this->id_cartelera);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            throw new NegocioException('Publicación no encontrada.', HttpCodigo::NO_ENCONTRADO->value);
        }

        return ['estatus' => true, 'datos' => $datos];
    }

    private function _registrar_cartelera()
    {
        $sql = "INSERT INTO cartelera_virtual (titulo, descripcion, imagen, prioridad, usuario_id)
                VALUES (:titulo, :descripcion, :imagen, :prioridad, :usuario_id)";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':imagen', $this->imagen);
        $stmt->bindParam(':prioridad', $this->prioridad);
        $stmt->bindParam(':usuario_id', $this->usuario_id);
        $stmt->execute();

        $lastId = $this->get_conex(TipoBaseDatos::SEGURIDAD)->lastInsertId();
        return ['estatus' => true, 'mensaje' => 'Publicación registrada correctamente', 'lastId' => $lastId];
    }

    private function _modificar_cartelera()
    {
        $sql = "UPDATE cartelera_virtual SET
                    titulo = :titulo,
                    descripcion = :descripcion,
                    imagen = :imagen,
                    prioridad = :prioridad,
                    usuario_id = :usuario_id
                WHERE id_cartelera = :id_cartelera";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->bindParam(':id_cartelera', $this->id_cartelera);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':imagen', $this->imagen);
        $stmt->bindParam(':prioridad', $this->prioridad);
        $stmt->bindParam(':usuario_id', $this->usuario_id);
        $stmt->execute();

        return ['estatus' => true, 'mensaje' => 'Publicación actualizada correctamente'];
    }

    private function _eliminar_cartelera()
    {
        $imagen = $this->obtenerImagenActual();

        $sql = "DELETE FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->bindParam(':id_cartelera', $this->id_cartelera);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            throw new NegocioException('No se encontró la publicación a eliminar.', HttpCodigo::NO_ENCONTRADO->value);
        }

        if ($imagen) {
            GestorImagenes::eliminar($imagen, 'cartelera');
        }

        return ['estatus' => true, 'mensaje' => 'Publicación eliminada correctamente'];
    }

    public function obtenerImagenActual()
    {
        if (!$this->id_cartelera) {
            return null;
        }

        $sql = "SELECT imagen FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->bindParam(':id_cartelera', $this->id_cartelera);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res ? $res['imagen'] : null;
    }

    public function consultar_inicio($limite, $fecha_limite = null)
    {
        $limite_int = (int)$limite;
        
        if (!$fecha_limite) {
            $fecha_limite = date('Y-m-d', strtotime(self::DIAS_RECIENTES_DEFECTO));
        }

        $prioridadBaja = self::PRIORIDAD_DESPLAZADA;
        $limiteConsulta = self::LIMITE_INICIO;

        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, imagen, descripcion, usuarios.nombre as nombre_usuario 
                FROM cartelera_virtual
                INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
                ORDER BY 
                    CASE 
                        WHEN fecha >= :fecha_limite THEN prioridad 
                        ELSE $prioridadBaja 
                    END ASC, 
                    fecha DESC 
                LIMIT $limiteConsulta OFFSET :offset";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->bindParam(':offset', $limite_int, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_limite', $fecha_limite, PDO::PARAM_STR);
        $stmt->execute();
        
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function consultar_widget_dashboard()
    {
        $limite = self::LIMITE_WIDGET;

        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, imagen, descripcion, usuarios.nombre as nombre_usuario
                FROM cartelera_virtual
                INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
                ORDER BY fecha DESC LIMIT $limite";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute();
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _consultar_paginada()
    {
        $limite = $this->limite_paginacion ?: 10;
        $offset = $this->offset_paginacion ?: 0;

        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, imagen, descripcion, usuarios.nombre as nombre_usuario 
                FROM cartelera_virtual
                INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
                ORDER BY fecha DESC 
                LIMIT :limite OFFSET :offset";

        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
}