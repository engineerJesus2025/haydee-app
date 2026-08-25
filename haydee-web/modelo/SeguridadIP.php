<?php
namespace haydee\modelo;

use PDO;
use haydee\enums\TipoListaIP;
use haydee\enums\TipoBaseDatos;
use haydee\enums\HttpCodigo;
use haydee\excepciones\SeguridadException;

class SeguridadIP extends Conexion
{
    private $ip;

    private const LIMITE_PETICIONES_IP_MINUTO = 120;
    private const LIMITE_CRITICO_INFRACCIONES = 5;
    
    private const DIAS_OLVIDO_ANTECEDENTES    = 7;
    private const STRIKES_MAXIMOS             = 3;
    private const HORAS_CASTIGO_STRIKE_1      = 1;
    private const HORAS_CASTIGO_STRIKE_2      = 24;

    public function set_ip(string $ip): void { $this->ip =$ip; }
    public function get_ip(): string { return $this->ip; }

    /**
     * Verificación de Entrada con Atenuación de Penas
     */
    public function verificarListaAcceso()
    {
        $db =$this->get_conex(TipoBaseDatos::SEGURIDAD);
        
        $stmt =$db->prepare("SELECT tipo_lista, fecha_expiracion, fecha_agregado FROM listas_acceso_ip WHERE ip = :ip");
        $stmt->execute([':ip' =>$this->get_ip()]);
        $lista =$stmt->fetch(PDO::FETCH_ASSOC);

        if ($lista &&$lista['tipo_lista'] === TipoListaIP::NEGRA->value) {
            
            if ($lista['fecha_expiracion'] !== null && strtotime($lista['fecha_expiracion']) < time()) {
                
                $segundosTranscurridos = time() - strtotime($lista['fecha_agregado']);
                $diasDesdeInfraccion   = (int)($segundosTranscurridos / 86400); 
                
                if ($diasDesdeInfraccion >= self::DIAS_OLVIDO_ANTECEDENTES) {
                    $db->prepare("DELETE FROM listas_acceso_ip WHERE ip = :ip")->execute([':ip' => $this->get_ip()]);
                    $db->prepare("DELETE FROM registro_ips WHERE ip = :ip")->execute([':ip' => $this->get_ip()]);
                    
                    error_log("[FIREWALL] [Amnistia] IP " . $this->get_ip() . " cumplio " . self::DIAS_OLVIDO_ANTECEDENTES . " dias sin incidentes. Antecedentes limpios.");
                    return ['estatus' => true];
                }

                return ['estatus' => true];
            }

            $tiempoMensaje = $lista['fecha_expiracion'] ? "temporalmente hasta " . $lista['fecha_expiracion'] : "permanentemente";
            $_SESSION['rate_limit_expiracion'] = $lista['fecha_expiracion'] ? strtotime($lista['fecha_expiracion']) : 'permanente';

            throw new SeguridadException("Acceso denegado. Su red está suspendida {$tiempoMensaje} por políticas de seguridad.", HttpCodigo::PROHIBIDO->value);
        }
        return ['estatus' => true];
    }

    /**
     * Escalación Adaptativa por Inundación Volumétrica
     */
    public function verificarRateLimitGlobal()
    {
        $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);$db->exec("SET time_zone = '-04:00'");
        
        $sqlFallo = "INSERT INTO registro_ips (ip, intentos, ultimo_intento) 
                     VALUES (:ip, 1, NOW()) 
                     ON DUPLICATE KEY UPDATE 
                     intentos = IF(TIMESTAMPDIFF(MINUTE, ultimo_intento, NOW()) < 1, intentos + 1, 1),
                     ultimo_intento = IF(TIMESTAMPDIFF(MINUTE, ultimo_intento, NOW()) < 1, ultimo_intento, NOW())";
        $db->prepare($sqlFallo)->execute([':ip' =>$this->get_ip()]);

        $stmt =$db->prepare("SELECT intentos FROM registro_ips WHERE ip = :ip");
        $stmt->execute([':ip' =>$this->get_ip()]);
        $registro =$stmt->fetch(PDO::FETCH_ASSOC);

