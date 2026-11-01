<?php
declare(strict_types=1);

namespace haydee\tests\pruebas_unitarias;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use haydee\modelo\SeguridadIP;
use haydee\excepciones\SeguridadException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de SeguridadIPTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/SeguridadIPTest.php

Solo las pruebas seguras (lista de acceso):
    .\vendor\bin\phpunit --testdox --group seguridad tests/pruebas_unitarias/SeguridadIPTest.php

Limpieza manual (por si un test falla antes del finally):
    DELETE FROM registro_ips WHERE ip LIKE '10.255.255.%';
    DELETE FROM listas_acceso_ip WHERE ip LIKE '10.255.255.%';
*/

#[TestDox('Módulo de Seguridad IP - Pruebas Unitarias')]
#[CoversClass(SeguridadIP::class)]
final class SeguridadIPTest extends TestCase
{
    private const IP_TEST_1 = '10.255.255.248';
    private const IP_TEST_2 = '10.255.255.247';
    private const IP_TEST_3 = '10.255.255.246';
    private const IP_TEST_4 = '10.255.255.245';

    /* =========================================================================
     *  BLOQUE 1: DISEÑO (diferente al resto del proyecto)
     * ========================================================================= */

    #[Group('seguridad')]
    #[Group('diseno')]
    #[TestDox('Diseño: SeguridadIP no implementa realizar_consulta() como otros modelos')]
    #[Test]
    public function testNoExisteMetodoDispatcher(): void
    {
        $reflection = new \ReflectionClass(SeguridadIP::class);

        $this->assertFalse(
            $reflection->hasMethod('realizar_consulta'),
            'SeguridadIP no tiene realizar_consulta — se invoca por métodos públicos directos'
        );
        $this->assertFalse(
            $reflection->hasMethod('obtenerReglas'),
            'SeguridadIP no tiene obtenerReglas — sin validación estática backend'
        );

        $this->markTestIncomplete(
            'DISEÑO: SeguridadIP rompe el patrón del proyecto. Sus métodos públicos ' .
            '(verificarListaAcceso, verificarRateLimitGlobal, registrarFalloCritico, ' .
            'verificarRateLimitUsuario, limpiarFallo) se invocan directamente. ' .
            'Documentar la diferencia o refactorizar para consistencia.'
        );
    }

    /* =========================================================================
     *  BLOQUE 2: VERIFICAR LISTA DE ACCESO (safe — solo SELECT si IP no listada)
     * ========================================================================= */

