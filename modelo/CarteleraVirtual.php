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
    private $tipo;
    private $imagen;
    private $prioridad;
    private $usuario_id;

    // Reglas de validación centralizadas
    private $reglas = [
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
        'fecha' => [
            'regex' => '/^\d{4}-\d{2}-\d{2}$/',
            'custom' => 'validarFecha'
        ],
        'imagen' => [
            'regex' => '/^[a-zA-Z0-9_.-]+\.(jpg|jpeg|png|gif)$/i',
            'opcional' => true
        ],
        'prioridad' => [
            'regex' => '/^\d+$/'
        ],
        'usuario_id' => [
            'regex' => '/^\d+$/',
            'exists' => ['tabla' => 'usuarios', 'campo' => 'id_usuario']
        ]
    ];

    // Getters y Setters
    public function set_id_cartelera($id) { $this->id_cartelera = $id; }
    public function get_id_cartelera() { return $this->id_cartelera; }
    public function set_titulo($titulo) { $this->titulo = $titulo; }
    public function get_titulo() { return $this->titulo; }
    public function set_descripcion($desc) { $this->descripcion = $desc; }
    public function get_descripcion() { return $this->descripcion; }
    public function set_fecha($fecha) { $this->fecha = $fecha; }
    public function get_fecha() { return $this->fecha; }
    public function set_tipo($tipo) { $this->tipo = $tipo; }
    public function get_tipo() { return $this->tipo; }
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
    // Método de validación centralizado
    // -----------------------------------------------------------------

    private function validar($campos)
    {
        foreach ($campos as $campo) {
            if (!isset($this->reglas[$campo])) {
                return [
                    'estatus' => false,
                    'mensaje' => "No hay reglas de validación definidas para el campo '$campo'."
                ];
            }
            $regla = $this->reglas[$campo];

            $getter = 'get_' . $campo;
            if (!method_exists($this, $getter)) {
                return [
                    'estatus' => false,
                    'mensaje' => "El campo '$campo' no tiene un getter definido."
                ];
            }
            $valor = $this->$getter();

            // Requerido (a menos que sea opcional)
            $requerido = !(isset($regla['opcional']) && $regla['opcional'] === true);
            if ($requerido) {
                if ($valor === null) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' es requerido y no se ha establecido."
                    ];
                }
                if (is_string($valor) && trim($valor) === '') {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no puede estar vacío."
                    ];
                }
            } else {
                // Si es opcional y está vacío, saltamos validaciones adicionales
                if ($valor === null || (is_string($valor) && trim($valor) === '')) {
                    continue;
                }
            }

            // Validar con expresión regular
            if (isset($regla['regex'])) {
                if (!preg_match($regla['regex'], (string)$valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no tiene un formato válido."
                    ];
                }
            }

            // Validación personalizada (método dentro de la clase)
            if (isset($regla['custom']) && method_exists($this, $regla['custom'])) {
                if (!$this->{$regla['custom']}($valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El campo '$campo' no es válido."
                    ];
                }
            }

            // Validar existencia en otra tabla (foránea)
            if (isset($regla['exists'])) {
                $tabla = $regla['exists']['tabla'];
                $campoFor = $regla['exists']['campo'] ?? $campo;
                if (!$this->existeEnTabla($tabla, $campoFor, $valor)) {
                    return [
                        'estatus' => false,
                        'mensaje' => "El valor del campo '$campo' no existe en la tabla $tabla."
                    ];
                }
            }
        }
        return ['estatus' => true];
    }

    /**
     * Verifica si un valor existe en una tabla específica (usa BD seguridad).
     */
    private function existeEnTabla($tabla, $campo, $valor)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = :valor";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':valor', $valor);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEnTabla: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validación personalizada para fecha.
     */
    private function validarFecha($fecha)
    {
        $valores = explode('-', $fecha);
        return count($valores) == 3 && checkdate((int)$valores[1], (int)$valores[2], (int)$valores[0]);
    }

    // -----------------------------------------------------------------
    // Métodos privados (acciones)
    // -----------------------------------------------------------------

    /**
     * Lista todas las publicaciones (vista resumida).
     */
    private function _consultar()
    {
        $sql = "SELECT id_cartelera, titulo, prioridad, fecha, usuarios.nombre as nombre_usuario 
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
     */
    private function _consultar_cartelera_id()
    {
        $validacion = $this->validar(['id_cartelera']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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
     */
    private function _registrar()
    {
        $campos = ['titulo', 'descripcion', 'fecha', 'prioridad', 'usuario_id'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "INSERT INTO cartelera_virtual (titulo, descripcion, fecha, imagen, prioridad, usuario_id)
                VALUES (:titulo, :descripcion, :fecha, :imagen, :prioridad, :usuario_id)";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':titulo', $this->titulo);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':fecha', $this->fecha);
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
     */
    private function _modificar_publicacion()
    {
        $campos = ['id_cartelera', 'titulo', 'descripcion', 'fecha', 'prioridad', 'usuario_id'];
        $validacion = $this->validar($campos);
        if (!$validacion['estatus']) {
            return $validacion;
        }

        $sql = "UPDATE cartelera_virtual SET
                    titulo = :titulo,
                    descripcion = :descripcion,
                    fecha = :fecha,
                    imagen = :imagen,
                    prioridad = :prioridad,
                    usuario_id = :usuario_id
                WHERE id_cartelera = :id_cartelera";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->bindParam(':id_cartelera', $this->id_cartelera);
            $stmt->bindParam(':titulo', $this->titulo);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':fecha', $this->fecha);
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
     */
    private function _eliminar_publicacion()
    {
        $validacion = $this->validar(['id_cartelera']);
        if (!$validacion['estatus']) {
            return $validacion;
        }

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

    /**
     * Último ID insertado.
     */
    private function _lastId()
    {
        $sql = "SELECT MAX(id_cartelera) as last_id FROM cartelera_virtual";
        try {
            $stmt = $this->get_conex('seguridad')->prepare($sql);
            $stmt->execute();
            $dato = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $dato];
        } catch (PDOException $e) {
            error_log("Error en _lastId: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener último ID'];
        }
    }

    // -----------------------------------------------------------------
    // Métodos públicos auxiliares
    // -----------------------------------------------------------------

    /**
     * Obtiene el nombre de la imagen actual de una publicación.
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
     * Consulta rápida de las últimas 3 publicaciones para el widget del Dashboard
     */
    public function consultar_widget_dashboard()
    {
        $sql = "SELECT titulo, fecha, usuarios.nombre as nombre_usuario 
                FROM cartelera_virtual
                INNER JOIN usuarios ON usuarios.id_usuario = cartelera_virtual.usuario_id
                ORDER BY prioridad ASC, fecha DESC LIMIT 3";
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