        if ($registro &&$registro['intentos'] > self::LIMITE_PETICIONES_IP_MINUTO) {
            
            $stmtCheck =$db->prepare("SELECT reincidencias FROM listas_acceso_ip WHERE ip = :ip");
            $stmtCheck->execute([':ip' =>$this->get_ip()]);
            $antecedente =$stmtCheck->fetch(PDO::FETCH_ASSOC);

            $ahoraTS = time();
            $ahora = date('Y-m-d H:i:s',$ahoraTS);

            if ($antecedente) {
                $strikes = (int)$antecedente['reincidencias'] + 1;

                if ($strikes >= self::STRIKES_MAXIMOS) {
                    $_SESSION['rate_limit_expiracion'] = 'permanente';$sqlEscalar = "UPDATE listas_acceso_ip 
                                   SET tipo_lista = :tipo, reincidencias = :strikes, motivo = :motivo, 
                                       fecha_expiracion = NULL, fecha_agregado = :ahora 
                                   WHERE ip = :ip";
                    $db->prepare($sqlEscalar)->execute([
                        ':tipo' => TipoListaIP::NEGRA->value, ':strikes' => $strikes, 
                        ':motivo' => 'Ataque volumétrico crónico (Baneo Permanente)', ':ahora' => $ahora, ':ip' =>$this->get_ip()
                    ]);
                    error_log("[WAF] [Strike 3] IP " . $this->get_ip() . " BANEADA PERMANENTEMENTE por reincidencia.");
                } else {
                    $expiracionTS =$ahoraTS + (self::HORAS_CASTIGO_STRIKE_2 * 3600);
                    $_SESSION['rate_limit_expiracion'] =$expiracionTS;
                    $expiracion = date('Y-m-d H:i:s', $expiracionTS);$sqlEscalar = "UPDATE listas_acceso_ip 
                                   SET tipo_lista = :tipo, reincidencias = :strikes, motivo = :motivo, 
                                       fecha_expiracion = :expiracion, fecha_agregado = :ahora 
                                   WHERE ip = :ip";
                    $db->prepare($sqlEscalar)->execute([
                        ':tipo' => TipoListaIP::NEGRA->value, ':strikes' => $strikes, 
                        ':motivo' => 'Abuso de tráfico reincidente (Segundo strike)', ':expiracion' => $expiracion, ':ahora' => $ahora, ':ip' =>$this->get_ip()
                    ]);
                    error_log("[WAF] [Strike {$strikes}] IP " . $this->get_ip() . " suspendida por " . self::HORAS_CASTIGO_STRIKE_2 . " horas.");
                }
            } else {
                $expiracionTS =$ahoraTS + (self::HORAS_CASTIGO_STRIKE_1 * 3600);
                $_SESSION['rate_limit_expiracion'] =$expiracionTS;
                $expiracion = date('Y-m-d H:i:s', $expiracionTS);$sqlPrimerStrike = "INSERT INTO listas_acceso_ip (ip, tipo_lista, motivo, fecha_expiracion, reincidencias, fecha_agregado) 
                                    VALUES (:ip, :tipo, :motivo, :expiracion, 1, :ahora)";
                $db->prepare($sqlPrimerStrike)->execute([
                    ':ip' => $this->get_ip(), ':tipo' => TipoListaIP::NEGRA->value, 
                    ':motivo' => 'Abuso volumétrico de tráfico (Primer strike)', ':expiracion' => $expiracion, ':ahora' =>$ahora
                ]);
                error_log("[WAF] [Strike 1] IP " . $this->get_ip() . " suspendida por " . self::HORAS_CASTIGO_STRIKE_1 . " hora.");
            }

            throw new SeguridadException('Actividad volumétrica anómala. Su dirección IP ha sido restringida.', HttpCodigo::PROHIBIDO->value);
        }
        
