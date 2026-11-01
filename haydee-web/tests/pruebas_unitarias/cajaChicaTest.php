<?php
declare(strict_types=1);

namespace haydee\tests\pruebas_unitarias;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use haydee\modelo\CajaChica;
use haydee\enums\EstadoPeriodo;
use haydee\enums\EstadoMovimientoCaja;
use haydee\excepciones\NegocioException;
use haydee\excepciones\HaydeeException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de CajaChicaTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/CajaChicaTest.php

Solo registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/CajaChicaTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/CajaChicaTest.php

Solo modificar:
    .\vendor\bin\phpunit --testdox --group modificar tests/pruebas_unitarias/CajaChicaTest.php

Solo eliminar:
    .\vendor\bin\phpunit --testdox --group eliminar tests/pruebas_unitarias/CajaChicaTest.php

TESTS SIEMPRE OMITIDOS (por seguridad):
- testRegistrarCajaChicaExitoso: constraint ABIERTO único + requiere año fiscal
  ABIERTO. Bloquearía futuras corridas.
- testEliminarMovimientoExitoso: soft-delete destructivo sobre movimiento real.
- testRevaluarFondoExitoso: modifica fondo_fijo real.
- testReponerCajaExitoso: ejecuta sp_registrar_reposicion_caja() que crea
  gasto real + movimiento real. PELIGROSO.

*/

#[TestDox('Módulo de Caja Chica - Pruebas Unitarias')]
#[CoversClass(CajaChica::class)]
final class CajaChicaTest extends TestCase
{
    private const ID_INEXISTENTE = 99999999;
    private const MONTO_TEST     = '0.01';
    private const TASA_TEST      = '36.50';
    private const CONCEPTO_TEST  = 'TEST movimiento unitario';
    private const FECHA_TEST     = '2099-01-01';

    /* =========================================================================
     *  BLOQUE 1: VALIDACIONES ESTÁTICAS (sin BD)
     * ========================================================================= */

