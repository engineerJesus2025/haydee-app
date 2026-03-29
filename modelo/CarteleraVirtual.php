<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\ayuda\GestorImagenes;

class CarteleraVirtual extends Conexion
{
    private $id_cartelera;
    private $titulo;
    private $descripcion;
    private $fecha;
    private $imagen;
    private $prioridad;
    private $usuario_id;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
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
                // La validamos como opcional, ya que a veces no se sube imagen nueva al modificar
                'regex' => '/^[a-zA-Z0-9_.\- ]+\.(jpg|jpeg|png|gif)$/i',
                'opcional' => true
            ],
            'prioridad' => [
                'regex' => '/^(1|2|3)$/'
            ],
            'usuario_id' => [
                'regex' => '/^\d+$/',
                'exists' => ['tabla' => 'usuarios', 'campo' => 'id_usuario']
            ]
        ];

        $camposPorOperacion = [
            'registrar_cartelera' => ['titulo', 'descripcion', 'prioridad', 'usuario_id'],
            'modificar_cartelera' => ['id_cartelera', 'titulo', 'descripcion', 'prioridad', 'usuario_id'],
            'eliminar_cartelera'  => ['id_cartelera'],
            'consultar_cartelera' => ['id_cartelera']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    // Getters y Setters
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

    /**
     * Enruta la acción al método privado correspondiente.
     */
    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            return ['estatus' => false, 'mensaje' => "La acción '$accion' no está implementada."];
        }

        try {
            return $this->$metodo();
        } catch (\Exception $e) {
            error_log("Error en realizar_consulta ($accion): " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Ocurrió un error interno en el servidor.'];
        }
    }

    // -----------------------------------------------------------------
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    /**
     * Lista todas las publicaciones (vista resumida).
     // SE USA EN EL MODULO
     */
    private function _consultar()
    {
        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, imagen, descripcion, usuarios.nombre as nombre_usuario 
                FROM cartelera_virtual
                INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
                ORDER BY fecha ASC";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar cartelera'];
        }
    }

    /**
     * Consulta detallada de una publicación por ID.
     // SE USA EN EL MODULO
     */
    private function _consultar_cartelera()
    {
        $sql = "SELECT cv.*, u.nombre AS nombre_usuario
                FROM cartelera_virtual cv
                INNER JOIN usuarios u ON cv.usuario_id = u.id_usuario
                WHERE cv.id_cartelera = :id_cartelera";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':id_cartelera', $this->id_cartelera);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['estatus' => false, 'mensaje' => 'Publicación no encontrada'];
            }
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _consultar_cartelera_id: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar la publicación'];
        }
    }

    /**
     * Registra una nueva publicación.
     // SE USA EN EL MODULO
     */
    private function _registrar_cartelera()
    {
        $sql = "INSERT INTO cartelera_virtual (titulo, descripcion, imagen, prioridad, usuario_id)
                VALUES (:titulo, :descripcion, :imagen, :prioridad, :usuario_id)";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':titulo', $this->titulo);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':imagen', $this->imagen);
            $stmt->bindParam(':prioridad', $this->prioridad);
            $stmt->bindParam(':usuario_id', $this->usuario_id);
            $stmt->execute();
            $lastId = $this->get_conex('seguridad')->lastInsertId();
            return ['estatus' => true, 'mensaje' => 'Publicación registrada correctamente', 'lastId' => $lastId];
        } catch (PDOException $e) {
            error_log("Error en _registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al registrar la publicación'];
        }
    }

    /**
     * Actualiza una publicación existente.
     // SE USA EN EL MODULO
     */
    private function _modificar_cartelera()
    {
        $sql = "UPDATE cartelera_virtual SET
                    titulo = :titulo,
                    descripcion = :descripcion,
                    imagen = :imagen,
                    prioridad = :prioridad,
                    usuario_id = :usuario_id
                WHERE id_cartelera = :id_cartelera";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':id_cartelera', $this->id_cartelera);
            $stmt->bindParam(':titulo', $this->titulo);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':imagen', $this->imagen);
            $stmt->bindParam(':prioridad', $this->prioridad);
            $stmt->bindParam(':usuario_id', $this->usuario_id);
            $stmt->execute();
            return ['estatus' => true, 'mensaje' => 'Publicación actualizada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _modificar_publicacion: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al actualizar la publicación'];
        }
    }

    /**
     * Elimina una publicación (físicamente) y su imagen asociada.
     // SE USA EN EL MODULO
     */
    private function _eliminar_cartelera()
    {
        // Obtener el nombre de la imagen antes de eliminar
        $imagen = $this->obtenerImagenActual();

        $sql = "DELETE FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':id_cartelera', $this->id_cartelera);
            $stmt->execute();
            $filas = $stmt->rowCount();
            if ($filas == 0) {
                return ['estatus' => false, 'mensaje' => 'No se encontró la publicación'];
            }

            // Eliminar archivo de imagen si existe
            if ($imagen) {
                GestorImagenes::eliminar($imagen, 'cartelera');
            }

            return ['estatus' => true, 'mensaje' => 'Publicación eliminada correctamente'];
        } catch (PDOException $e) {
            error_log("Error en _eliminar_publicacion: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al eliminar la publicación'];
        }
    }

    // -----------------------------------------------------------------
    // Métodos públicos auxiliares
    // -----------------------------------------------------------------

    /**
     * Obtiene el nombre de la imagen actual de una publicación.
     // SE USA EN LA PROPIA CLASE (considerar quitar metodo)
     */
    public function obtenerImagenActual()
    {
        if (!$this->id_cartelera) {
            return null;
        }
        $sql = "SELECT imagen FROM cartelera_virtual WHERE id_cartelera = :id_cartelera";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':id_cartelera', $this->id_cartelera);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? $res['imagen'] : null;
        } catch (PDOException $e) {
            error_log("Error en obtener_imagen_actual: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Consulta para la página de inicio (con paginación).
     // SE USA EN INICIO
     */
    public function consultar_inicio($limite)
    {
        $limite_int = (int)$limite;
        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, imagen, descripcion, usuarios.nombre as nombre_usuario 
                FROM cartelera_virtual
                INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
                ORDER BY prioridad ASC, fecha DESC LIMIT 2 OFFSET :offset";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':offset', $limite_int, PDO::PARAM_INT);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en consultar_inicio: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar cartelera para inicio'];
        }
    }

    /**
     * Consulta rápida de las últimas 5 publicaciones para el widget del Dashboard
     // SE USA EN INICIO
     */
    public function consultar_widget_dashboard()
    {
        $sql = "SELECT titulo, fecha, usuarios.nombre as nombre_usuario, prioridad 
                FROM cartelera_virtual
                INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
                ORDER BY prioridad ASC, fecha DESC LIMIT 5";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (\PDOException $e) {
            error_log("Error en consultar_widget_dashboard: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al consultar publicaciones'];
        }
    }


}
?>