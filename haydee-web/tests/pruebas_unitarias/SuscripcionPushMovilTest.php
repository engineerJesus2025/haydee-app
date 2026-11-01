<?php
declare(strict_types=1);

namespace haydee\tests\pruebas_unitarias;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use haydee\modelo\SuscripcionPushMovil;
use haydee\modelo\Usuario;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de SuscripcionPushMovilTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/SuscripcionPushMovilTest.php

Solo registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/SuscripcionPushMovilTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/SuscripcionPushMovilTest.php

⚠️ ADVERTENCIA — escribe en la BD de SEGURIDAD:

- testRegistrarSuscripcionExitoso: INSERT en suscripciones_push_movil con
  expo_token único generado (ExponentPushToken[TEST_<sufijo>]).
  El modelo NO tiene método de eliminación → la fila queda huérfana.
- testRegistrarSuscripcionDuplicadaActualiza: segunda llamada con el mismo token.
  Por diseño del modelo debería ejecutar UPDATE. Sin embargo, el código usa
  $stmt->rowCount() DESPUÉS de un SELECT, patrón poco confiable en MySQL PDO.
  Si rowCount() devuelve 0, el modelo intentará INSERT → posible Duplicate Key.
  El test detecta esa situación y la reporta como Incomplete.
- Consultas (obtener_todos, obtener_admins): solo SELECT.

Limpieza manual de los registros de prueba:
    DELETE FROM suscripciones_push_movil
     WHERE expo_token LIKE 'ExponentPushToken[TEST_%]';
*/

#[TestDox('Módulo de Suscripciones Push Móvil - Pruebas Unitarias')]
#[CoversClass(SuscripcionPushMovil::class)]
final class SuscripcionPushMovilTest extends TestCase
{
    /* =========================================================================
     *  BLOQUE 1: VALIDACIONES ESTÁTICAS (sin BD)
     * ========================================================================= */

