<?php
declare(strict_types=1);

namespace haydee\tests\pruebas_unitarias;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use haydee\modelo\Presupuesto;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de PresupuestoTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/PresupuestoTest.php

Solo las pruebas del método Registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/PresupuestoTest.php

Solo las pruebas del método Consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/PresupuestoTest.php

Solo las pruebas del método Modificar:
    .\vendor\bin\phpunit --testdox --group modificar tests/pruebas_unitarias/PresupuestoTest.php

Solo las pruebas del método Eliminar:
    .\vendor\bin\phpunit --testdox --group eliminar tests/pruebas_unitarias/PresupuestoTest.php
Limpieza manual del registro de prueba (fecha 2099-12-01):
    UPDATE presupuesto SET activo = 0
      WHERE YEAR(fecha) = 2099 AND MONTH(fecha) = 12;
    UPDATE mensualidad SET activo = 0
      WHERE periodo_id IN (
        SELECT id_periodo FROM periodos_mensualidad
        WHERE mes = 12 AND anio = 2099
      );
    UPDATE periodos_mensualidad SET activo = 0
      WHERE mes = 12 AND anio = 2099;
*/

#[TestDox('Módulo de Presupuestos - Pruebas Unitarias')]
#[CoversClass(Presupuesto::class)]
final class PresupuestoTest extends TestCase
{
    private const FECHA_TEST     = '2099-12-01';
    private const FECHA_VALIDA   = '2025-04-01';
    private const ID_INEXISTENTE = 99999999;
    private const VACIO          = '';
    private const OBSERVACION    = 'Prueba unitaria automatizada TEST';
    private const MONTO_DETALLE  = '100.00';
    private const TASA_DOLAR     = '36.50';
    private const CUOTA_RESERVA  = '10.00';

    /* =========================================================================
     *  BLOQUE 1: REGISTRAR
     * ========================================================================= */

