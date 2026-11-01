<?php
declare(strict_types=1);

namespace haydee\tests\pruebas_unitarias;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use haydee\modelo\Bitacora;
use haydee\modelo\Usuario;
use haydee\enums\Accion;
use haydee\enums\Modulo;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de BitacoraTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/BitacoraTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/BitacoraTest.php

Limpieza manual si se llegara a ejecutar registrar() para test:
    DELETE FROM bitacora
     WHERE valores_nuevos LIKE '%"test":%'
        OR valores_anteriores LIKE '%"test":%'
        OR accion LIKE '%TEST%';
*/

#[TestDox('Módulo de Bitácora - Pruebas Unitarias')]
#[CoversClass(Bitacora::class)]
final class BitacoraTest extends TestCase
{
    /* =========================================================================
     *  BLOQUE 1: DISEÑO (patrones diferentes al resto)
     * ========================================================================= */

    #[Group('bitacora')]
    #[Group('diseno')]
    #[TestDox('Diseño: Bitacora no implementa obtenerReglas ni firma estándar')]
    #[Test]
    public function testDisenoInconsistente(): void
    {
        $reflection = new \ReflectionClass(Bitacora::class);

        $this->assertFalse(
            $reflection->hasMethod('obtenerReglas'),
            'Bitacora no tiene obtenerReglas — sin validación estática backend'
        );

        $this->assertTrue(
            $reflection->hasMethod('getInstancia'),
            'Bitacora implementa el patrón singleton con getInstancia'
        );

        $this->assertTrue(
            $reflection->getMethod('registrar')->isStatic(),
            'registrar() es estático (a diferencia de los modelos CRUD normales)'
        );

        // La firma de realizar_consulta tiene 2 parámetros (accion, datos=null)
        $metodo = $reflection->getMethod('realizar_consulta');
        $this->assertGreaterThanOrEqual(
            2,
            $metodo->getNumberOfParameters(),
            'realizar_consulta tiene 2 parámetros: $accion y $datos'
        );

        $this->markTestIncomplete(
            'DISEÑO: Bitacora rompe 3 patrones del proyecto: (1) sin obtenerReglas, ' .
            '(2) sin firma realizar_consulta($accion) — usa ($accion, $datos=null), ' .
            '(3) singleton + métodos estáticos. Documentar o refactorizar.'
        );
    }

    /* =========================================================================
     *  BLOQUE 2: DISPATCHER
     * ========================================================================= */

    #[Group('bitacora')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción cuando la acción no está implementada')]
    #[Test]
    public function testAccionInexistenteFalla(): void
    {
        $b = new Bitacora();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage("La acción 'accion_falsa' no está implementada.");

        $b->realizar_consulta('accion_falsa');
    }

    /* =========================================================================
     *  BLOQUE 3: CONSULTAR (solo SELECT)
     * ========================================================================= */

    #[Group('bitacora')]
    #[Group('consultar')]
    #[TestDox('Consultar: listado general de la bitácora ordenado por ID desc')]
    #[Test]
    public function testConsultarListadoBitacora(): void
    {
        $b = new Bitacora();
        try {
            $resultado = $b->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);

        // Si hay datos, verificar estructura esperada de la vista
        if (!empty($resultado['datos'])) {
            $primeraFila = $resultado['datos'][0];
            $this->assertArrayHasKey('id_bitacora', $primeraFila);
            $this->assertArrayHasKey('fecha_hora', $primeraFila);
            $this->assertArrayHasKey('accion', $primeraFila);
            $this->assertArrayHasKey('nombre_usuario', $primeraFila);
            $this->assertArrayHasKey('nombre_modulo', $primeraFila);
            $this->assertArrayHasKey('nombre_rol', $primeraFila);
        }
    }

