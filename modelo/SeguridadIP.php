<?php
namespace haydee\modelo;

use PDO;
use PDOException;
use haydee\config\TipoListaIP;
use haydee\enums\TipoBaseDatos;

class SeguridadIP extends Conexion
{
    private $ip;
    private const LIMITE_TEMPORAL = 20; 
    private const LIMITE_CRITICO = 50;

    public function set_ip($ip) {$this->ip = $ip;}
    
    public function get_ip() {return $this->ip;}

    /**
     * Verifica ÚNICAMENTE si la IP está en lista blanca o negra.
     * Esto se ejecutará en TODOS los controladores.
     */
    public function verificarListaAcceso()
    {
        try {
            $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $stmt = $db->prepare("SELECT tipo_lista FROM listas_acceso_ip WHERE ip = :ip");
            $stmt->execute([':ip' => $this->get_ip()]); // Usando el getter
            $lista = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($lista) {
                if ($lista['tipo_lista'] === TipoListaIP::NEGRA->value) {
                    return ['estatus' => false, 'mensaje' => 'Acceso denegado desde esta red. IP Bloqueada.', 'codigo_http' => 403];
                }
                // Si es BLANCA, tiene pase libre
            }
            return ['estatus' => true];
        } catch (PDOException $e) {
            error_log("Error en verificarListaAcceso: " . $e->getMessage());
            return ['estatus' => true]; // Fallback seguro
        }
    }

    /**
     * Verifica el Rate Limit (Intentos fallidos).
     * Esto se ejecutará en el login_controlador.
     */
    public function verificarRateLimit()
    {
        try {
            $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $stmt = $db->prepare("SELECT intentos, TIMESTAMPDIFF(HOUR, ultimo_intento, NOW()) as horas FROM registro_ips WHERE ip = :ip");
            $stmt->execute([':ip' => $this->get_ip()]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($registro && $registro['intentos'] >= self::LIMITE_TEMPORAL && $registro['horas'] < 24) {
                return ['estatus' => false, 'mensaje' => 'Demasiadas peticiones. Intente más tarde.', 'codigo_http' => 429];
            }
            return ['estatus' => true];
        } catch (PDOException $e) {
            error_log("Error en verificarRateLimit: " . $e->getMessage());
            return ['estatus' => true];
        }
    }

    /**
     * Registra un fallo de login o intento malicioso.
     */
    public function registrarFallo()
    {
        try {
            $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);
            $db->beginTransaction();

            $sqlFallo = "INSERT INTO registro_ips (ip, intentos, ultimo_intento) 
                         VALUES (:ip, 1, NOW()) 
                         ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = NOW()";
            $db->prepare($sqlFallo)->execute([':ip' => $this->get_ip()]);

            $stmt = $db->prepare("SELECT intentos FROM registro_ips WHERE ip = :ip FOR UPDATE");
            $stmt->execute([':ip' => $this->get_ip()]);
            $intentos = (int)$stmt->fetchColumn();

            if ($intentos >= self::LIMITE_CRITICO) {
                $sqlNegra = "INSERT IGNORE INTO listas_acceso_ip (ip, tipo_lista, motivo) 
                             VALUES (:ip, :tipo, 'Múltiples infracciones de seguridad detectadas')";
                $db->prepare($sqlNegra)->execute([
                    ':ip' => $this->get_ip(),
                    ':tipo' => TipoListaIP::NEGRA->value
                ]);
                $db->prepare("DELETE FROM registro_ips WHERE ip = :ip")->execute([':ip' => $this->get_ip()]);
            }
            $db->commit();
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
        }
    }

        /**
     * Limpia el historial de la IP tras un login exitoso.
     */
    public function limpiarFallo()
    {
        try {
            $sql = "DELETE FROM registro_ips WHERE ip = :ip";
            $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
            $stmt->execute([':ip' => $this->get_ip()]);
        } catch (PDOException $e) {
            error_log("Error al limpiar IP: " . $e->getMessage());
        }
    }
}