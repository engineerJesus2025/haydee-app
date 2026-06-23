<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\enums\RolSistema;
use haydee\enums\TipoBaseDatos;

class SuscripcionPushMovil extends Conexion
{
    private $id_suscripcion_movil;
    private $usuario_id;
    private $expo_token;
    private $plataforma;

    // GETTERS Y SETTERS
    public function set_usuario_id($id) { $this->usuario_id = $id; }
    public function set_expo_token($token) { $this->expo_token = $token; }
    public function set_plataforma($plat) { $this->plataforma = $plat; }

    // VALIDACIONES CENTRALIZADAS
    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'expo_token' => ['regex' => '/^ExponentPushToken\[.+\]$/'],
            'plataforma' => ['regex' => '/^(ANDROID|IOS)$/i'] // La 'i' permite mayúsculas y minúsculas
        ];

        $camposPorOperacion = [
            'registrar_suscripcion_movil' => ['expo_token', 'plataforma']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

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

    // MÉTODOS PRIVADOS
    private function _registrar_suscripcion() 
    {
        try {
            $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $conexion->beginTransaction();

            // Verificar si el token ya está registrado
            $sql_check = "SELECT id_suscripcion_movil FROM suscripciones_push_movil WHERE expo_token = :token";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->bindParam(':token', $this->expo_token);
            $stmt_check->execute();

            $plat = strtoupper($this->plataforma);

            if ($stmt_check->rowCount() > 0) {
                // Si existe, actualizamos a quién le pertenece y su plataforma
                $sql_upd = "UPDATE suscripciones_push_movil SET usuario_id = :uid, plataforma = :plat WHERE expo_token = :token";
                $stmt_upd = $conexion->prepare($sql_upd);
                $stmt_upd->execute([
                    ':uid' => $this->usuario_id, 
                    ':plat' => $plat, 
                    ':token' => $this->expo_token
                ]);
                $conexion->commit();
                return ['estatus' => true, 'mensaje' => 'Suscripción móvil actualizada en el dispositivo.'];
            }

            // Si no existe, insertamos el nuevo token
            $sql = "INSERT INTO suscripciones_push_movil (usuario_id, expo_token, plataforma)
                    VALUES (:usuario_id, :expo_token, :plataforma)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $this->usuario_id);
            $stmt->bindParam(':expo_token', $this->expo_token);
            $stmt->bindParam(':plataforma', $plat);
            $stmt->execute();

            $conexion->commit();
            return ['estatus' => true, 'mensaje' => 'Suscripción móvil guardada correctamente.'];

        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error en SuscripcionPushMovil::_registrar_suscripcion: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al guardar la suscripción del dispositivo.'];
        }
    }

    private function _obtener_todos() 
    {
        try {
            $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $sql = "SELECT expo_token, plataforma FROM suscripciones_push_movil";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _obtener_todos móvil: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener tokens de notificaciones.', 'datos' => []];
        }
    }

    private function _obtener_admins() 
    {
        try {
            $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            
            // Usando tus Enums establecidos
            $admin = RolSistema::ADMINISTRADOR->value;
            $superAdmin = RolSistema::SUPER_ADMIN->value;

            $sql = "SELECT spm.expo_token, spm.plataforma 
                    FROM suscripciones_push_movil spm
                    INNER JOIN usuarios u ON spm.usuario_id = u.id_usuario
                    WHERE u.rol_id IN (:admin, :superadmin)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':admin', $admin, PDO::PARAM_INT);
            $stmt->bindValue(':superadmin', $superAdmin, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en _obtener_admins móvil: " . $e->getMessage());
            return ['estatus' => false, 'mensaje' => 'Error al obtener tokens de administradores.', 'datos' => []];
        }
    }
}