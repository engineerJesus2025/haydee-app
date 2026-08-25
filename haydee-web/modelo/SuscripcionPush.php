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

    public static function obtenerReglas(string $operacion): array 
    {
        $reglasGenerales = [
            'endpoint' => ['regex' => '/^.+$/'],
            'p256dh'   => ['regex' => '/^[A-Za-z0-9\+\/\=_-]+$/'],
            'auth'     => ['regex' => '/^[A-Za-z0-9\+\/\=_-]+$/']
        ];

        $camposPorOperacion = [
            'registrar_suscripcion' => ['endpoint', 'p256dh', 'auth']
        ];

        return isset($camposPorOperacion[$operacion]) 
            ? array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion])) 
            : [];
    }

    // GETTERS Y SETTERS
    public function set_usuario_id($id) { $this->usuario_id = $id; }
    public function set_endpoint($e) { $this->endpoint = $e; }
    public function set_p256dh($p) { $this->p256dh = $p; }
    public function set_auth($a) { $this->auth = $a; }

    public function realizar_consulta(string $accion): array
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

    private function _registrar_suscripcion(): array
    {
        try {
            $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $conexion->beginTransaction();

            $stmtCheck = $conexion->prepare("SELECT id_suscripcion FROM suscripciones_push WHERE endpoint = :endpoint");
            $stmtCheck->execute([':endpoint' => $this->endpoint]);

            if ($stmtCheck->fetchColumn()) {
                $conexion->rollBack();
                return ['estatus' => true, 'mensaje' => 'El dispositivo ya estaba registrado en Haydee'];
            }

            $sql = "INSERT INTO suscripciones_push (usuario_id, endpoint, p256dh, auth) 
                    VALUES (:usuario_id, :endpoint, :p256dh, :auth)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $this->usuario_id,
                ':endpoint'   => $this->endpoint,
                ':p256dh'     => $this->p256dh,
                ':auth'       => $this->auth
            ]);
            
            $conexion->commit();
            return ['estatus' => true, 'mensaje' => 'Suscripción Push guardada correctamente'];

        } catch (PDOException $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error en SuscripcionPush::_registrar_suscripcion: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al guardar la suscripción del dispositivo'];
        }
    }

    private function _obtener_todos(): array
    {
        try {
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare("SELECT endpoint, p256dh, auth FROM suscripciones_push");
            $stmt->execute();
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _obtener_todos: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener las suscripciones.', 'datos' => []];
        }
    }

    private function _obtener_admins(): array
    {
        try {
            $sql = "SELECT sp.endpoint, sp.p256dh, sp.auth 
                    FROM suscripciones_push sp
                    INNER JOIN usuarios u ON sp.usuario_id = u.id_usuario
                    WHERE u.rol_id IN (:admin, :superadmin)";
            
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([
                ':admin'      => RolSistema::ADMINISTRADOR->value,
                ':superadmin' => RolSistema::SUPER_ADMIN->value
            ]);
            
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _obtener_admins: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener suscripciones de administradores.', 'datos' => []];
        }
    }
}