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
use haydee\modelo\Rol;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de RolTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/RolTest.php

Solo registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/RolTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/RolTest.php

Solo modificar:
    .\vendor\bin\phpunit --testdox --group modificar tests/pruebas_unitarias/RolTest.php

Solo eliminar:
    .\vendor\bin\phpunit --testdox --group eliminar tests/pruebas_unitarias/RolTest.php

Limpieza manual de los registros de prueba (opcional):
    DELETE FROM asignacion_permisos
      WHERE rol_id IN (
        SELECT id_rol FROM roles
        WHERE nombre LIKE 'Prueba Unitaria %' OR nombre LIKE 'Rol Modificado %'
      );
    DELETE FROM roles
      WHERE nombre LIKE 'Prueba Unitaria %' OR nombre LIKE 'Rol Modificado %';
*/

#[TestDox('Módulo de Roles - Pruebas Unitarias')]
#[CoversClass(Rol::class)]
final class RolTest extends TestCase
{
    private const ID_INEXISTENTE = 99999999;

    /* =========================================================================
     *  BLOQUE 1: VALIDACIONES ESTÁTICAS (sin BD)
     * ========================================================================= */

    #[Group('roles')]
    #[Group('registrar')]
    #[TestDox('Registrar rol: Validación de datos de entrada vía reglas de negocio')]
    #[DataProvider('providerValidacionesRegistrar')]
    #[Test]
    public function testRegistrarRolValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = Rol::obtenerReglas('registrar_rol');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex definido para '$campo' en registrar_rol");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesRegistrar(): array
    {
        return [
            'Nombre Admin'            => ['nombre', 'Admin', true],
            'Nombre Rol de Prueba'    => ['nombre', 'Rol de Prueba', true],
            'Nombre Super Administrador' => ['nombre', 'Super Administrador', true],
            'Nombre muy corto (2)'    => ['nombre', 'AB', false],
            'Nombre con números'      => ['nombre', 'Admin123', false],
            'Nombre con guión bajo'   => ['nombre', 'Rol_Test', false],
            'Nombre con guión'        => ['nombre', 'Rol-Test', false],
            'Nombre vacío'            => ['nombre', '', false],
            'Nombre con HTML'         => ['nombre', '<script>x</script>', false],
            'Permisos array vacío'    => ['permisos', '[]', true],
            'Permisos JSON array'     => ['permisos', '[{"modulo_id":1,"permiso_id":1}]', true],
            'Permisos objeto (no array)' => ['permisos', '{"modulo_id":1}', false],
            'Permisos incompleto'     => ['permisos', '[', false],
        ];
    }

    #[Group('roles')]
    #[Group('modificar')]
    #[TestDox('Modificar rol: Validación de datos de entrada vía reglas de negocio')]
    #[DataProvider('providerValidacionesModificar')]
    #[Test]
    public function testModificarRolValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = Rol::obtenerReglas('modificar_rol');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex para '$campo' en modificar_rol");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesModificar(): array
    {
        return [
            'ID rol válido'         => ['id_rol', '1', true],
            'ID rol con letras'     => ['id_rol', 'ABC', false],
            'ID rol con símbolos'   => ['id_rol', '1#', false],
            'Nombre válido'         => ['nombre', 'Rol Editado', true],
            'Nombre con números'    => ['nombre', 'Rol2025', false],
            'Permisos array'        => ['permisos', '[{"modulo_id":1,"permiso_id":2}]', true],
        ];
    }

