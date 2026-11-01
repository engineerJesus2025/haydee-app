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
use haydee\modelo\Notificaciones;
use haydee\modelo\Usuario;
use haydee\enums\TipoEventoNotificacion;
use haydee\enums\TablaOrigen;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de NotificacionesTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/NotificacionesTest.php

Solo registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/NotificacionesTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/NotificacionesTest.php

Limpieza manual:
    DELETE FROM notificaciones WHERE titulo LIKE 'TEST_%';
*/

#[TestDox('Módulo de Notificaciones - Pruebas Unitarias')]
#[CoversClass(Notificaciones::class)]
final class NotificacionesTest extends TestCase
{
    private const ID_INEXISTENTE = 99999999;
    private const TITULO_PREFIX  = 'TEST_';

    /* =========================================================================
     *  BLOQUE 1: VALIDACIONES ESTÁTICAS (sin BD)
     * ========================================================================= */

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de título')]
    #[DataProvider('providerValidacionesTitulo')]
    #[Test]
    public function testRegistrarValidacionesTitulo(string $valor, bool $valido): void
    {
        $reglas = Notificaciones::obtenerReglas('registrar_notificacion');
        $regex  = $reglas['titulo']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesTitulo(): array
    {
        return [
            'Título normal'          => ['Pago recibido', true],
            'Título con acentos'     => ['Notificación nueva', true],
            'Título 2 caracteres'    => ['AB', false],
            'Título con #'           => ['Título #1', false],
            'Título con HTML'        => ['<script>x</script>', false],
            'Título vacío'           => ['', false],
        ];
    }

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de descripción')]
    #[DataProvider('providerValidacionesDescripcion')]
    #[Test]
    public function testRegistrarValidacionesDescripcion(string $valor, bool $valido): void
    {
        $reglas = Notificaciones::obtenerReglas('registrar_notificacion');
        $regex  = $reglas['descripcion']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesDescripcion(): array
    {
        return [
            'Descripción normal'   => ['El pago fue procesado', true],
            'Descripción 2 chars'  => ['OK', false],
            'Descripción 256'      => [str_repeat('A', 256), false],
            'Descripción 255'      => [str_repeat('A', 255), true],
            'Descripción vacía'    => ['', false],
        ];
    }

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de tipo_evento')]
    #[Test]
    public function testRegistrarValidacionesTipoEvento(): void
    {
        $reglas = Notificaciones::obtenerReglas('registrar_notificacion');
        $regex  = $reglas['tipo_evento']['regex'];

        $tipoValido = TipoEventoNotificacion::cases()[0]->value;
        $this->assertSame(1, preg_match($regex, $tipoValido));
        $this->assertSame(0, preg_match($regex, 'TIPO_INVALIDO_XYZ'));
    }

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de tabla_origen')]
    #[Test]
    public function testRegistrarValidacionesTablaOrigen(): void
    {
        $reglas = Notificaciones::obtenerReglas('registrar_notificacion');
        $regex  = $reglas['tabla_origen']['regex'];

        $tablaValida = TablaOrigen::cases()[0]->value;
        $this->assertSame(1, preg_match($regex, $tablaValida));
        $this->assertSame(0, preg_match($regex, 'tabla_inexistente'));
    }

    #[Group('notificacion')]
    #[Group('consultar')]
    #[TestDox('Consultar: Validación de ID de notificación (marcar_leida)')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testMarcarLeidaValidacionesId(string $id, bool $valido): void
    {
        $reglas = Notificaciones::obtenerReglas('marcar_leida');
        $regex  = $reglas['id_notificacion']['regex'];

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

    #[Group('notificacion')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción cuando la acción no está implementada')]
    #[Test]
    public function testAccionInexistenteFalla(): void
    {
        $n = new Notificaciones();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage("La acción 'accion_falsa' no está implementada.");

        $n->realizar_consulta('accion_falsa');
    }

    /* =========================================================================
     *  BLOQUE 3: BUGS DEL MODELO
     * ========================================================================= */

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Registrar: BUG — registrar_notificacion sin método implementado')]
    #[Test]
    public function testRegistrarNotificacionNoImplementado(): void
    {
        $this->markTestIncomplete(
            'BUG: obtenerReglas("registrar_notificacion") devuelve reglas para ' .
            'titulo, descripcion, tipo_evento y tabla_origen, pero NO existe ' .
            '_registrar_notificacion(). El método real es _registrar_simple(), ' .
            'que NO tiene entrada en $camposPorOperacion (sin validación backend).'
        );
    }

    #[Group('notificacion')]
    #[Group('consultar')]
    #[TestDox('Consultar: BUG — _consultar_mis_notificaciones sin reglas')]
    #[Test]
    public function testConsultarMisNotificacionesSinReglas(): void
    {
        $reglas = Notificaciones::obtenerReglas('consultar_mis_notificaciones');
        $this->assertEmpty($reglas);
        $this->markTestIncomplete(
            'BUG: _consultar_mis_notificaciones, _marcar_todas_leidas y todos ' .
            'los _notificar_* no están en $camposPorOperacion. obtenerReglas() ' .
            'devuelve [] para todas ellas.'
        );
    }

    /* =========================================================================
     *  BLOQUE 4: CONSULTAR (solo SELECT)
     * ========================================================================= */

    #[Group('notificacion')]
    #[Group('consultar')]
    #[TestDox('Consultar: mis notificaciones de un usuario existente')]
    #[Test]
    public function testConsultarMisNotificaciones(): void
    {
        $usuarioId = $this->obtenerUsuarioIdValido();
        if ($usuarioId === null) {
            $this->markTestSkipped('No hay usuarios activos en BD seguridad.');
            return;
        }

        $n = new Notificaciones();
        $n->set_usuario_id($usuarioId);

        try {
            $resultado = $n->realizar_consulta('consultar_mis_notificaciones');
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
     *  BLOQUE 5: SKIPS MASIVOS (notificar a todos / SP)
     * ========================================================================= */

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Notificar todos: SIEMPRE OMITIDO — INSERT masivo a todos los usuarios')]
    #[Test]
    public function testNotificarTodosExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO: _notificar_todos() inserta 1 notificación POR CADA usuario ' .
            'activo en la BD (puede ser 50+ filas). Sin cleanup automático. ' .
            'Ejecutar manualmente en entorno de pruebas.'
        );
    }

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Notificar por rol: SIEMPRE OMITIDO — INSERT masivo por rol')]
    #[Test]
    public function testNotificarPorRolExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO: _notificar_por_rol() inserta notificaciones a TODOS los ' .
            'usuarios de un rol. Ejecutar manualmente con cleanup previo.'
        );
    }

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Notificar pago: SIEMPRE OMITIDO — INSERT masivo a admins')]
    #[Test]
    public function testNotificarPagoExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO: _notificar_pago() inserta notificaciones a todos los ' .
            'administradores activos. Ejecutar manualmente con cleanup previo.'
        );
    }

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Notificar evento admins: SIEMPRE OMITIDO — ejecuta SP')]
    #[Test]
    public function testNotificarEventoAdminsExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO: _notificar_evento_admins() ejecuta ' .
            'sp_notificar_administradores() que inserta evento + notificaciones ' .
            'para todos los admins. Muy invasivo.'
        );
    }

    /* =========================================================================
     *  BLOQUE 6: ESCRITURAS CONTROLADAS (con #[Depends])
     * ========================================================================= */

    #[Group('notificacion')]
    #[Group('registrar')]
    #[TestDox('Registrar simple: Éxito con título único (retorna datos para dependientes)')]
    #[Test]
    public function testRegistrarSimpleExitoso(): array
    {
        $usuarioId = $this->obtenerUsuarioIdValido();
        if ($usuarioId === null) {
            $this->markTestSkipped('No hay usuarios activos.');
            return [];
        }

        $sufijo = $this->sufijoAleatorio(8);
        $titulo = self::TITULO_PREFIX . $sufijo;

        $n = new Notificaciones();
        $n->set_titulo($titulo);
        $n->set_descripcion('Notificación de prueba automatizada');
        $n->set_usuario_id($usuarioId);

        try {
            $resultado = $n->realizar_consulta('registrar_simple');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return [];
        }

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('lastId', $resultado);
        $this->assertGreaterThan(0, (int) $resultado['lastId']);

        return [
            'id_notificacion' => (int) $resultado['lastId'],
            'usuario_id'      => $usuarioId,
            'titulo'          => $titulo,
        ];
    }

    #[Group('notificacion')]
    #[Group('consultar')]
    #[TestDox('Consultar: la notificación recién creada aparece en el listado del usuario')]
    #[Depends('testRegistrarSimpleExitoso')]
    #[Test]
    public function testConsultarNotificacionRecienCreada(array $data): void
    {
        if (empty($data['id_notificacion'])) {
            $this->markTestSkipped('La notificación test no se pudo crear.');
            return;
        }

        $n = new Notificaciones();
        $n->set_usuario_id($data['usuario_id']);

        try {
            $resultado = $n->realizar_consulta('consultar_mis_notificaciones');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($resultado['estatus']);

        $encontrada = false;
        foreach ($resultado['datos'] as $notif) {
            if ((int) $notif['id_notificacion'] === $data['id_notificacion']) {
                $encontrada = true;
                $this->assertSame($data['titulo'], $notif['titulo']);
                $this->assertSame(0, (int) $notif['leido']);
                break;
            }
        }
        $this->assertTrue($encontrada, 'La notificación test debe aparecer en el listado');
    }

    #[Group('notificacion')]
    #[Group('modificar')]
    #[TestDox('Marcar leída: Éxito sobre la notificación recién creada')]
    #[Depends('testRegistrarSimpleExitoso')]
    #[Test]
    public function testMarcarLeidaExitoso(array $data): void
    {
        if (empty($data['id_notificacion'])) {
            $this->markTestSkipped('La notificación test no se pudo crear.');
            return;
        }

        $n = new Notificaciones();
        $n->set_id_notificacion($data['id_notificacion']);

        try {
            $resultado = $n->realizar_consulta('marcar_leida');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($resultado['estatus']);
        $this->assertArrayHasKey('mensaje', $resultado);

        // Verificar que quedó leída
        $verif = new Notificaciones();
        $verif->set_usuario_id($data['usuario_id']);
        $listado = $verif->realizar_consulta('consultar_mis_notificaciones');
        foreach ($listado['datos'] as $notif) {
            if ((int) $notif['id_notificacion'] === $data['id_notificacion']) {
                $this->assertSame(1, (int) $notif['leido']);
                break;
            }
        }
    }

    #[Group('notificacion')]
    #[Group('modificar')]
    #[TestDox('Marcar leída: ID inexistente es idempotente (0 filas afectadas)')]
    #[Test]
    public function testMarcarLeidaInexistenteEsIdempotente(): void
    {
        $n = new Notificaciones();
        $n->set_id_notificacion(self::ID_INEXISTENTE);

        try {
            $resultado = $n->realizar_consulta('marcar_leida');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        // El modelo no valida existencia, solo UPDATE (0 filas)
        $this->assertTrue($resultado['estatus']);
    }

    #[Group('notificacion')]
    #[Group('modificar')]
    #[TestDox('Marcar todas leídas: usuario inexistente no hace daño (0 filas)')]
    #[Test]
    public function testMarcarTodasLeidasUsuarioInexistenteEsSeguro(): void
    {
        $n = new Notificaciones();
        $n->set_usuario_id(self::ID_INEXISTENTE);

        try {
            $resultado = $n->realizar_consulta('marcar_todas_leidas');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($resultado['estatus']);
    }

    /* =========================================================================
     *  HELPERS
     * ========================================================================= */

    private function sufijoAleatorio(int $largo = 8): string
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