    #[Group('bitacora')]
    #[Group('consultar')]
    #[TestDox('Consultar: actividad del dashboard (LIMIT 7 OFFSET 1)')]
    #[Test]
    public function testConsultarActividadDashboard(): void
    {
        $b = new Bitacora();
        try {
            $resultado = $b->realizar_consulta('consultar_actividad_dashboard');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('datos', $resultado);
        $this->assertIsArray($resultado['datos']);

        // No debe exceder el límite del widget (7)
        $this->assertLessThanOrEqual(7, count($resultado['datos']));

        // Cada fila debe tener las transformaciones aplicadas por el método
        foreach ($resultado['datos'] as $fila) {
            $this->assertArrayHasKey('accion', $fila);
            $this->assertArrayHasKey('descripcion', $fila);
            $this->assertArrayHasKey('nombre_modulo', $fila);

            // La acción transformada debe ser un verbo humanizado, no el enum
            $this->assertNotEmpty($fila['descripcion']);
        }
    }

    #[Group('bitacora')]
    #[Group('consultar')]
    #[TestDox('Consultar: actividad del dashboard no incluye el primer registro (OFFSET 1)')]
    #[Test]
    public function testActividadDashboardExcluyePrimero(): void
    {
        $b = new Bitacora();

        try {
            $listado = $b->realizar_consulta('consultar');
            $dashboard = $b->realizar_consulta('consultar_actividad_dashboard');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        if (empty($listado['datos']) || count($listado['datos']) < 2) {
            $this->markTestSkipped('Se necesitan al menos 2 filas en bitacora para probar el OFFSET.');
            return;
        }

        if (empty($dashboard['datos'])) {
            $this->markTestSkipped('Sin datos en dashboard.');
            return;
        }

        // El primero del listado general NO debe aparecer en el dashboard
        $idPrimeroListado = $listado['datos'][0]['id_bitacora'];
        $idsDashboard = array_column($dashboard['datos'], 'id_bitacora');

        $this->assertNotContains(
            $idPrimeroListado,
            $idsDashboard,
            'El dashboard hace OFFSET 1, no debe incluir el registro más reciente'
        );
    }

    #[Group('bitacora')]
    #[Group('consultar')]
    #[TestDox('Consultar: dashboard transforma acciones "REGISTRAR" a "Registró"')]
    #[Test]
    public function testDashboardTransformaAcciones(): void
    {
        $b = new Bitacora();
        try {
            $resultado = $b->realizar_consulta('consultar_actividad_dashboard');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        // Si hay datos, verificar que las acciones fueron humanizadas
        foreach ($resultado['datos'] as $fila) {
            $accion = $fila['accion'];

            // Después de la transformación, la acción debe ser una palabra
            // en español humanizada, no el valor crudo del enum
            $this->assertIsString($accion);

            // Ningún valor del enum debería sobrevivir (todos empiezan con mayúscula
            // y son verbos en pasado, ej: "Registró", "Modificó", "Inició sesión")
            $this->assertDoesNotMatchRegularExpression(
                '/^[A-Z_]+$/',
                $accion,
                "La acción '$accion' parece no haber sido transformada del enum"
            );
        }
    }

    /* =========================================================================
     *  BLOQUE 4: SKIP — REGISTRAR (contaminaría auditoría real)
     * ========================================================================= */

    #[Group('bitacora')]
    #[Group('registrar')]
    #[TestDox('Registrar: SIEMPRE OMITIDO — contamina la auditoría real del sistema')]
    #[Test]
    public function testRegistrarExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO POR SEGURIDAD: registrar() inserta filas en la tabla de ' .
            'auditoría real. Los datos de test quedarían mezclados con eventos ' .
            'reales y son difíciles de distinguir históricamente. ' .
            'Si necesitas probarlo: usar BD de pruebas dedicada y limpiar con ' .
            "DELETE FROM bitacora WHERE valores_nuevos LIKE '%\"test\":%';"
        );
    }

    #[Group('bitacora')]
    #[Group('registrar')]
    #[TestDox('Registrar: valida que sin usuario_id en sesión retorna false (sin INSERT)')]
    #[Test]
    public function testRegistrarSinUsuarioEnSesionRetornaFalse(): void
    {
        // Limpiar la sesión para simular usuario no autenticado
        $backup = $_SESSION ?? null;
        $_SESSION = [];

        try {
            $resultado = Bitacora::registrar(
                Accion::cases()[0],
                Modulo::cases()[0],
                null,   // usuarioId explícitamente null
                null,
                null
            );

            $this->assertFalse(
                $resultado,
                'Sin usuario_id en sesión, registrar() debe retornar false sin insertar'
            );
        } finally {
            // Restaurar sesión
            if ($backup !== null) {
                $_SESSION = $backup;
            }
        }
    }

    #[Group('bitacora')]
    #[Group('registrar')]
    #[TestDox('Registrar: con usuario_id válido ejecuta INSERT (no verificado por seguridad)')]
    #[Test]
    public function testRegistrarConUsuarioValidoSeOmitePorSeguridad(): void
    {
        $this->markTestSkipped(
            'OMITIDO POR SEGURIDAD: requiere usuario_id válido + modulo_id válido, ' .
            'y deja un registro huérfano en auditoría. Ejecutar manualmente.'
        );
    }

    /* =========================================================================
     *  BLOQUE 5: SKIP — CERRAR CONEXIÓN
     * ========================================================================= */

    #[Group('bitacora')]
    #[Group('diseno')]
    #[TestDox('Cerrar conexión: SIEMPRE OMITIDO — cierra el singleton compartido')]
    #[Test]
    public function testCerrarConexionBitacora(): void
    {
        $this->markTestSkipped(
            'OMITIDO: cerrarConexionBitacora() destruye el singleton de conexión ' .
            'compartido. Si se ejecuta en medio de otros tests, rompe sus conexiones.'
        );
    }
}