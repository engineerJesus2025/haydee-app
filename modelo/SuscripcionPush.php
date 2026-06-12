<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\RolSistema;
use haydee\enums\TipoBaseDatos;

class SuscripcionPush extends Conexion
{
    private $id_suscripcion;
    private $usuario_id;
    private $endpoint;
    private $p256dh;
    private $auth;

    // ====================================================================
    // VALIDACIONES CENTRALIZADAS
    // ====================================================================
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'endpoint' => [
                'regex' => '/^.+$/' // Permite cualquier URL válida del endpoint
            ],
            'p256dh' => [
                'regex' => '/^[A-Za-z0-9\+\/\=_-]+$/' // Base64
            ],
            'auth' => [
                'regex' => '/^[A-Za-z0-9\+\/\=_-]+$/' // Base64
            ]
        ];

        // Definimos qué campos se validan en cada operación
        $camposPorOperacion = [
            'registrar_suscripcion'  => ['endpoint', 'p256dh', 'auth']
        ];

        // Si la operación existe en nuestro mapeo, devolvemos solo las reglas de esos campos
        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }

        return [];
    }

    // GETTERS Y SETTERS
    public function set_usuario_id($id) { $this->usuario_id = $id; }
    public function set_endpoint($e) { $this->endpoint = $e; }
    public function set_p256dh($p) { $this->p256dh = $p; }
    public function set_auth($a) { $this->auth = $a; }

    // ENRUTADOR
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

    // ====================================================================
    // MÉTODOS PRIVADOS (LÓGICA)
    // ====================================================================
    private function _registrar_suscripcion()
    {
        try {
            $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $conexion->beginTransaction();

            $sql_check = "SELECT id_suscripcion FROM suscripciones_push WHERE endpoint = :endpoint";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->bindParam(':endpoint', $this->endpoint);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                $conexion->rollBack();
                return ['estatus' => true, 'mensaje' => 'El dispositivo ya estaba registrado en Haydee'];
            }

            $sql = "INSERT INTO suscripciones_push (usuario_id, endpoint, p256dh, auth) 
                    VALUES (:usuario_id, :endpoint, :p256dh, :auth)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $this->usuario_id);
            $stmt->bindParam(':endpoint', $this->endpoint);
            $stmt->bindParam(':p256dh', $this->p256dh);
            $stmt->bindParam(':auth', $this->auth);
            $stmt->execute();
            
            $conexion->commit();
            return ['estatus' => true, 'mensaje' => 'Suscripción Push guardada correctamente'];

        } catch (PDOException $e) {
            $conexion->rollBack();
            error_log("Error en SuscripcionPush::_registrar: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al guardar la suscripción del dispositivo'];
        }
    }

    private function _obtener_todos()
    {
        try {
            $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $sql = "SELECT endpoint, p256dh, auth FROM suscripciones_push";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _obtener_todos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener las suscripciones.', 'datos' => []];
        }
    }

    // Método para obtener solo los dispositivos de los administradores
    private function _obtener_admins()
    {
        try {
            $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            
            $admin = RolSistema::ADMINISTRADOR->value;
            $superAdmin = RolSistema::SUPER_ADMIN->value;

            $sql = "SELECT sp.endpoint, sp.p256dh, sp.auth 
                    FROM suscripciones_push sp
                    INNER JOIN usuarios u ON sp.usuario_id = u.id_usuario
                    WHERE u.rol_id IN (:admin, :superadmin)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':admin', $admin, PDO::PARAM_INT);
            $stmt->bindValue(':superadmin', $superAdmin, PDO::PARAM_INT);
            $stmt->execute();
            
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['estatus' => true, 'datos' => $datos];
        } catch (PDOException $e) {
            error_log("Error en _obtener_admins: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener suscripciones de administradores.', 'datos' => []];
        }
    }
}