<?php
declare(strict_types=1);

namespace haydee\tests\pruebas_unitarias;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;
use haydee\modelo\ClaveSesion;
use haydee\modelo\Usuario;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de ClaveSesionTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/ClaveSesionTest.php

Solo escrituras:
    .\vendor\bin\phpunit --testdox --group claves tests/pruebas_unitarias/ClaveSesionTest.php


Limpieza manual (por si el test chain falla antes del finally):
    DELETE FROM claves_sesion WHERE dispositivo_id LIKE 'TEST_DEV_%';
*/

#[TestDox('Módulo de Claves de Sesión - Pruebas Unitarias')]
#[CoversClass(ClaveSesion::class)]
final class ClaveSesionTest extends TestCase
{
    /* =========================================================================
     *  BLOQUE 1: DISEÑO
     * ========================================================================= */

    #[Group('claves')]
    #[Group('diseno')]
    #[TestDox('Diseño: ClaveSesion no implementa dispatcher ni obtenerReglas')]
    #[Test]
    public function testDisenoInconsistente(): void
    {
        $reflection = new \ReflectionClass(ClaveSesion::class);

        $this->assertFalse($reflection->hasMethod('realizar_consulta'));
        $this->assertFalse($reflection->hasMethod('obtenerReglas'));

        $this->assertTrue($reflection->hasMethod('guardarClave'));
        $this->assertTrue($reflection->hasMethod('obtenerClave'));
        $this->assertTrue($reflection->hasMethod('eliminarClave'));

        $this->markTestIncomplete(
            'DISEÑO: ClaveSesion rompe el patrón del proyecto: sin ' .
            'realizar_consulta() ni obtenerReglas(). Métodos públicos directos. ' .
            'Los valores de retorno son bool/string|null, no arrays con estatus. ' .
            'Documentar la diferencia o refactorizar.'
        );
    }

    /* =========================================================================
     *  BLOQUE 2: OBTENER CLAVE DE DISPOSITIVO INEXISTENTE (safe, solo SELECT)
     * ========================================================================= */