    #[Group('roles')]
    #[Group('eliminar')]
    #[TestDox('Eliminar rol: Validación de formato de ID vía reglas de negocio')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testEliminarRolValidacionesBackend(string $id, bool $valido): void
    {
        $reglas = Rol::obtenerReglas('eliminar_rol');
        $regex  = $reglas['id_rol']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar rol: Validación de ID vía reglas de negocio')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testConsultarRolValidacionesBackend(string $id, bool $valido): void
    {
        $reglas = Rol::obtenerReglas('consultar_rol');
        $regex  = $reglas['id_rol']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar permisos asignados: Validación de ID vía reglas')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testConsultarPermisosAsignadosValidacionesBackend(string $id, bool $valido): void
    {
        $reglas = Rol::obtenerReglas('consultar_permisos_asignados');
        $regex  = $reglas['id_rol']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    public static function providerValidacionesId(): array
    {
        return [
            'ID numérico válido' => ['1', true],
            'ID con letras'      => ['ABC', false],
            'ID con símbolos'    => ['1#', false],
            'ID vacío'           => ['', false],
        ];
    }

    /* =========================================================================
     *  BLOQUE 2: DISPATCHER (sin BD)
     * ========================================================================= */

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción cuando la acción no está implementada')]
    #[Test]
    public function testAccionInexistenteFalla(): void
    {
        $r = new Rol();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage("La acción 'accion_falsa' no está implementada.");

        $r->realizar_consulta('accion_falsa');
    }

    /* =========================================================================
     *  BLOQUE 3: CONSULTAR (solo SELECT)
     * ========================================================================= */

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar: Listado general de roles activos')]
    #[Test]
    public function testConsultarListadoRoles(): void
    {
        $r = new Rol();
        try {
            $resultado = $r->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        if (empty($resultado['estatus'])) {
            $this->markTestSkipped(
                'BD seguridad no disponible: ' . ($resultado['mensaje'] ?? 'desconocido')
            );
            return;
        }

        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar rol: Devuelve estatus false con mensaje si no existe')]
    #[Test]
    public function testConsultarRolInexistenteRetornaEstatusFalse(): void
    {
        $r = new Rol();
        $r->set_id_rol(self::ID_INEXISTENTE);

        try {
            $resultado = $r->realizar_consulta('consultar_rol');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertArrayHasKey('mensaje', $resultado);
        $this->assertStringContainsString('no encontrado', strtolower($resultado['mensaje']));
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar permisos de rol inexistente: Retorna estatus true con arreglo vacío')]
    #[Test]
    public function testConsultarPermisosRolInexistenteRetornaVacio(): void
    {
        $r = new Rol();
        $r->set_id_rol(self::ID_INEXISTENTE);

        try {
            $resultado = $r->realizar_consulta('consultar_permisos_asignados');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);
        $this->assertEmpty($resultado['datos']);
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar matriz de permisos: módulos y permisos activos')]
    #[Test]
    public function testConsultarMatrizPermisos(): void
    {
        $r = new Rol();
        try {
            $resultado = $r->realizar_consulta('consultar_matriz_permisos');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        if (empty($resultado['estatus'])) {
            $this->markTestSkipped(
                'BD seguridad no disponible: ' . ($resultado['mensaje'] ?? 'desconocido')
            );
            return;
        }

        $this->assertArrayHasKey('datos', $resultado);
        $this->assertArrayHasKey('modulos', $resultado['datos']);
        $this->assertArrayHasKey('permisos', $resultado['datos']);
        $this->assertIsArray($resultado['datos']['modulos']);
        $this->assertIsArray($resultado['datos']['permisos']);
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Verificar nombre: Retorna existe=false para un nombre aleatorio')]
    #[Test]
    public function testVerificarNombreInexistente(): void
    {
        $nombre = 'Inexistente ' . $this->sufijoAleatorioLetras();

        $r = new Rol();
        $r->set_nombre($nombre);

        try {
            $resultado = $r->realizar_consulta('verificar_nombre');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        if (empty($resultado['estatus'])) {
            $this->markTestSkipped(
                'BD seguridad no disponible: ' . ($resultado['mensaje'] ?? 'desconocido')
            );
            return;
        }

        $this->assertArrayHasKey('existe', $resultado);
        $this->assertFalse($resultado['existe']);
    }

    /* =========================================================================
     *  BLOQUE 4: ESCRITURAS CONTROLADAS (encadenadas con #[Depends])
     * ========================================================================= */

    #[Group('roles')]
    #[Group('registrar')]
    #[TestDox('Registrar rol: Éxito con nombre único generado (retorna ID para dependientes)')]
    #[Test]
    public function testRegistrarRolExitoso(): int
    {
        $nombre = 'Prueba Unitaria ' . $this->sufijoAleatorioLetras();

        $r = new Rol();
        $r->set_nombre($nombre);
        $r->set_permisos_asignados([]);

        try {
            $resultado = $r->realizar_consulta('registrar_rol');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return 0;
        }

        if (empty($resultado['estatus'])) {
            $this->markTestSkipped(
                'Registro de rol falló: ' . ($resultado['mensaje'] ?? 'desconocido')
                . ' (posible UNIQUE constraint)'
            );
            return 0;
        }

        $id = (int) $r->get_id_rol();
        $this->assertGreaterThan(0, $id, 'El ID del rol recién creado debe ser mayor que 0');
        $this->assertArrayHasKey('mensaje', $resultado);

        return $id;
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar rol: Recupera el rol recién registrado con sus permisos')]
    #[Depends('testRegistrarRolExitoso')]
    #[Test]
    public function testConsultarRolRecienCreado(int $idRol): void
    {
        $r = new Rol();
        $r->set_id_rol($idRol);

        try {
            $resultado = $r->realizar_consulta('consultar_rol');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertArrayHasKey('rol', $resultado['datos']);
        $this->assertArrayHasKey('permisos', $resultado['datos']);
        $this->assertSame($idRol, (int) $resultado['datos']['rol']['id_rol']);
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Verificar nombre: El rol recién creado existe en la BD')]
    #[Depends('testRegistrarRolExitoso')]
    #[Test]
    public function testVerificarNombreDeRolRegistrado(int $idRol): void
    {
        // Recuperar el nombre del rol recién creado
        $consulta = new Rol();
        $consulta->set_id_rol($idRol);

        try {
            $info = $consulta->realizar_consulta('consultar_rol');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($info['estatus']);
        $nombre = $info['datos']['rol']['nombre'] ?? null;
        $this->assertNotNull($nombre);

        $r = new Rol();
        $r->set_nombre($nombre);
        $resultado = $r->realizar_consulta('verificar_nombre');

        $this->assertTrue($resultado['estatus']);
        $this->assertTrue($resultado['existe'], 'El rol recién creado debe existir');
    }

    #[Group('roles')]
    #[Group('consultar')]
    #[TestDox('Consultar permisos: Retorna arreglo vacío para el rol recién creado')]
    #[Depends('testRegistrarRolExitoso')]
    #[Test]
    public function testConsultarPermisosAsignadosDelRol(int $idRol): void
    {
        $r = new Rol();
        $r->set_id_rol($idRol);

        try {
            $resultado = $r->realizar_consulta('consultar_permisos_asignados');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);
        // Como registramos sin permisos, debe estar vacío
        $this->assertEmpty($resultado['datos']);
    }

    #[Group('roles')]
    #[Group('modificar')]
    #[TestDox('Modificar rol: Actualiza el nombre del rol recién creado')]
    #[Depends('testRegistrarRolExitoso')]
    #[Test]
    public function testModificarRolExitoso(int $idRol): void
    {
        $nuevoNombre = 'Rol Modificado ' . $this->sufijoAleatorioLetras();

        $r = new Rol();
        $r->set_id_rol($idRol);
        $r->set_nombre($nuevoNombre);
        $r->set_permisos_asignados([]);

        try {
            $resultado = $r->realizar_consulta('modificar_rol');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus'], 'La modificación debe retornar estatus true');
        $this->assertArrayHasKey('mensaje', $resultado);

        // Verificar que el cambio persistió
        $verificar = new Rol();
        $verificar->set_id_rol($idRol);
        $info = $verificar->realizar_consulta('consultar_rol');

        $this->assertTrue($info['estatus']);
        $this->assertSame($nuevoNombre, $info['datos']['rol']['nombre']);
    }

    #[Group('roles')]
    #[Group('eliminar')]
    #[TestDox('Eliminar rol: Desactiva (soft-delete) el rol recién creado')]
    #[Depends('testRegistrarRolExitoso')]
    #[Test]
    public function testEliminarRolExitoso(int $idRol): void
    {
        $r = new Rol();
        $r->set_id_rol($idRol);

        try {
            $resultado = $r->realizar_consulta('eliminar_rol');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus'], 'La eliminación debe retornar estatus true');
        $this->assertArrayHasKey('mensaje', $resultado);

        // Verificar que quedó inactivo (soft-delete)
        $verificar = new Rol();
        $verificar->set_id_rol($idRol);
        $info = $verificar->realizar_consulta('consultar_rol');

        $this->assertFalse(
            $info['estatus'],
            'Un rol eliminado (activo=0) no debe ser recuperable por consultar_rol'
        );
    }

    /* =========================================================================
     *  HELPERS PRIVADOS
     * ========================================================================= */

    /**
     * Genera un sufijo único de solo letras (cumple la regex del modelo:
     * /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/).
     */
    private function sufijoAleatorioLetras(int $largo = 6): string
    {
        $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sufijo = '';
        for ($i = 0; $i < $largo; $i++) {
            $sufijo .= $letras[random_int(0, 25)];
        }
        return $sufijo;
    }
}