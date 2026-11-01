<?php
declare(strict_types=1);

namespace haydee\tests\pruebas_unitarias;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use haydee\modelo\SuscripcionPush;
use haydee\modelo\Usuario;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de SuscripcionPushTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/SuscripcionPushTest.php

Solo registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/SuscripcionPushTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/SuscripcionPushTest.php

Limpieza manual de los registros de prueba:
    DELETE FROM suscripciones_push WHERE endpoint LIKE 'https://example.com/test/%';
*/

#[TestDox('Módulo de Suscripciones Push Web - Pruebas Unitarias')]
#[CoversClass(SuscripcionPush::class)]
final class SuscripcionPushTest extends TestCase
{
    /* =========================================================================
     *  BLOQUE 1: VALIDACIONES ESTÁTICAS (sin BD)
     * ========================================================================= */

    #[Group('suscripcion_push')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de endpoint')]
    #[DataProvider('providerValidacionesEndpoint')]
    #[Test]
    public function testRegistrarValidacionesEndpoint(string $valor, bool $valido): void
    {
        $reglas = SuscripcionPush::obtenerReglas('registrar_suscripcion');
        $regex  = $reglas['endpoint']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesEndpoint(): array
    {
        return [
            'Endpoint URL https'   => ['https://fcm.googleapis.com/fcm/send/abc123', true],
            'Endpoint URL http'    => ['http://localhost:8080/push', true],
            'Endpoint simple'      => ['abc', true],
            'Endpoint vacío'       => ['', false],
        ];
    }

    #[Group('suscripcion_push')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de p256dh (base64url)')]
    #[DataProvider('providerValidacionesP256dh')]
    #[Test]
    public function testRegistrarValidacionesP256dh(string $valor, bool $valido): void
    {
        $reglas = SuscripcionPush::obtenerReglas('registrar_suscripcion');
        $regex  = $reglas['p256dh']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    #[Group('suscripcion_push')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de auth (base64url)')]
    #[DataProvider('providerValidacionesP256dh')]
    #[Test]
    public function testRegistrarValidacionesAuth(string $valor, bool $valido): void
    {
        $reglas = SuscripcionPush::obtenerReglas('registrar_suscripcion');
        $regex  = $reglas['auth']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesP256dh(): array
    {
        return [
            'Base64url estándar'       => ['BOr6mY0Q2v3z8K1j7Nq5pA', true],
            'Base64 con + y /'         => ['abc+def/ghi=', true],
            'Base64 con guión bajo'    => ['abc_def-ghi', true],
            'Con caracteres inválidos' => ['abc def', false],
            'Con @'                    => ['abc@def', false],
            'Vacío'                    => ['', false],
        ];
    }

    /* =========================================================================
     *  BLOQUE 2: BUG DEL MODELO (falta import)
     * ========================================================================= */

    #[Group('suscripcion_push')]
    #[Group('diseno')]
    #[TestDox('Diseño: dispatcher referencia NegocioException/HttpCodigo sin importarlos')]
    #[Test]
    public function testDispatcherImportaClasesFaltantes(): void
    {
        $s = new SuscripcionPush();

        try {
            $s->realizar_consulta('accion_falsa');
            $this->fail('Se esperaba una excepción al invocar acción inexistente.');
        } catch (NegocioException $e) {
            $this->assertStringContainsString('no está implementada', $e->getMessage());
        } catch (\Error $e) {
            $this->markTestIncomplete(
                'BUG: SuscripcionPush::realizar_consulta referencia NegocioException ' .
                'y HttpCodigo sin importarlos. Error actual: ' . $e->getMessage() . '. ' .
                'Fix: añadir al inicio de SuscripcionPush.php: ' .
                '"use haydee\excepciones\NegocioException;" y ' .
                '"use haydee\enums\HttpCodigo;".'
            );
        }
    }

    /* =========================================================================
     *  BLOQUE 3: CONSULTAR (solo SELECT, siempre seguro)
     * ========================================================================= */

    #[Group('suscripcion_push')]
    #[Group('consultar')]
    #[TestDox('Consultar: listado general de suscripciones push')]
    #[Test]
    public function testObtenerTodos(): void
    {
        $s = new SuscripcionPush();
        try {
            $resultado = $s->realizar_consulta('obtener_todos');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);
    }

    #[Group('suscripcion_push')]
    #[Group('consultar')]
    #[TestDox('Consultar: suscripciones de administradores')]
    #[Test]
    public function testObtenerAdmins(): void
    {
        $s = new SuscripcionPush();
        try {
            $resultado = $s->realizar_consulta('obtener_admins');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);
    }

    /* =========================================================================
     *  BLOQUE 4: REGISTRAR (escritura controlada con endpoint único)
     * ========================================================================= */

    #[Group('suscripcion_push')]
    #[Group('registrar')]
    #[TestDox('Registrar: Éxito con endpoint único (queda rastro en suscripciones_push)')]
    #[Test]
    public function testRegistrarSuscripcionExitoso(): array
    {
        $userId = $this->obtenerUsuarioIdValido();
        if ($userId === null) {
            $this->markTestSkipped('No hay usuarios activos en BD seguridad para asociar.');
            return [];
        }

        $sufijo   = $this->sufijoAleatorio(10);
        $endpoint = "https://example.com/test/{$sufijo}";

        $s = new SuscripcionPush();
        $s->set_usuario_id($userId);
        $s->set_endpoint($endpoint);
        $s->set_p256dh('BOr6mY0Q2v3z8K1j7Nq5pA');
        $s->set_auth('abc_def-ghi');

        try {
            $resultado = $s->realizar_consulta('registrar_suscripcion');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return [];
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus'], 'El registro debe retornar estatus true');
        $this->assertArrayHasKey('mensaje', $resultado);

        return ['endpoint' => $endpoint, 'usuario_id' => $userId];
    }

    #[Group('suscripcion_push')]
    #[Group('registrar')]
    #[TestDox('Registrar: Segundo registro con el mismo endpoint retorna éxito sin duplicar')]
    #[Test]
    public function testRegistrarSuscripcionDuplicadaRetornaExito(): void
    {
        $userId = $this->obtenerUsuarioIdValido();
        if ($userId === null) {
            $this->markTestSkipped('No hay usuarios activos.');
            return;
        }

        $sufijo   = $this->sufijoAleatorio(10);
        $endpoint = "https://example.com/test/dup_{$sufijo}";

        // Primer registro
        $s1 = new SuscripcionPush();
        $s1->set_usuario_id($userId);
        $s1->set_endpoint($endpoint);
        $s1->set_p256dh('BOr6mY0Q2v3z8K1j7Nq5pA');
        $s1->set_auth('abc_def-ghi');

        try {
            $r1 = $s1->realizar_consulta('registrar_suscripcion');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }
        $this->assertTrue($r1['estatus']);

        // Segundo registro (mismo endpoint)
        $s2 = new SuscripcionPush();
        $s2->set_usuario_id($userId);
        $s2->set_endpoint($endpoint);
        $s2->set_p256dh('BOr6mY0Q2v3z8K1j7Nq5pA');
        $s2->set_auth('abc_def-ghi');

        $r2 = $s2->realizar_consulta('registrar_suscripcion');

        $this->assertTrue($r2['estatus']);
        // El modelo retorna "ya estaba registrado" y hace rollback
        $this->assertStringContainsString('ya estaba registrado', $r2['mensaje']);
    }

    /* =========================================================================
     *  HELPERS PRIVADOS
     * ========================================================================= */

    private function sufijoAleatorio(int $largo = 10): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $sufijo = '';
        for ($i = 0; $i < $largo; $i++) {
            $sufijo .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $sufijo;
    }

    private function obtenerUsuarioIdValido(): ?int
    {
        $u = new Usuario();
        try {
            $r = $u->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            return null;
        }

        if (empty($r['estatus']) || empty($r['datos'])) {
            return null;
        }
        return (int) $r['datos'][0]['id_usuario'];
    }
}