    #[Group('claves')]
    #[Group('consultar')]
    #[TestDox('Obtener clave: dispositivo inexistente retorna null (sin INSERT)')]
    #[Test]
    public function testObtenerClaveDispositivoInexistenteRetornaNull(): void
    {
        $cs = new ClaveSesion();

        try {
            $resultado = $cs->obtenerClave('TEST_DEV_INEXISTENTE_' . $this->sufijo(8));
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertNull($resultado, 'Dispositivo inexistente debe retornar null');
    }

    /* =========================================================================
     *  BLOQUE 3: ELIMINAR CLAVE DE DISPOSITIVO INEXISTENTE (safe, DELETE 0 filas)
     * ========================================================================= */

    #[Group('claves')]
    #[Group('eliminar')]
    #[TestDox('Eliminar clave: dispositivo inexistente retorna true (idempotente)')]
    #[Test]
    public function testEliminarClaveDispositivoInexistenteEsIdempotente(): void
    {
        $cs = new ClaveSesion();

        try {
            // DELETE afecta 0 filas, PDO::execute retorna true
            $r1 = $cs->eliminarClave('TEST_DEV_INEXISTENTE_' . $this->sufijo(8));
            $r2 = $cs->eliminarClave('TEST_DEV_INEXISTENTE_' . $this->sufijo(8));
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r1);
        $this->assertTrue($r2);
    }

    /* =========================================================================
     *  BLOQUE 4: CICLO COMPLETO (guardar → obtener → eliminar → verificar)
     *  Este es el patrón ideal para ClaveSesion.
     * ========================================================================= */

    #[Group('claves')]
    #[Group('registrar')]
    #[TestDox('Guardar clave: éxito con dispositivo único (retorna dispositivo_id para dependientes)')]
    #[Test]
    public function testGuardarClaveExitoso(): array
    {
        $usuarioId = $this->obtenerUsuarioIdValido();
        if ($usuarioId === null) {
            $this->markTestSkipped('No hay usuarios activos.');
            return [];
        }

        $dispositivoId = 'TEST_DEV_' . $this->sufijo(10);
        $claveAes      = $this->generarClaveAesFalsa();

        $cs = new ClaveSesion();

        try {
            $resultado = $cs->guardarClave($dispositivoId, $usuarioId, $claveAes);
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return [];
        }

        $this->assertTrue(
            $resultado,
            'guardarClave() debe retornar true al ejecutar INSERT correctamente'
        );

        return [
            'dispositivo_id' => $dispositivoId,
            'usuario_id'     => $usuarioId,
            'clave_aes'      => $claveAes,
        ];
    }

    #[Group('claves')]
    #[Group('consultar')]
    #[TestDox('Obtener clave: recupera la clave recién guardada para el dispositivo')]
    #[Depends('testGuardarClaveExitoso')]
    #[Test]
    public function testObtenerClaveRecienGuardada(array $data): void
    {
        if (empty($data['dispositivo_id'])) {
            $this->markTestSkipped('La clave test no se pudo guardar.');
            return;
        }

        $cs = new ClaveSesion();
        $resultado = $cs->obtenerClave($data['dispositivo_id']);

        $this->assertSame(
            $data['clave_aes'],
            $resultado,
            'obtenerClave() debe retornar exactamente la clave almacenada'
        );
    }

    #[Group('claves')]
    #[Group('registrar')]
    #[TestDox('Guardar clave: segundo guardado actualiza la clave (UPSERT)')]
    #[Depends('testGuardarClaveExitoso')]
    #[Test]
    public function testGuardarClaveActualizaExistente(array $data): array
    {
        if (empty($data['dispositivo_id'])) {
            $this->markTestSkipped('La clave test no se pudo guardar.');
            return [];
        }

        $nuevaClave = $this->generarClaveAesFalsa();

        $cs = new ClaveSesion();
        $resultado = $cs->guardarClave(
            $data['dispositivo_id'],
            $data['usuario_id'],
            $nuevaClave
        );

        $this->assertTrue($resultado, 'UPSERT debe retornar true');

        // Verificar que la clave realmente se actualizó
        $verif = $cs->obtenerClave($data['dispositivo_id']);
        $this->assertSame($nuevaClave, $verif, 'La clave debe haber sido actualizada');

        return [
            'dispositivo_id' => $data['dispositivo_id'],
            'usuario_id'     => $data['usuario_id'],
            'clave_aes'      => $nuevaClave,
        ];
    }

    #[Group('claves')]
    #[Group('eliminar')]
    #[TestDox('Eliminar clave: borra la fila y obtenerClave retorna null (cleanup final)')]
    #[Depends('testGuardarClaveActualizaExistente')]
    #[Test]
    public function testEliminarClaveExitoso(array $data): void
    {
        if (empty($data['dispositivo_id'])) {
            $this->markTestSkipped('La clave test no se pudo guardar.');
            return;
        }

        $cs = new ClaveSesion();
        $resultado = $cs->eliminarClave($data['dispositivo_id']);

        $this->assertTrue($resultado, 'eliminarClave() debe retornar true');

        // Verificar que ya no existe
        $verif = $cs->obtenerClave($data['dispositivo_id']);
        $this->assertNull(
            $verif,
            'Después de eliminar, obtenerClave() debe retornar null'
        );
    }

    /* =========================================================================
     *  HELPERS
     * ========================================================================= */

    private function sufijo(int $largo = 10): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $sufijo = '';
        for ($i = 0; $i < $largo; $i++) {
            $sufijo .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $sufijo;
    }

    /**
     * Genera un blob AES simulado (32 bytes en base64 = 44 caracteres).
     * Solo para cumplir con la columna clave_aes (probablemente VARBINARY/BLOB).
     */
    private function generarClaveAesFalsa(): string
    {
        $bytes = '';
        for ($i = 0; $i < 32; $i++) {
            $bytes .= chr(random_int(0, 255));
        }
        return base64_encode($bytes);
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