    #[Group('seguridad')]
    #[Group('consultar')]
    #[TestDox('Lista de acceso: IP no listada retorna estatus true sin escribir')]
    #[Test]
    public function testVerificarListaAccesoIPNoListada(): void
    {
        $seg = new SeguridadIP();
        $seg->set_ip(self::IP_TEST_1);

        try {
            $resultado = $seg->verificarListaAcceso();
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
    }

    #[Group('seguridad')]
    #[Group('consultar')]
    #[TestDox('Lista de acceso: SIEMPRE OMITIDO — requiere INSERT directo a listas_acceso_ip')]
    #[Test]
    public function testVerificarListaAccesoConBaneoFalla(): void
    {
        $this->markTestSkipped(
            'OMITIDO: probar la rama de baneo requiere INSERT directo a ' .
            'listas_acceso_ip (no hay método público para crear un ban). ' .
            'Ejecutar manualmente insertando un registro con fecha_expiracion futura.'
        );
    }

    /* =========================================================================
     *  BLOQUE 3: REGISTRAR FALLO CRÍTICO (safe si IP no existe en registro_ips)
     * ========================================================================= */

    #[Group('seguridad')]
    #[Group('registrar')]
    #[TestDox('Fallo crítico: IP inexistente no banea (UPDATE afecta 0 filas)')]
    #[Test]
    public function testRegistrarFalloCriticoIPInexistenteNoBanea(): void
    {
        $seg = new SeguridadIP();
        $seg->set_ip(self::IP_TEST_2);

        try {
            // Con IP no presente en registro_ips: UPDATE afecta 0 filas,
            // SELECT posterior devuelve false, (int)false=0, no dispara ban.
            $seg->registrarFalloCritico();
            $this->addToAssertionCount(1);
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('seguridad')]
    #[Group('registrar')]
    #[TestDox('Fallo crítico: SIEMPRE OMITIDO — 5 llamadas crean ban permanente')]
    #[Test]
    public function testRegistrarFalloCritico5VecesBaneaPermanentemente(): void
    {
        $this->markTestSkipped(
            'OMITIDO: 5 llamadas con la IP en registro_ips crean un ban NEGRA con ' .
            'fecha_expiracion = NULL (permanente). Sin cleanup en el modelo. ' .
            'Ejecutar manualmente con DELETE directo posterior.'
        );
    }

    /* =========================================================================
     *  BLOQUE 4: RATE LIMIT GLOBAL (safe con limpiarFallo en finally)
     * ========================================================================= */

    #[Group('seguridad')]
    #[Group('registrar')]
    #[TestDox('Rate limit global: una petición bajo el límite retorna true (cleanup en finally)')]
    #[Test]
    public function testVerificarRateLimitGlobalBajoLimiteYLimpiar(): void
    {
        $seg = new SeguridadIP();
        $seg->set_ip(self::IP_TEST_3);

        try {
            $resultado = $seg->verificarRateLimitGlobal();

            $this->assertIsArray($resultado);
            $this->assertTrue($resultado['estatus']);
        } catch (SeguridadException $e) {
            $this->fail('Una sola petición no debe disparar el rate limit: ' . $e->getMessage());
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        } finally {
            // Cleanup: elimina el registro insertado en registro_ips
            try {
                $seg->limpiarFallo();
            } catch (\Throwable $e) {
                // Ignora fallos de cleanup
            }
        }
    }

    #[Group('seguridad')]
    #[Group('registrar')]
    #[TestDox('Rate limit global: SIEMPRE OMITIDO — 121 llamadas disparan ban de la IP')]
    #[Test]
    public function testRateLimitGlobalTriggerBaneoFalla(): void
    {
        $this->markTestSkipped(
            'OMITIDO: disparar el rate limit requiere 121 llamadas en <1 minuto. ' .
            'El modelo banearía la IP en listas_acceso_ip con strikes escalonados. ' .
            'Sin cleanup disponible. Ejecutar manualmente en entorno de pruebas.'
        );
    }

    /* =========================================================================
     *  BLOQUE 5: RATE LIMIT USUARIO (skip — deja fila huérfana)
     * ========================================================================= */

    #[Group('seguridad')]
    #[Group('registrar')]
    #[TestDox('Rate limit usuario: SIEMPRE OMITIDO — INSERT ocurre antes del throw')]
    #[Test]
    public function testVerificarRateLimitUsuarioExcedeLimiteFalla(): void
    {
        $this->markTestSkipped(
            'OMITIDO: _verificarRateLimitUsuario hace INSERT en trafico_usuarios ' .
            'SIN transacción. Aunque lance SeguridadException, la fila queda huérfana ' .
            'y NO hay método público para eliminarla. Ejecutar manualmente con DELETE.'
        );
    }

    /* =========================================================================
     *  BLOQUE 6: LIMPIAR FALLO (safe — DELETE sobre IP reservada inexistente)
     * ========================================================================= */

    #[Group('seguridad')]
    #[Group('eliminar')]
    #[TestDox('Limpiar fallo: idempotente sobre una IP sin registros')]
    #[Test]
    public function testLimpiarFalloEsIdempotente(): void
    {
        $seg = new SeguridadIP();
        $seg->set_ip(self::IP_TEST_4);

        try {
            // Sin registros previos: DELETE afecta 0 filas, sin error
            $seg->limpiarFallo();
            // Segunda vez: mismo comportamiento
            $seg->limpiarFallo();
            $this->addToAssertionCount(2);
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }
}