        return ['estatus' => true];
    }

    /**
     * Capa 3: Castigo Inmediato por Infracciones Críticas (Fuerza Bruta / XSS)
     */
    public function registrarFalloCritico()
    {
        $db =$this->get_conex(TipoBaseDatos::SEGURIDAD);
        try {
            $db->beginTransaction();

            $sql = "UPDATE registro_ips SET infracciones = infracciones + 1 WHERE ip = :ip";
            $db->prepare($sql)->execute([':ip' =>$this->get_ip()]);

            $stmt =$db->prepare("SELECT infracciones FROM registro_ips WHERE ip = :ip");
            $stmt->execute([':ip' =>$this->get_ip()]);
            $infracciones = (int)$stmt->fetchColumn();

            error_log("[WAF] Sumando infracción criptográfica a IP " . $this->get_ip() . " [{$infracciones}/" . self::LIMITE_CRITICO_INFRACCIONES . "]");

            if ($infracciones >= self::LIMITE_CRITICO_INFRACCIONES) {
                $ahora = date('Y-m-d H:i:s');$sqlNegra = "INSERT INTO listas_acceso_ip (ip, tipo_lista, motivo, fecha_expiracion, reincidencias, fecha_agregado) 
                             VALUES (:ip, :tipo, :motivo, NULL, :strikes, :ahora)
                             ON DUPLICATE KEY UPDATE 
                             tipo_lista = :tipo, motivo = :motivo, fecha_expiracion = NULL, reincidencias = :strikes, fecha_agregado = :ahora";
                
                $db->prepare($sqlNegra)->execute([
                    ':ip' => $this->get_ip(),
                    ':tipo' => TipoListaIP::NEGRA->value,
                    ':motivo' => 'Infracción crítica / Fuerza Bruta (Baneo Permanente)',
                    ':strikes' => self::STRIKES_MAXIMOS,
                    ':ahora' => $ahora
                ]);
                error_log("[WAF] [CRÍTICO] IP " . $this->get_ip() . " BANEADA PERMANENTEMENTE por Fuerza Bruta.");
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction())$db->rollBack();
            throw $e;
        }
    }

    /**
     * Rate-Limit interno por Usuario (Anti-Flood en Sesión)
     */
    public function verificarRateLimitUsuario(int $usuarioId, int$limitePeticiones)
    {
        $db = $this->get_conex(TipoBaseDatos::SEGURIDAD);$db->exec("SET time_zone = '-04:00'");
        
        $sql = "INSERT INTO trafico_usuarios (usuario_id, peticiones, ultimo_intento) 
                VALUES (:id, 1, NOW()) 
                ON DUPLICATE KEY UPDATE 
                peticiones = IF(TIMESTAMPDIFF(MINUTE, ultimo_intento, NOW()) < 1, peticiones + 1, 1),
                ultimo_intento = IF(TIMESTAMPDIFF(MINUTE, ultimo_intento, NOW()) < 1, ultimo_intento, NOW())";
        
        $db->prepare($sql)->execute([':id' =>$usuarioId]);

        $stmt =$db->prepare("SELECT peticiones, UNIX_TIMESTAMP(ultimo_intento) as ts_ultimo FROM trafico_usuarios WHERE usuario_id = :id");
        $stmt->execute([':id' =>$usuarioId]);
        $registro =$stmt->fetch(PDO::FETCH_ASSOC);

        $peticionesActuales = (int)($registro['peticiones'] ?? 0);

        if ($peticionesActuales >$limitePeticiones) {
            $expiracionTS = (int)$registro['ts_ultimo'] + 60;
            $_SESSION['rate_limit_expiracion'] =$expiracionTS;

            error_log("[Anti-Flood Engine] Bloqueo temporal activo para Usuario ID: {$usuarioId}. Solicitudes concurrentes: {$peticionesActuales}/{$limitePeticiones}");
            throw new SeguridadException('Se ha detectado actividad inusual en su cuenta. Por favor, espere un momento.', HttpCodigo::DEMASIADAS_PETICIONES->value);
        }

        return ['estatus' => true];
    }

    /**
     * Limpia el historial de fallos tras un comportamiento exitoso.
     */
    public function limpiarFallo()
    {
        $sql = "DELETE FROM registro_ips WHERE ip = :ip";
        $stmt = $this->get_conex(TipoBaseDatos::SEGURIDAD)->prepare($sql);
        $stmt->execute([':ip' =>$this->get_ip()]);
    }
}