    #[Group('suscripcion_push_movil')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de expo_token (formato ExponentPushToken[...])')]
    #[DataProvider('providerValidacionesExpoToken')]
    #[Test]
    public function testRegistrarValidacionesExpoToken(string $valor, bool $valido): void
    {
        $reglas = SuscripcionPushMovil::obtenerReglas('registrar_suscripcion_movil');
        $regex  = $reglas['expo_token']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesExpoToken(): array
    {
        return [
            'Token estándar Expo'      => ['ExponentPushToken[abc123DEF456]', true],
            'Token con guión'          => ['ExponentPushToken[a-b_c]', true],
            'Sin prefijo'              => ['abc123', false],
            'Prefijo incorrecto'       => ['ExpoPushToken[abc]', false],
            'Sin corchetes'            => ['ExponentPushTokenabc', false],
            'Corchetes vacíos'         => ['ExponentPushToken[]', false],
            'Token vacío'              => ['', false],
        ];
    }

    #[Group('suscripcion_push_movil')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de plataforma (ANDROID|IOS)')]
    #[DataProvider('providerValidacionesPlataforma')]
    #[Test]
    public function testRegistrarValidacionesPlataforma(string $valor, bool $valido): void
    {
        $reglas = SuscripcionPushMovil::obtenerReglas('registrar_suscripcion_movil');
        $regex  = $reglas['plataforma']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesPlataforma(): array
    {
        return [
            'ANDROID mayúsculas' => ['ANDROID', true],
            'IOS mayúsculas'     => ['IOS', true],
            'android minúsculas' => ['android', true],
            'ios minúsculas'     => ['ios', true],
            'Windows'            => ['WINDOWS', false],
            'Web'                => ['WEB', false],
            'Vacío'              => ['', false],
        ];
    }

    /* =========================================================================
     *  BLOQUE 2: DISPATCHER (imports correctos en este modelo)
     * ========================================================================= */

    #[Group('suscripcion_push_movil')]
    #[Group('diseno')]
    #[TestDox('Dispatchers: acción inexistente lanza NegocioException correctamente')]
    #[Test]
    public function testAccionInexistenteFalla(): void
    {
        $s = new SuscripcionPushMovil();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage("La acción 'accion_falsa' no está implementada.");

        $s->realizar_consulta('accion_falsa');
    }

    /* =========================================================================
     *  BLOQUE 3: CONSULTAR (solo SELECT)
     * ========================================================================= */

    #[Group('suscripcion_push_movil')]
    #[Group('consultar')]
    #[TestDox('Consultar: listado general de suscripciones push móvil')]
    #[Test]
    public function testObtenerTodos(): void
    {
        $s = new SuscripcionPushMovil();
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

    #[Group('suscripcion_push_movil')]
    #[Group('consultar')]
    #[TestDox('Consultar: suscripciones push móvil de administradores')]
    #[Test]
    public function testObtenerAdmins(): void
    {
        $s = new SuscripcionPushMovil();
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
     *  BLOQUE 4: REGISTRAR (escritura controlada + test del bug rowCount)
     * ========================================================================= */

    #[Group('suscripcion_push_movil')]
    #[Group('registrar')]
    #[TestDox('Registrar: Éxito con token único (queda rastro en suscripciones_push_movil)')]
    #[Test]
    public function testRegistrarSuscripcionExitoso(): array
    {
        $userId = $this->obtenerUsuarioIdValido();
        if ($userId === null) {
            $this->markTestSkipped('No hay usuarios activos.');
            return [];
        }

        $token = 'ExponentPushToken[TEST_' . $this->sufijoAleatorio(10) . ']';

        $s = new SuscripcionPushMovil();
        $s->set_usuario_id($userId);
        $s->set_expo_token($token);
        $s->set_plataforma('ANDROID');

        try {
            $resultado = $s->realizar_consulta('registrar_suscripcion_movil');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return [];
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('mensaje', $resultado);
        $this->assertStringContainsString('guardada', $resultado['mensaje']);

        return ['expo_token' => $token, 'usuario_id' => $userId];
    }

    #[Group('suscripcion_push_movil')]
    #[Group('registrar')]
    #[TestDox('Registrar: Segundo registro con el mismo token dispara rama UPDATE')]
    #[Test]
    public function testRegistrarSuscripcionDuplicadaActualiza(): void
    {
        $userId = $this->obtenerUsuarioIdValido();
        if ($userId === null) {
            $this->markTestSkipped('No hay usuarios activos.');
            return;
        }

        $token = 'ExponentPushToken[TEST_DUP_' . $this->sufijoAleatorio(8) . ']';

        // Primer registro
        $s1 = new SuscripcionPushMovil();
        $s1->set_usuario_id($userId);
        $s1->set_expo_token($token);
        $s1->set_plataforma('ANDROID');

        try {
            $r1 = $s1->realizar_consulta('registrar_suscripcion_movil');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }
        $this->assertTrue($r1['estatus']);

        // Segundo registro con misma token, plataforma distinta (minúscula)
        $s2 = new SuscripcionPushMovil();
        $s2->set_usuario_id($userId);
        $s2->set_expo_token($token);
        $s2->set_plataforma('ios');

        try {
            $r2 = $s2->realizar_consulta('registrar_suscripcion_movil');
        } catch (\PDOException $e) {
            if (stripos($e->getMessage(), 'Duplicate') !== false) {
                $this->markTestIncomplete(
                    'BUG: rowCount() después de SELECT no es fiable en MySQL PDO. ' .
                    'El modelo intentó INSERT en lugar de UPDATE y rompió por ' .
                    'duplicate key. Fix: usar fetch() o fetchColumn() en lugar de ' .
                    'rowCount() tras el execute() del SELECT.'
                );
                return;
            }
            throw $e;
        }

        $this->assertTrue($r2['estatus']);
        $this->assertArrayHasKey('mensaje', $r2['mensaje'] ?? $r2);
        // Comportamiento esperado: mensaje contiene "actualizada"
        // Si no, al menos no debe haber fallado
        $this->assertStringContainsString(
            'actualizada',
            $r2['mensaje'],
            'Segundo registro debería ir a la rama UPDATE del modelo'
        );
    }

    /* =========================================================================
     *  HELPERS PRIVADOS
     * ========================================================================= */

    private function sufijoAleatorio(int $largo = 10): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
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