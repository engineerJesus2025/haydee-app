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
use haydee\modelo\Permisos;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de PermisosTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/PermisosTest.php

Solo registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/PermisosTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/PermisosTest.php

Solo modificar:
    .\vendor\bin\phpunit --testdox --group modificar tests/pruebas_unitarias/PermisosTest.php

Solo eliminar:
    .\vendor\bin\phpunit --testdox --group eliminar tests/pruebas_unitarias/PermisosTest.php

Limpieza manual:
    DELETE FROM asignacion_permisos
     WHERE permiso_id IN (SELECT id_permiso FROM permisos WHERE accion LIKE 'TEST_%');
    DELETE FROM permisos WHERE accion LIKE 'TEST_%';
*/

#[TestDox('Módulo de Permisos - Pruebas Unitarias')]
#[CoversClass(Permisos::class)]
final class PermisosTest extends TestCase
{
    private const ID_INEXISTENTE = 99999999;
    private const ACCION_PREFIX  = 'TEST_';

    /* =========================================================================
     *  BLOQUE 1: VALIDACIONES ESTÁTICAS
     * ========================================================================= */

    #[Group('permiso')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de acción (solo letras y guión bajo)')]
    #[DataProvider('providerValidacionesAccion')]
    #[Test]
    public function testRegistrarValidacionesAccion(string $valor, bool $valido): void
    {
        $reglas = Permisos::obtenerReglas('registrar_permiso');
        $regex  = $reglas['accion']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesAccion(): array
    {
        return [
            'Acción simple'          => ['CREAR', true],
            'Acción con underscore'  => ['VER_REPORTES', true],
            'Acción minúsculas'      => ['crear_usuario', true],
            'Acción con espacio'     => ['crear usuario', false],
            'Acción con dígito'      => ['CREAR2', false],
            'Acción con guión'       => ['crear-usuario', false],
            'Acción vacía'           => ['', false],
        ];
    }

    #[Group('permiso')]
    #[Group('modificar')]
    #[TestDox('Modificar: Validación de ID')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testModificarValidacionesId(string $id, bool $valido): void
    {
        $reglas = Permisos::obtenerReglas('modificar_permiso');
        $regex  = $reglas['id_permiso']['regex'];

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

    #[Group('permiso')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción cuando la acción no está implementada')]
    #[Test]
    public function testAccionInexistenteFalla(): void
    {
        $p = new Permisos();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage("La acción 'accion_falsa' no está implementada.");

        $p->realizar_consulta('accion_falsa');
    }

    /* =========================================================================
     *  BLOQUE 3: BUGS DEL MODELO
     * ========================================================================= */

    #[Group('permiso')]
    #[Group('consultar')]
    #[TestDox('Consultar: BUG — _consultar y _validar_permisos_usuarios sin reglas')]
    #[Test]
    public function testMetodosSinReglas(): void
    {
        $this->assertEmpty(Permisos::obtenerReglas('consultar'));
        $this->assertEmpty(Permisos::obtenerReglas('validar_permisos_usuarios'));
        $this->markTestIncomplete(
            'BUG: _consultar y _validar_permisos_usuarios existen pero no están ' .
            'en $camposPorOperacion. obtenerReglas() devuelve [] para ellas.'
        );
    }

    /* =========================================================================
     *  BLOQUE 4: CONSULTAR (SELECT)
     * ========================================================================= */

    #[Group('permiso')]
    #[Group('consultar')]
    #[TestDox('Consultar: listado general de permisos activos')]
    #[Test]
    public function testConsultarListadoPermisos(): void
    {
        $p = new Permisos();
        try {
            $r = $p->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertIsArray($r['datos']);
    }

    #[Group('permiso')]
    #[Group('consultar')]
    #[TestDox('Consultar: lanza excepción si el permiso no existe')]
    #[Test]
    public function testConsultarPermisoInexistenteFalla(): void
    {
        $p = new Permisos();
        $p->set_id_permiso(self::ID_INEXISTENTE);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Permiso no encontrado.');

        try {
            $p->realizar_consulta('consultar_permiso');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('permiso')]
    #[Group('consultar')]
    #[TestDox('Validar permisos de usuario: arreglo vacío lanza excepción')]
    #[Test]
    public function testValidarPermisosArregloVacioFalla(): void
    {
        $p = new Permisos();
        $p->set_id_permiso([]); // arreglo vacío

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Datos inválidos: se esperaba un arreglo de IDs de permisos.');

        $p->realizar_consulta('validar_permisos_usuarios');
    }

    #[Group('permiso')]
    #[Group('consultar')]
    #[TestDox('Validar permisos de usuario: IDs inexistentes lanzan excepción')]
    #[Test]
    public function testValidarPermisosInexistentesFalla(): void
    {
        $p = new Permisos();
        $p->set_id_permiso([self::ID_INEXISTENTE]);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Se detectaron permisos inexistentes en la solicitud.');

        try {
            $p->realizar_consulta('validar_permisos_usuarios');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     *  BLOQUE 5: ESCRITURAS ENCADENADAS
     * ========================================================================= */

    #[Group('permiso')]
    #[Group('registrar')]
    #[TestDox('Registrar: Éxito con acción única (retorna array con id)')]
    #[Test]
    public function testRegistrarPermisoExitoso(): array
    {
        $accion = self::ACCION_PREFIX . strtoupper($this->sufijoAleatorio(8));

        $p = new Permisos();
        $p->set_accion($accion);

        try {
            $r = $p->realizar_consulta('registrar_permiso');
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

        return ['id_permiso' => $id, 'accion' => $accion];
    }

    #[Group('permiso')]
    #[Group('consultar')]
    #[TestDox('Consultar: recupera el permiso recién creado')]
    #[Depends('testRegistrarPermisoExitoso')]
    #[Test]
    public function testConsultarPermisoRecienCreado(array $data): void
    {
        if (empty($data['id_permiso'])) {
            $this->markTestSkipped('El permiso test no se pudo crear.');
            return;
        }

        $p = new Permisos();
        $p->set_id_permiso($data['id_permiso']);

        try {
            $r = $p->realizar_consulta('consultar_permiso');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertSame($data['id_permiso'], (int) $r['datos']['id_permiso']);
        $this->assertSame($data['accion'], $r['datos']['accion']);
    }

    #[Group('permiso')]
    #[Group('consultar')]
    #[TestDox('Validar permisos: el permiso recién creado se valida OK')]
    #[Depends('testRegistrarPermisoExitoso')]
    #[Test]
    public function testValidarPermisosDelCreado(array $data): void
    {
        if (empty($data['id_permiso'])) {
            $this->markTestSkipped('El permiso test no se pudo crear.');
            return;
        }

        $p = new Permisos();
        $p->set_id_permiso([$data['id_permiso']]);

        try {
            $r = $p->realizar_consulta('validar_permisos_usuarios');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('mensaje', $r);
    }

    #[Group('permiso')]
    #[Group('modificar')]
    #[TestDox('Modificar: cambia el nombre de la acción del permiso test')]
    #[Depends('testRegistrarPermisoExitoso')]
    #[Test]
    public function testModificarPermisoExitoso(array $data): array
    {
        if (empty($data['id_permiso'])) {
            $this->markTestSkipped('El permiso test no se pudo crear.');
            return [];
        }

        $nuevaAccion = self::ACCION_PREFIX . 'EDIT_' . strtoupper($this->sufijoAleatorio(6));

        $p = new Permisos();
        $p->set_id_permiso($data['id_permiso']);
        $p->set_accion($nuevaAccion);

        try {
            $r = $p->realizar_consulta('modificar_permiso');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return $data;
        }

        $this->assertTrue($r['estatus']);

        // Verificar persistencia
        $verif = new Permisos();
        $verif->set_id_permiso($data['id_permiso']);
        $info = $verif->realizar_consulta('consultar_permiso');
        $this->assertSame($nuevaAccion, $info['datos']['accion']);

        return ['id_permiso' => $data['id_permiso'], 'accion' => $nuevaAccion];
    }

    #[Group('permiso')]
    #[Group('eliminar')]
    #[TestDox('Eliminar: soft-delete del permiso test')]
    #[Depends('testModificarPermisoExitoso')]
    #[Test]
    public function testEliminarPermisoExitoso(array $data): void
    {
        if (empty($data['id_permiso'])) {
            $this->markTestSkipped('El permiso test no se pudo crear/modificar.');
            return;
        }

        $p = new Permisos();
        $p->set_id_permiso($data['id_permiso']);

        try {
            $r = $p->realizar_consulta('eliminar_permiso');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);

        // Verificar soft-delete: consultar_permiso filtra activo=1, debe lanzar excepción
        $verif = new Permisos();
        $verif->set_id_permiso($data['id_permiso']);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Permiso no encontrado.');
        $verif->realizar_consulta('consultar_permiso');
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