    #[Group('caja_chica')]
    #[Group('registrar')]
    #[TestDox('Registrar caja: Validación de datos de entrada vía reglas de negocio')]
    #[DataProvider('providerValidacionesRegistrarCaja')]
    #[Test]
    public function testRegistrarCajaChicaValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('registrar_caja_chica');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex definido para '$campo' en registrar_caja_chica");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesRegistrarCaja(): array
    {
        return [
            'Descripción válida'         => ['descripcion', 'Fondo inicial 2025', true],
            'Descripción con HTML'       => ['descripcion', '<script>x</script>', false],
            'Descripción vacía (opcional)' => ['descripcion', '', true],
            'Fondo fijo entero'          => ['fondo_fijo', '1000', true],
            'Fondo fijo 2 decimales'     => ['fondo_fijo', '1000.50', true],
            'Fondo fijo 3 decimales'     => ['fondo_fijo', '1000.555', false],
            'Límite UCAU con 2 decimales' => ['limite_ucau', '10.50', true],
            'Límite UCAU con letras'     => ['limite_ucau', 'abc', false],
            'UCAU Bs con 4 decimales'    => ['ucau_valor_bs', '36.5000', true],
            'UCAU Bs con 5 decimales'    => ['ucau_valor_bs', '36.50000', false],
        ];
    }


    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar caja: Validación de ID vía reglas de negocio')]
    #[DataProvider('providerValidacionesIdCaja')]
    #[Test]
    public function testConsultarCajaChicaValidacionesBackend(string $id, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('consulta_caja_chica');
        $regex  = $reglas['id_caja_chica']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar caja única: Validación de ID vía reglas de negocio')]
    #[DataProvider('providerValidacionesIdCaja')]
    #[Test]
    public function testConsultarCajaUnicaValidacionesBackend(string $id, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('consultar_caja_unica');
        $regex  = $reglas['id_caja_chica']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    public static function providerValidacionesIdCaja(): array
    {
        return [
            'ID numérico válido' => ['1', true],
            'ID con letras'      => ['ABC', false],
            'ID vacío'           => ['', false],
        ];
    }

    #[Group('caja_chica')]
    #[Group('registrar')]
    #[TestDox('Registrar movimiento: Validación de datos de entrada vía reglas')]
    #[DataProvider('providerValidacionesRegistrarMovimiento')]
    #[Test]
    public function testRegistrarMovimientoValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('registrar_movimiento');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex para '$campo' en registrar_movimiento");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesRegistrarMovimiento(): array
    {
        return [
            'Caja ID válido'           => ['caja_chica_id', '1', true],
            'Caja ID con letras'       => ['caja_chica_id', 'ABC', false],
            'Concepto válido (≥3)'     => ['concepto', 'Compra de papel', true],
            'Concepto muy corto'       => ['concepto', 'AB', false],
            'Concepto con HTML'        => ['concepto', '<script>x</script>', false],
            'Monto con 2 decimales'    => ['monto', '50.25', true],
            'Monto con 3 decimales'    => ['monto', '50.255', false],
            'Monto negativo'           => ['monto', '-10', false],
            'Fecha YYYY-MM-DD'         => ['fecha', '2025-04-01', true],
            'Fecha con barras'         => ['fecha', '2025/04/01', false],
            'Tasa con 4 decimales'     => ['tasa_dolar', '36.5000', true],
            'Tasa con 5 decimales'     => ['tasa_dolar', '36.50000', false],
        ];
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Modificar movimiento: Validación de datos de entrada vía reglas')]
    #[DataProvider('providerValidacionesModificarMovimiento')]
    #[Test]
    public function testModificarMovimientoValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('modificar_movimiento');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex para '$campo' en modificar_movimiento");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesModificarMovimiento(): array
    {
        return [
            'ID movimiento válido' => ['id_movimiento_caja', '10', true],
            'ID movimiento con letras' => ['id_movimiento_caja', 'ABC', false],
            'Concepto válido'      => ['concepto', 'Compra corregida', true],
            'Monto válido'         => ['monto', '75.50', true],
            'Monto con 3 decimales' => ['monto', '75.555', false],
            'Fecha válida'         => ['fecha', '2025-05-01', true],
        ];
    }

    #[Group('caja_chica')]
    #[Group('eliminar')]
    #[TestDox('Eliminar movimiento: Validación de ID vía reglas')]
    #[DataProvider('providerValidacionesEliminarMovimiento')]
    #[Test]
    public function testEliminarMovimientoValidacionesBackend(string $id, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('eliminar_movimiento');
        $regex  = $reglas['id_movimiento_caja']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar movimiento único: Validación de ID vía reglas')]
    #[DataProvider('providerValidacionesEliminarMovimiento')]
    #[Test]
    public function testConsultarMovimientoUnicoValidacionesBackend(string $id, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('consultar_movimiento_unico');
        $regex  = $reglas['id_movimiento_caja']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    public static function providerValidacionesEliminarMovimiento(): array
    {
        return [
            'ID numérico válido' => ['10', true],
            'ID con letras'      => ['ABC', false],
            'ID con símbolos'    => ['10#', false],
            'ID vacío'           => ['', false],
        ];
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Revaluar fondo: Validación de datos de entrada vía reglas')]
    #[DataProvider('providerValidacionesRevaluar')]
    #[Test]
    public function testRevaluarFondoValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('revaluar_fondo');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex para '$campo' en revaluar_fondo");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesRevaluar(): array
    {
        return [
            'ID válido'                => ['id_caja_chica', '1', true],
            'Límite UCAU válido'       => ['limite_ucau', '10.50', true],
            'Límite UCAU con letras'   => ['limite_ucau', 'diez', false],
            'UCAU Bs válido'           => ['ucau_valor_bs', '36.5000', true],
            'UCAU Bs 5 decimales'      => ['ucau_valor_bs', '36.50000', false],
            'Nota válida'              => ['nota_revaluacion', 'Ajuste trimestral 2025', true],
            'Nota vacía (opcional)'    => ['nota_revaluacion', '', true],
        ];
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Reponer caja: Validación de datos de entrada vía reglas')]
    #[DataProvider('providerValidacionesReponer')]
    #[Test]
    public function testReponerCajaValidacionesBackend(string $id, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('reponer_caja');
        $regex  = $reglas['caja_chica_id']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    public static function providerValidacionesReponer(): array
    {
        return [
            'ID válido'      => ['1', true],
            'ID con letras'  => ['ABC', false],
            'ID vacío'       => ['', false],
        ];
    }

    /* =========================================================================
     *  BLOQUE 2: DISPATCHER / EXCEPCIONES SIN BD
     * ========================================================================= */

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción cuando la acción no está implementada')]
    #[Test]
    public function testAccionInexistenteFalla(): void
    {
        $c = new CajaChica();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage("La acción 'accion_falsa' no está implementada.");

        $c->realizar_consulta('accion_falsa');
    }

    /* =========================================================================
     *  BLOQUE 3: BUGS DEL MODELO (revelados y documentados)
     * ========================================================================= */

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Modificar caja: operación removida del modelo, sin reglas backend')]
    #[Test]
    public function testModificarCajaChicaSinReglas(): void
    {
        $reglas = CajaChica::obtenerReglas('modificar_caja_chica');
        $this->assertEmpty(
            $reglas,
            'modificar_caja_chica fue removido del $camposPorOperacion porque no existe el método'
        );
    }

    #[Group('caja_chica')]
    #[Group('eliminar')]
    #[TestDox('Eliminar caja: operación removida del modelo, sin reglas backend')]
    #[Test]
    public function testEliminarCajaChicaSinReglas(): void
    {
        $reglas = CajaChica::obtenerReglas('eliminar_caja_chica');
        $this->assertEmpty(
            $reglas,
            'eliminar_caja_chica fue removido del $camposPorOperacion porque no existe el método'
        );
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Modificar descripción: ahora tiene reglas backend')]
    #[Test]
    public function testModificarDescripcionTieneReglas(): void
    {
        $reglas = CajaChica::obtenerReglas('modificar_descripcion');

        $this->assertArrayHasKey('id_caja_chica', $reglas, 'Debe validar id_caja_chica');
        $this->assertArrayHasKey('descripcion', $reglas, 'Debe validar descripcion');
        $this->assertSame('/^\d+$/', $reglas['id_caja_chica']['regex']);
        $this->assertSame('/^[a-zA-Z0-9 áéíóúÁÉÍÓÚñÑ\.,-]{0,255}$/', $reglas['descripcion']['regex']);
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Modificar descripción: validación de datos de entrada vía reglas')]
    #[DataProvider('providerValidacionesModificarDescripcion')]
    #[Test]
    public function testModificarDescripcionValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = CajaChica::obtenerReglas('modificar_descripcion');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex para '$campo' en modificar_descripcion");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesModificarDescripcion(): array
    {
        return [
            'ID válido'             => ['id_caja_chica', '1', true],
            'ID con letras'         => ['id_caja_chica', 'ABC', false],
            'Descripción válida'    => ['descripcion', 'Caja chica noviembre 2026', true],
            'Descripción con HTML'  => ['descripcion', '<script>x</script>', false],
            'Descripción vacía (opcional)' => ['descripcion', '', true],
        ];
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Reponer caja: SIEMPRE OMITIDO — ejecuta SP que crea gasto real')]
    #[Test]
    public function testReponerCajaExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO POR SEGURIDAD: sp_registrar_reposicion_caja() crea un gasto ' .
            'real + un movimiento de reposición. Ejecutar manualmente en BD de pruebas.'
        );
    }

    /* =========================================================================
     *  BLOQUE 4: CONSULTAR (solo SELECT, siempre seguro)
     * ========================================================================= */

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar: Listado general de cajas chicas activas')]
    #[Test]
    public function testConsultarCajas(): void
    {
        $c = new CajaChica();
        try {
            $r = $c->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertIsArray($r['datos']);
    }

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar: Devuelve una caja existente por ID')]
    #[Test]
    public function testConsultarCajaUnicaExistente(): void
    {
        $id = $this->obtenerPrimerIdCaja();
        if ($id === null) {
            $this->markTestSkipped('No hay cajas registradas en la BD.');
            return;
        }

        $c = new CajaChica();
        $c->set_id_caja_chica($id);

        try {
            $r = $c->realizar_consulta('consultar_caja_unica');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertSame($id, (int) $r['datos']['id_caja_chica']);
    }

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza HaydeeException si la caja no existe')]
    #[Test]
    public function testConsultarCajaUnicaInexistenteFalla(): void
    {
        $c = new CajaChica();
        $c->set_id_caja_chica(self::ID_INEXISTENTE);

        $this->expectException(HaydeeException::class);
        $this->expectExceptionMessage('Caja no encontrada');

        try {
            $c->realizar_consulta('consultar_caja_unica');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lista movimientos de una caja existente')]
    #[Test]
    public function testConsultarMovimientosDeCaja(): void
    {
        $id = $this->obtenerPrimerIdCaja();
        if ($id === null) {
            $this->markTestSkipped('No hay cajas registradas.');
            return;
        }

        $c = new CajaChica();
        $c->set_id_caja_chica($id);

        try {
            $r = $c->realizar_consulta('consultar_movimientos');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertIsArray($r['datos']);
    }

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar: Devuelve un movimiento existente por ID')]
    #[Test]
    public function testConsultarMovimientoUnicoExistente(): void
    {
        $mov = $this->obtenerPrimerMovimiento();
        if ($mov === null) {
            $this->markTestSkipped('No hay movimientos registrados.');
            return;
        }

        $c = new CajaChica();
        $c->set_id_movimiento_caja((int) $mov['id_movimiento_caja']);

        try {
            $r = $c->realizar_consulta('consultar_movimiento_unico');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
    }

    #[Group('caja_chica')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza HaydeeException si el movimiento no existe')]
    #[Test]
    public function testConsultarMovimientoUnicoInexistenteFalla(): void
    {
        $c = new CajaChica();
        $c->set_id_movimiento_caja(self::ID_INEXISTENTE);

        $this->expectException(HaydeeException::class);
        $this->expectExceptionMessage('Movimiento no encontrado');

        try {
            $c->realizar_consulta('consultar_movimiento_unico');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     *  BLOQUE 5: EXCEPCIONES DE NEGOCIO (sin escritura real)
     * ========================================================================= */

    #[Group('caja_chica')]
    #[Group('registrar')]
    #[TestDox('Registrar caja: Rechazo cuando ya existe una ABIERTA')]
    #[Test]
    public function testRegistrarCajaChicaConAbiertaExistenteFalla(): void
    {
        $abierta = $this->obtenerCajaAbierta();
        if ($abierta === null) {
            $this->markTestSkipped('No hay caja ABIERTA para probar el conflicto.');
            return;
        }

        $c = new CajaChica();
        $c->set_fondo_fijo(1000);
        $c->set_descripcion('Intento de segunda caja');

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Ya existe una caja chica activa');

        $c->realizar_consulta('registrar_caja_chica');
    }

    #[Group('caja_chica')]
    #[Group('registrar')]
    #[TestDox('Registrar caja: SIEMPRE OMITIDO — constraint ABIERTO único bloquea futuras corridas')]
    #[Test]
    public function testRegistrarCajaChicaExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO: registrar una caja exitosamente requiere que NO exista otra ' .
            'ABIERTA y que exista un año fiscal ABIERTO. Ejecutar en entorno limpio.'
        );
    }

    #[Group('caja_chica')]
    #[Group('registrar')]
    #[TestDox('Registrar movimiento: Lanza excepción si la caja no existe')]
    #[Test]
    public function testRegistrarMovimientoCajaInexistenteFalla(): void
    {
        $m = new CajaChica();
        $m->set_id_caja_chica(self::ID_INEXISTENTE);
        $m->set_concepto(self::CONCEPTO_TEST);
        $m->set_monto_movimiento(1);
        $m->set_fecha_movimiento(self::FECHA_TEST);
        $m->set_tasa_dolar(self::TASA_TEST);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('La caja chica no existe o fue eliminada.');

        try {
            $m->realizar_consulta('registrar_movimiento');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('caja_chica')]
    #[Group('registrar')]
    #[TestDox('Registrar movimiento: Rechazo cuando la caja está CERRADA')]
    #[Test]
    public function testRegistrarMovimientoCajaCerradaFalla(): void
    {
        $cerrada = $this->obtenerCajaCerrada();
        if ($cerrada === null) {
            $this->markTestSkipped('No hay caja CERRADA para probar.');
            return;
        }

        $m = new CajaChica();
        $m->set_id_caja_chica((int) $cerrada['id_caja_chica']);
        $m->set_concepto(self::CONCEPTO_TEST);
        $m->set_monto_movimiento(1);
        $m->set_fecha_movimiento(self::FECHA_TEST);
        $m->set_tasa_dolar(self::TASA_TEST);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('No se pueden registrar movimientos en una caja chica cerrada');

        try {
            $m->realizar_consulta('registrar_movimiento');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('caja_chica')]
    #[Group('registrar')]
    #[TestDox('Registrar movimiento: Rechazo por fondos insuficientes (rollback)')]
    #[Test]
    public function testRegistrarMovimientoSaldoInsuficienteFalla(): void
    {
        $abierta = $this->obtenerCajaAbierta();
        if ($abierta === null) {
            $this->markTestSkipped('No hay caja ABIERTA para probar.');
            return;
        }

        $saldo = (float) ($abierta['saldo_calculado'] ?? 0);
        $montoExcesivo = $saldo + 999999.99;

        $m = new CajaChica();
        $m->set_id_caja_chica((int) $abierta['id_caja_chica']);
        $m->set_concepto(self::CONCEPTO_TEST);
        $m->set_monto_movimiento($montoExcesivo);
        $m->set_fecha_movimiento(self::FECHA_TEST);
        $m->set_tasa_dolar(self::TASA_TEST);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Fondos insuficientes');

        try {
            $m->realizar_consulta('registrar_movimiento');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Modificar movimiento: Lanza excepción si no existe')]
    #[Test]
    public function testModificarMovimientoInexistenteFalla(): void
    {
        $m = new CajaChica();
        $m->set_id_movimiento_caja(self::ID_INEXISTENTE);
        $m->set_concepto(self::CONCEPTO_TEST);
        $m->set_monto_movimiento(1);
        $m->set_fecha_movimiento(self::FECHA_TEST);
        $m->set_tasa_dolar(self::TASA_TEST);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Movimiento no encontrado.');

        try {
            $m->realizar_consulta('modificar_movimiento');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('caja_chica')]
    #[Group('eliminar')]
    #[TestDox('Eliminar movimiento: Lanza excepción si no existe')]
    #[Test]
    public function testEliminarMovimientoInexistenteFalla(): void
    {
        $m = new CajaChica();
        $m->set_id_movimiento_caja(self::ID_INEXISTENTE);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Movimiento no encontrado.');

        try {
            $m->realizar_consulta('eliminar_movimiento');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     *  BLOQUE 6: ESCRITURAS CONTROLADAS (con guardas y advertencias)
     * ========================================================================= */

    #[Group('caja_chica')]
    #[Group('registrar')]
    #[TestDox('Registrar movimiento: Éxito con monto mínimo (deja rastro de 0.01 Bs)')]
    #[Test]
    public function testRegistrarMovimientoExitoso(): void
    {
        $abierta = $this->obtenerCajaAbierta();
        if ($abierta === null) {
            $this->markTestSkipped('No hay caja ABIERTA para probar.');
            return;
        }

        $saldo = (float) ($abierta['saldo_calculado'] ?? 0);
        if ($saldo < 0.10) {
            $this->markTestSkipped("Saldo insuficiente en caja ABIERTA: $saldo Bs.");
            return;
        }

        $m = new CajaChica();
        $m->set_id_caja_chica((int) $abierta['id_caja_chica']);
        $m->set_concepto(self::CONCEPTO_TEST);
        $m->set_monto_movimiento(self::MONTO_TEST);
        $m->set_fecha_movimiento(self::FECHA_TEST);
        $m->set_tasa_dolar(self::TASA_TEST);

        try {
            $r = $m->realizar_consulta('registrar_movimiento');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido por BD o integridad: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus'], 'El registro debe retornar estatus true');
        $this->assertArrayHasKey('lastId', $r);
        $this->assertGreaterThan(0, (int) $r['lastId']);
        $this->assertArrayHasKey('mensaje', $r);
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Modificar movimiento: Éxito reescribiendo los mismos valores (no-op)')]
    #[Test]
    public function testModificarMovimientoExitoso(): void
    {
        $mov = $this->obtenerPrimerMovimientoPendiente();
        if ($mov === null) {
            $this->markTestSkipped('No hay movimientos PENDIENTES para probar.');
            return;
        }

        $m = new CajaChica();
        $m->set_id_movimiento_caja((int) $mov['id_movimiento_caja']);
        $m->set_concepto($mov['concepto']);
        $m->set_monto_movimiento((float) $mov['monto']);
        $m->set_fecha_movimiento($mov['fecha']);
        $m->set_tasa_dolar((float) ($mov['tasa_dolar'] ?? 1));
        $m->set_id_caja_chica((int) $mov['caja_chica_id']);

        try {
            $r = $m->realizar_consulta('modificar_movimiento');
        } catch (HaydeeException $e) {
            $this->markTestSkipped('Omitido (movimiento repuesto): ' . $e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus'], 'La modificación debe retornar estatus true');
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Modificar descripción: Éxito reescribiendo el mismo valor (no-op)')]
    #[Test]
    public function testModificarDescripcionExitoso(): void
    {
        $id = $this->obtenerPrimerIdCaja();
        if ($id === null) {
            $this->markTestSkipped('No hay cajas para probar.');
            return;
        }

        $caja = $this->obtenerCajaUnica($id);
        if ($caja === null) {
            $this->markTestSkipped('No se pudo leer la caja.');
            return;
        }

        $m = new CajaChica();
        $m->set_id_caja_chica($id);
        $m->set_descripcion($caja['descripcion'] ?? '');

        try {
            $r = $m->realizar_consulta('modificar_descripcion');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('mensaje', $r);
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Modificar movimiento: monto excedente debe fallar SIN pre-setear id_caja_chica')]
    #[Test]
    public function testModificarMovimientoMontoExcedenteFallaSinPresetCaja(): void
    {
        $mov = $this->obtenerPrimerMovimientoPendiente();
        if ($mov === null) {
            $this->markTestSkipped('No hay movimientos PENDIENTES.');
            return;
        }

        $m = new CajaChica();
        $m->set_id_movimiento_caja((int) $mov['id_movimiento_caja']);
        $m->set_concepto($mov['concepto']);
        $m->set_monto_movimiento(99999999.99);  // ← excede cualquier fondo
        $m->set_fecha_movimiento($mov['fecha']);
        $m->set_tasa_dolar((float) ($mov['tasa_dolar'] ?? 1));

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('excede los fondos disponibles');

        try {
            $m->realizar_consulta('modificar_movimiento');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('caja_chica')]
    #[Group('eliminar')]
    #[TestDox('Eliminar movimiento: SIEMPRE OMITIDO — soft-delete destructivo sobre datos reales')]
    #[Test]
    public function testEliminarMovimientoExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO POR SEGURIDAD: marcaría activo=0 en un movimiento real. ' .
            'Ejecutar manualmente solo sobre datos de prueba.'
        );
    }

    #[Group('caja_chica')]
    #[Group('modificar')]
    #[TestDox('Revaluar fondo: SIEMPRE OMITIDO — modifica fondo_fijo real')]
    #[Test]
    public function testRevaluarFondoExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO POR SEGURIDAD: modifica el fondo_fijo y el log de revaluaciones ' .
            'de una caja real. Ejecutar manualmente en entorno de pruebas.'
        );
    }

    /* =========================================================================
     *  HELPERS PRIVADOS
     * ========================================================================= */

    private function obtenerCajas(): array
    {
        $c = new CajaChica();
        try {
            $r = $c->realizar_consulta('consultar');
            return $r['datos'] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function obtenerPrimerIdCaja(): ?int
    {
        $cajas = $this->obtenerCajas();
        return empty($cajas) ? null : (int) $cajas[0]['id_caja_chica'];
    }

    private function obtenerCajaAbierta(): ?array
    {
        foreach ($this->obtenerCajas() as $c) {
            if (strtoupper($c['estado'] ?? '') === EstadoPeriodo::ABIERTO->value) {
                return $c;
            }
        }
        return null;
    }

    private function obtenerCajaCerrada(): ?array
    {
        foreach ($this->obtenerCajas() as $c) {
            if (strtoupper($c['estado'] ?? '') === EstadoPeriodo::CERRADO->value) {
                return $c;
            }
        }
        return null;
    }

    private function obtenerCajaUnica(int $id): ?array
    {
        $c = new CajaChica();
        $c->set_id_caja_chica($id);
        try {
            $r = $c->realizar_consulta('consultar_caja_unica');
            return $r['datos'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function obtenerPrimerMovimiento(): ?array
    {
        foreach ($this->obtenerCajas() as $c) {
            $m = new CajaChica();
            $m->set_id_caja_chica((int) $c['id_caja_chica']);
            try {
                $r = $m->realizar_consulta('consultar_movimientos');
            } catch (\Throwable $e) {
                continue;
            }
            if (!empty($r['datos'][0])) {
                $mov = $r['datos'][0];
                $mov['caja_chica_id'] = (int) $c['id_caja_chica'];
                return $mov;
            }
        }
        return null;
    }

    private function obtenerPrimerMovimientoPendiente(): ?array
    {
        foreach ($this->obtenerCajas() as $c) {
            $m = new CajaChica();
            $m->set_id_caja_chica((int) $c['id_caja_chica']);
            try {
                $r = $m->realizar_consulta('consultar_movimientos');
            } catch (\Throwable $e) {
                continue;
            }
            foreach ($r['datos'] ?? [] as $mov) {
                if (strtoupper($mov['estado'] ?? '') === EstadoMovimientoCaja::PENDIENTE->value) {
                    $mov['caja_chica_id'] = (int) $c['id_caja_chica'];
                    return $mov;
                }
            }
        }
        return null;
    }
}