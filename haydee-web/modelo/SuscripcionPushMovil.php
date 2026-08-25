<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\RolSistema;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\NegocioException;

class SuscripcionPushMovil extends Conexion
{
    private $id_suscripcion_movil;
    private $usuario_id;
    private $expo_token;
    private $plataforma;

    public function set_usuario_id($id) { $this->usuario_id = $id; }
    public function set_expo_token($token) { $this->expo_token = $token; }
    public function set_plataforma($plat) { $this->plataforma = $plat; }

    public static function obtenerReglas($operacion) {
        $reglasGenerales = [
            'expo_token' => ['regex' => '/^ExponentPushToken\[.+\]$/'],
            'plataforma' => ['regex' => '/^(ANDROID|IOS)$/i']
        ];

        $camposPorOperacion = [
            'registrar_suscripcion_movil' => ['expo_token', 'plataforma']
        ];

        if (isset($camposPorOperacion[$operacion])) {
            return array_intersect_key($reglasGenerales, array_flip($camposPorOperacion[$operacion]));
        }
        return [];
    }

    public function realizar_consulta($accion)
    {
        $metodo = '_' . $accion;
        if (!method_exists($this, $metodo)) {
            throw new NegocioException("La acción '$accion' no está implementada.", HttpCodigo::BAD_REQUEST->value);
        }
        return $this->$metodo();
    }

    private function _registrar_suscripcion() 
    {
        $conexion = $this->get_conex(TipoBaseDatos::SEGURIDAD);
        try {
            $conexion->beginTransaction();

            $sql_check = "SELECT id_suscripcion_movil FROM suscripciones_push_movil WHERE expo_token = :token";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->execute([':token' => $this->expo_token]);

            $plat = strtoupper($this->plataforma);

            if ($stmt_check->rowCount() > 0) {
                $sql_upd = "UPDATE suscripciones_push_movil SET usuario_id = :uid, plataforma = :plat WHERE expo_token = :token";
                $stmt_upd = $conexion->prepare($sql_upd);
                $stmt_upd->execute([
                    ':uid'   => $this->usuario_id, 
                    ':plat'  => $plat, 
                    ':token' => $this->expo_token
                ]);
                $conexion->commit();
                return ['estatus' => true, 'mensaje' => 'Suscripción móvil actualizada en el dispositivo.'];
            }

            $sql = "INSERT INTO suscripciones_push_movil (usuario_id, expo_token, plataforma)
                    VALUES (:usuario_id, :expo_token, :plataforma)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $this->usuario_id,
                ':expo_token' => $this->expo_token,
                ':plataforma' => $plat
            ]);

            $conexion->commit();
            return ['estatus' => true, 'mensaje' => 'Suscripción móvil guardada correctamente.'];

        } catch (\Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }

    private function _obtener_todos() 
    {
        $sql = "SELECT expo_token, plataforma FROM suscripciones_push_movil";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute();
        
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function _obtener_admins() 
    {
        $admin = RolSistema::ADMINISTRADOR->value;
        $superAdmin = RolSistema::SUPER_ADMIN->value;

        $sql = "SELECT spm.expo_token, spm.plataforma 
                FROM suscripciones_push_movil spm
                INNER JOIN usuarios u ON spm.usuario_id = u.id_usuario
                WHERE u.rol_id IN (:admin, :superadmin)";
        
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->bindValue(':admin', $admin, PDO::PARAM_INT);
        $stmt->bindValue(':superadmin', $superAdmin, PDO::PARAM_INT);
        $stmt->execute();
        
        return ['estatus' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
}