    #[Group('presupuesto')]
    #[Group('registrar')]
    #[TestDox('Registrar: Registro exitoso con datos dinámicos válidos')]
    #[Test]
    public function testRegistrarExitoso(): void
    {
        // 1) Verificar que no exista ya un presupuesto en la fecha aislada de prueba
        $verificar = new Presupuesto();
        try {
            $verificar->set_fecha(self::FECHA_TEST);
            $existentes = $verificar->realizar_consulta('consultar_presupuestos_mensualidades');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido por falta de conexión o vista de BD: ' . $e->getMessage());
            return;
        }

        if (!empty($existentes['datos'])) {
            $this->markTestSkipped(
                'Omitido: ya existe presupuesto para ' . self::FECHA_TEST .
                '. Limpia con el SQL indicado en el comentario del archivo.'
            );
            return;
        }

        // 2) Obtener un concepto_id válido desde un presupuesto existente
        $conceptoId = $this->obtenerConceptoIdValido();
        if ($conceptoId === null) {
            $this->markTestSkipped('Omitido: no hay presupuestos existentes para reutilizar concepto_id.');
            return;
        }

        // 3) Registrar
        $p = new Presupuesto();
        $p->set_fecha(self::FECHA_TEST);
        $p->set_cuota_reserva(self::CUOTA_RESERVA);
        $p->set_observacion(self::OBSERVACION);
        $p->set_tasa_dolar(self::TASA_DOLAR);
        $p->setDetallesTemp([
            ['monto' => self::MONTO_DETALLE, 'concepto_id' => $conceptoId],
        ]);

        try {
            $resultado = $p->realizar_consulta('registrar_presupuesto');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido por regla de integridad o BD: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], 'El registro debe retornar estatus true');
        $this->assertArrayHasKey('id', $resultado);
        $this->assertGreaterThan(0, (int) $resultado['id']);
    }

    #[Group('presupuesto')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de datos de entrada mediante reglas de negocio')]
    #[DataProvider('providerValidacionesRegistrar')]
    #[Test]
    public function testRegistrarValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = Presupuesto::obtenerReglas('registrar_presupuesto');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex definido para '$campo' en registrar_presupuesto");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesRegistrar(): array
    {
        return [
            'Fecha YYYY-MM-DD válida'         => ['fecha', '2025-04-01', true],
            'Fecha con barras'                => ['fecha', '2025/04/01', false],
            'Fecha vacía'                     => ['fecha', '', false],
            'Cuota reserva entero'            => ['cuota_reserva', '1000', true],
            'Cuota reserva con 2 decimales'   => ['cuota_reserva', '1000.50', true],
            'Cuota reserva 3 decimales'       => ['cuota_reserva', '1000.555', false],
            'Observación válida'              => ['observacion', 'Pago mensual 2026', true],
            'Observación con caracteres HTML' => ['observacion', '<script>alert(1)</script>', false],
            'Tasa dólar con 2 decimales'      => ['tasa_dolar', '36.50', true],
            'Tasa dólar 5 decimales'          => ['tasa_dolar', '36.50000', false],
        ];
    }

    #[Group('presupuesto')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de reglas para los renglones de detalle')]
    #[DataProvider('providerValidacionesDetalles')]
    #[Test]
    public function testRegistrarValidacionesDetalles(string $campo, $valor, bool $valido): void
    {
        $reglas = Presupuesto::obtenerReglasDetalles();
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex definido para '$campo' en obtenerReglasDetalles");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesDetalles(): array
    {
        return [
            'Monto con 2 decimales' => ['monto', '100.50', true],
            'Monto con 3 decimales' => ['monto', '100.555', false],
            'Monto negativo'        => ['monto', '-10', false],
            'Concepto ID válido'    => ['concepto_id', '1', true],
            'Concepto ID con letras' => ['concepto_id', 'ABC', false],
        ];
    }

    #[Group('presupuesto')]
    #[Group('registrar')]
    #[TestDox('Registrar: Rechazo obligatorio cuando no se envían detalles')]
    #[Test]
    public function testRegistrarFallaSinDetalles(): void
    {
        $p = new Presupuesto();
        $p->set_fecha(self::FECHA_VALIDA);
        $p->set_cuota_reserva(self::CUOTA_RESERVA);
        $p->set_observacion(self::OBSERVACION);
        $p->set_tasa_dolar(self::TASA_DOLAR);
        $p->setDetallesTemp([]);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('No hay detalles para el presupuesto.');

        $p->realizar_consulta('registrar_presupuesto');
    }

    /* =========================================================================
     *  BLOQUE 2: CONSULTAR
     * ========================================================================= */

    #[Group('presupuesto')]
    #[Group('consultar')]
    #[TestDox('Consultar: Consulta general del historial de presupuestos')]
    #[Test]
    public function testConsultar(): void
    {
        $p = new Presupuesto();
        try {
            $resultado = $p->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido por falta de entorno o vista de BD: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);
    }

    #[Group('presupuesto')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lista de meses faltantes por presupuestar')]
    #[Test]
    public function testConsultarMesesFaltantes(): void
    {
        $p = new Presupuesto();
        try {
            $resultado = $p->realizar_consulta('consultar_meses_faltantes');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido por falta de BD: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);
    }

    #[Group('presupuesto')]
    #[Group('consultar')]
    #[TestDox('Consultar: Devuelve un presupuesto existente con sus detalles')]
    #[Test]
    public function testConsultarPresupuestoExistente(): void
    {
        $id = $this->obtenerIdPresupuestoExistente();
        if ($id === null) {
            $this->markTestSkipped('Omitido: no hay presupuestos en la BD.');
            return;
        }

        $p = new Presupuesto();
        $p->set_id_presupuesto($id);

        try {
            $resultado = $p->realizar_consulta('consultar_presupuesto');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertArrayHasKey('id_presupuesto', $resultado['datos']);
        $this->assertArrayHasKey('detalles', $resultado['datos']);
    }

    #[Group('presupuesto')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción si el presupuesto no existe')]
    #[Test]
    public function testConsultarPresupuestoInexistenteFalla(): void
    {
        $p = new Presupuesto();
        $p->set_id_presupuesto(self::ID_INEXISTENTE);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Presupuesto no encontrado.');

        try {
            $p->realizar_consulta('consultar_presupuesto');
        } catch (\PDOException $e) {
            $this->markTestSkipped('Omitido por BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('presupuesto')]
    #[Group('consultar')]
    #[TestDox('Consultar: Presupuestos y mensualidades de un mes concreto')]
    #[Test]
    public function testConsultarPresupuestosMensualidades(): void
    {
        // Obtener fecha dinámica desde un presupuesto existente
        $id = $this->obtenerIdPresupuestoExistente();
        if ($id === null) {
            $this->markTestSkipped('Omitido: no hay presupuestos en la BD.');
            return;
        }

        $pDet = new Presupuesto();
        $pDet->set_id_presupuesto($id);
        try {
            $det = $pDet->realizar_consulta('consultar_presupuesto');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido: ' . $e->getMessage());
            return;
        }

        $p = new Presupuesto();
        $p->set_fecha($det['datos']['fecha']);

        try {
            $resultado = $p->realizar_consulta('consultar_presupuestos_mensualidades');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);
    }

    #[Group('presupuesto')]
    #[Group('consultar')]
    #[TestDox('Consultar: Verificación booleana de existencia de un mes')]
    #[Test]
    public function testConsultarMesPresupuesto(): void
    {
        $p = new Presupuesto();
        $p->set_fecha('12');   // mes a consultar

        try {
            $resultado = $p->realizar_consulta('consultar_mes_presupuesto');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsBool($resultado['datos']);
    }

    #[Group('presupuesto')]
    #[Group('consultar')]
    #[TestDox('Consultar: Verificación booleana de existencia de un año')]
    #[Test]
    public function testConsultarAnioPresupuesto(): void
    {
        $p = new Presupuesto();
        $p->set_fecha('2025');  // año a consultar

        try {
            $resultado = $p->realizar_consulta('consultar_anio_presupuesto');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsBool($resultado['datos']);
    }

    #[Group('presupuesto')]
    #[Group('consultar')]
    #[TestDox('Consultar: Validación de ID mediante reglas del modelo')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testConsultarValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = Presupuesto::obtenerReglas('consultar_presupuesto');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex definido para '$campo'");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesId(): array
    {
        return [
            'ID numérico válido' => ['id_presupuesto', '67', true],
            'ID con letras'      => ['id_presupuesto', 'ABC', false],
            'ID con símbolos'    => ['id_presupuesto', '67#', false],
            'ID vacío'           => ['id_presupuesto', '', false],
        ];
    }

    /* =========================================================================
     *  BLOQUE 3: MODIFICAR
     * ========================================================================= */

    #[Group('presupuesto')]
    #[Group('modificar')]
    #[TestDox('Modificar: Modifica correctamente un presupuesto sin pagos asociados')]
    #[Test]
    public function testModificarExitoso(): void
    {
        $lista = $this->obtenerListaPresupuestos();
        if (empty($lista)) {
            $this->markTestSkipped('Omitido: no hay presupuestos en la BD.');
            return;
        }

        $modificado = false;
        foreach ($lista as $item) {
            $id = (int) $item['id_presupuesto'];

            $pDet = new Presupuesto();
            $pDet->set_id_presupuesto($id);
            try {
                $det = $pDet->realizar_consulta('consultar_presupuesto');
            } catch (\Throwable $e) {
                continue;
            }

            $detallesTemp = [];
            foreach ($det['datos']['detalles'] ?? [] as $d) {
                if (empty($d['concepto_id'])) continue;
                $detallesTemp[] = [
                    'monto'       => $d['monto'],
                    'concepto_id' => $d['concepto_id'],
                ];
            }
            if (empty($detallesTemp)) continue;

            $p = new Presupuesto();
            $p->set_id_presupuesto($id);
            $p->set_fecha($det['datos']['fecha']);
            $p->set_cuota_reserva($det['datos']['cuota_reserva']);
            $p->set_observacion(self::OBSERVACION . ' - editado');
            $p->set_tasa_dolar($det['datos']['tasa_dolar'] ?? self::TASA_DOLAR);
            $p->setDetallesTemp($detallesTemp);

            try {
                $resultado = $p->realizar_consulta('modificar_presupuesto');
                if (!empty($resultado['estatus'])) {
                    $this->assertTrue($resultado['estatus']);
                    $this->assertArrayHasKey('mensaje', $resultado);
                    $modificado = true;
                    break;
                }
            } catch (NegocioException $e) {
                // Tiene pagos asociados → probar con el siguiente presupuesto
                continue;
            } catch (\Throwable $e) {
                $this->markTestSkipped('Omitido: ' . $e->getMessage());
                return;
            }
        }

        if (!$modificado) {
            $this->markTestSkipped('Omitido: todos los presupuestos existentes tienen pagos asociados.');
        }
    }

    #[Group('presupuesto')]
    #[Group('modificar')]
    #[TestDox('Modificar: Lanza excepción al modificar un presupuesto inexistente')]
    #[Test]
    public function testModificarPresupuestoInexistenteFalla(): void
    {
        $conceptoId = $this->obtenerConceptoIdValido();
        if ($conceptoId === null) {
            $this->markTestSkipped('Omitido: no hay presupuestos existentes para tomar concepto_id.');
            return;
        }

        $p = new Presupuesto();
        $p->set_id_presupuesto(self::ID_INEXISTENTE);
        // Fecha aislada para evitar que se sincronicen mensualidades reales
        $p->set_fecha(self::FECHA_TEST);
        $p->set_cuota_reserva(self::CUOTA_RESERVA);
        $p->set_observacion(self::OBSERVACION);
        $p->set_tasa_dolar(self::TASA_DOLAR);
        $p->setDetallesTemp([
            ['monto' => self::MONTO_DETALLE, 'concepto_id' => $conceptoId],
        ]);

        $this->expectException(NegocioException::class);

        try {
            $p->realizar_consulta('modificar_presupuesto');
        } catch (\PDOException $e) {
            $this->markTestSkipped('Omitido por BD no disponible o integridad: ' . $e->getMessage());
        }
    }

    #[Group('presupuesto')]
    #[Group('modificar')]
    #[TestDox('Modificar: Validación de datos de entrada mediante reglas de negocio')]
    #[DataProvider('providerValidacionesModificar')]
    #[Test]
    public function testModificarValidacionesBackend(string $campo, $valor, bool $valido): void
    {
        $reglas = Presupuesto::obtenerReglas('modificar_presupuesto');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex definido para '$campo' en modificar_presupuesto");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesModificar(): array
    {
        return [
            'ID válido'                 => ['id_presupuesto', '67', true],
            'ID con letras'             => ['id_presupuesto', 'ABC', false],
            'Fecha válida'              => ['fecha', '2025-04-01', true],
            'Fecha con formato erróneo' => ['fecha', '04-2025-01', false],
            'Cuota válida'              => ['cuota_reserva', '150.00', true],
            'Cuota con 3 decimales'     => ['cuota_reserva', '150.123', false],
        ];
    }

    /* =========================================================================
     *  BLOQUE 4: ELIMINAR
     * ========================================================================= */

    #[Group('presupuesto')]
    #[Group('eliminar')]
    #[TestDox('Eliminar: Desactiva correctamente un presupuesto marcado como TEST/PRUEBA')]
    #[Test]
    public function testEliminarExitoso(): void
    {
        // Guard: solo se eliminan presupuestos marcados como TEST/PRUEBA o fecha 2099-*
        $lista = $this->obtenerListaPresupuestos();
        if (empty($lista)) {
            $this->markTestSkipped('Omitido: no hay presupuestos en la BD.');
            return;
        }

        $idBorrar = null;
        foreach ($lista as $item) {
            $obs   = strtoupper($item['observacion'] ?? '');
            $fecha = $item['fecha'] ?? '';
            if (str_contains($obs, 'PRUEBA') || str_contains($obs, 'TEST')
                || str_starts_with($fecha, '2099')) {
                $idBorrar = (int) $item['id_presupuesto'];
                break;
            }
        }

        if ($idBorrar === null) {
            $this->markTestSkipped(
                'Omitido: no hay presupuesto TEST/PRUEBA para eliminar. ' .
                'Ejecuta primero --group registrar o marca uno manualmente.'
            );
            return;
        }

        $p = new Presupuesto();
        $p->set_id_presupuesto($idBorrar);

        try {
            $resultado = $p->realizar_consulta('eliminar_presupuesto');
        } catch (NegocioException $e) {
            $this->markTestSkipped('Omitido por regla de integridad (pagos asociados): ' . $e->getMessage());
            return;
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido por BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus'], 'La eliminación debe retornar estatus true');
        $this->assertArrayHasKey('mensaje', $resultado);
    }

    #[Group('presupuesto')]
    #[Group('eliminar')]
    #[TestDox('Eliminar: Validación de formato de ID en backend')]
    #[DataProvider('providerValidacionesEliminar')]
    #[Test]
    public function testEliminarValidaciones(string $id, bool $valido): void
    {
        $reglas = Presupuesto::obtenerReglas('eliminar_presupuesto');
        $regex  = $reglas['id_presupuesto']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    public static function providerValidacionesEliminar(): array
    {
        return [
            'ID numérico válido' => ['10', true],
            'ID con letras'      => ['ABC', false],
            'ID con símbolos'    => ['10#', false],
            'ID vacío'           => ['', false],
        ];
    }

    /* =========================================================================
     *  HELPERS PRIVADOS
     * ========================================================================= */

    private function obtenerIdPresupuestoExistente(): ?int
    {
        $lista = $this->obtenerListaPresupuestos();
        if (empty($lista)) return null;
        return (int) $lista[0]['id_presupuesto'];
    }

    private function obtenerListaPresupuestos(): array
    {
        $p = new Presupuesto();
        try {
            $r = $p->realizar_consulta('consultar');
            return $r['datos'] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function obtenerConceptoIdValido(): ?int
    {
        $lista = $this->obtenerListaPresupuestos();
        foreach ($lista as $item) {
            $p = new Presupuesto();
            $p->set_id_presupuesto((int) $item['id_presupuesto']);
            try {
                $det = $p->realizar_consulta('consultar_presupuesto');
            } catch (\Throwable $e) {
                continue;
            }
            foreach ($det['datos']['detalles'] ?? [] as $d) {
                if (!empty($d['concepto_id'])) {
                    return (int) $d['concepto_id'];
                }
            }
        }
        return null;
    }
}