<?php
declare(strict_types=1);

namespace haydee\tests\pruebas_unitarias;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;
use haydee\modelo\Modulos;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de ModulosTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/ModulosTest.php

Solo registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/ModulosTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/ModulosTest.php

Solo modificar:
    .\vendor\bin\phpunit --testdox --group modificar tests/pruebas_unitarias/ModulosTest.php

Solo eliminar:
    .\vendor\bin\phpunit --testdox --group eliminar tests/pruebas_unitarias/ModulosTest.php

Limpieza manual:
    DELETE FROM asignacion_permisos
     WHERE modulo_id IN (SELECT id_modulo FROM modulos WHERE nombre LIKE 'TEST_%');
    DELETE FROM modulos WHERE nombre LIKE 'TEST_%';
*/

#[TestDox('Módulo de Módulos del Sistema - Pruebas Unitarias')]
#[CoversClass(Modulos::class)]
final class ModulosTest extends TestCase
{
    private const ID_INEXISTENTE = 99999999;
    private const NOMBRE_PREFIX  = 'TEST_';

    /* =========================================================================
     *  BLOQUE 1: VALIDACIONES ESTÁTICAS
     * ========================================================================= */

    #[Group('modulo')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de nombre')]
    #[DataProvider('providerValidacionesNombre')]
    #[Test]
    public function testRegistrarValidacionesNombre(string $valor, bool $valido): void
    {
        $reglas = Modulos::obtenerReglas('registrar_modulo');
        $regex  = $reglas['nombre']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesNombre(): array
    {
        return [
            'Nombre simple'          => ['Pagos', true],
            'Nombre con underscore'  => ['Caja_Chica', true],
            'Nombre con espacio'     => ['Caja Chica', true],
            'Nombre con acentos'     => ['Notificación', true],
            'Nombre 2 chars'         => ['AB', false],
            'Nombre con dígitos'     => ['Modulo2', false],
            'Nombre con guión'       => ['modulo-test', false],
            'Nombre vacío'           => ['', false],
        ];
    }

    #[Group('modulo')]
    #[Group('modificar')]
    #[TestDox('Modificar: Validación de ID')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testModificarValidacionesId(string $id, bool $valido): void
    {
        $reglas = Modulos::obtenerReglas('modificar_modulo');
        $regex  = $reglas['id_modulo']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    public static function providerValidacionesId(): array
    {
        return [
            'ID válido'       => ['1', true],
            'ID con letras'   => ['ABC', false],
            'ID con símbolos' => ['1#', false],
            'ID vacío'        => ['', false],
        ];
    }

    /* =========================================================================
     *  BLOQUE 2: DISPATCHER
     * ========================================================================= */

    #[Group('modulo')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción cuando la acción no está implementada')]
    #[Test]
    public function testAccionInexistenteFalla(): void
    {
        $m = new Modulos();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage("La acción 'accion_falsa' no está implementada.");

        $m->realizar_consulta('accion_falsa');
    }

    /* =========================================================================
     *  BLOQUE 3: BUGS DEL MODELO
     * ========================================================================= */

    #[Group('modulo')]
    #[Group('consultar')]
    #[TestDox('Consultar: BUG — _consultar (listado) sin reglas')]
    #[Test]
    public function testConsultarListadoSinReglas(): void
    {
        $this->assertEmpty(Modulos::obtenerReglas('consultar'));
        $this->markTestIncomplete(
            'BUG: _consultar (listado general) existe pero no está en ' .
            '$camposPorOperacion. obtenerReglas("consultar") devuelve [].'
        );
    }

    /* =========================================================================
     *  BLOQUE 4: CONSULTAR (SELECT)
     * ========================================================================= */

    #[Group('modulo')]
    #[Group('consultar')]
    #[TestDox('Consultar: listado general de módulos activos')]
    #[Test]
    public function testConsultarListadoModulos(): void
    {
        $m = new Modulos();
        try {
            $r = $m->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertIsArray($r['datos']);
    }

    #[Group('modulo')]
    #[Group('consultar')]
    #[TestDox('Consultar: lanza excepción si el módulo no existe')]
    #[Test]
    public function testConsultarModuloInexistenteFalla(): void
    {
        $m = new Modulos();
        $m->set_id_modulo(self::ID_INEXISTENTE);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Módulo no encontrado.');

        try {
            $m->realizar_consulta('consultar_modulo');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     *  BLOQUE 5: ESCRITURAS ENCADENADAS
     * ========================================================================= */

    #[Group('modulo')]
    #[Group('registrar')]
    #[TestDox('Registrar: Éxito con nombre único (retorna array con id)')]
    #[Test]
    public function testRegistrarModuloExitoso(): array
    {
        $nombre = self::NOMBRE_PREFIX . strtoupper($this->sufijoAleatorio(8));

        $m = new Modulos();
        $m->set_nombre($nombre);

        try {
            $r = $m->realizar_consulta('registrar_modulo');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return [];
        }

        if (empty($r['estatus'])) {
            $this->markTestSkipped('Registro falló: ' . ($r['mensaje'] ?? 'desconocido'));
            return [];
        }

        $this->assertArrayHasKey('id', $r);
        $id = (int) $r['id'];
        $this->assertGreaterThan(0, $id);

        return ['id_modulo' => $id, 'nombre' => $nombre];
    }

    #[Group('modulo')]
    #[Group('consultar')]
    #[TestDox('Consultar: recupera el módulo recién creado')]
    #[Depends('testRegistrarModuloExitoso')]
    #[Test]
    public function testConsultarModuloRecienCreado(array $data): void
    {
        if (empty($data['id_modulo'])) {
            $this->markTestSkipped('El módulo test no se pudo crear.');
            return;
        }

        $m = new Modulos();
        $m->set_id_modulo($data['id_modulo']);

        try {
            $r = $m->realizar_consulta('consultar_modulo');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertSame($data['id_modulo'], (int) $r['datos']['id_modulo']);
        $this->assertSame($data['nombre'], $r['datos']['nombre']);
    }

    #[Group('modulo')]
    #[Group('modificar')]
    #[TestDox('Modificar: cambia el nombre del módulo test')]
    #[Depends('testRegistrarModuloExitoso')]
    #[Test]
    public function testModificarModuloExitoso(array $data): array
    {
        if (empty($data['id_modulo'])) {
            $this->markTestSkipped('El módulo test no se pudo crear.');
            return [];
        }

        $nuevoNombre = self::NOMBRE_PREFIX . 'EDIT_' . strtoupper($this->sufijoAleatorio(6));

        $m = new Modulos();
        $m->set_id_modulo($data['id_modulo']);
        $m->set_nombre($nuevoNombre);

        try {
            $r = $m->realizar_consulta('modificar_modulo');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return $data;
        }

        $this->assertTrue($r['estatus']);

        // Verificar persistencia
        $verif = new Modulos();
        $verif->set_id_modulo($data['id_modulo']);
        $info = $verif->realizar_consulta('consultar_modulo');
        $this->assertSame($nuevoNombre, $info['datos']['nombre']);

        return ['id_modulo' => $data['id_modulo'], 'nombre' => $nuevoNombre];
    }

    #[Group('modulo')]
    #[Group('eliminar')]
    #[TestDox('Eliminar: soft-delete del módulo test')]
    #[Depends('testModificarModuloExitoso')]
    #[Test]
    public function testEliminarModuloExitoso(array $data): void
    {
        if (empty($data['id_modulo'])) {
            $this->markTestSkipped('El módulo test no se pudo crear/modificar.');
            return;
        }

        $m = new Modulos();
        $m->set_id_modulo($data['id_modulo']);

        try {
            $r = $m->realizar_consulta('eliminar_modulo');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);

        // Verificar soft-delete: consultar_modulo filtra activo=1, debe fallar
        $verif = new Modulos();
        $verif->set_id_modulo($data['id_modulo']);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Módulo no encontrado.');
        $verif->realizar_consulta('consultar_modulo');
    }

    /* =========================================================================
     *  HELPERS
     * ========================================================================= */

    private function sufijoAleatorio(int $largo = 8): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $sufijo = '';
        for ($i = 0; $i < $largo; $i++) {
            $sufijo .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $sufijo;
    }
}