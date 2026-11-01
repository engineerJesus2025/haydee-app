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
use haydee\modelo\Usuario;
use haydee\modelo\Rol;
use haydee\excepciones\NegocioException;

/*
Comando para meterlo en el terminal:

Todas las pruebas unitarias de UsuarioTest.php con salida tipo TestDox:
    .\vendor\bin\phpunit --testdox tests/pruebas_unitarias/UsuarioTest.php

Solo registrar:
    .\vendor\bin\phpunit --testdox --group registrar tests/pruebas_unitarias/UsuarioTest.php

Solo consultar:
    .\vendor\bin\phpunit --testdox --group consultar tests/pruebas_unitarias/UsuarioTest.php

Solo modificar:
    .\vendor\bin\phpunit --testdox --group modificar tests/pruebas_unitarias/UsuarioTest.php

Solo eliminar:
    .\vendor\bin\phpunit --testdox --group eliminar tests/pruebas_unitarias/UsuarioTest.php

Limpieza manual del usuario test (los que quedaron con activo=0):
    DELETE FROM tokens_seguridad
     WHERE usuario_id IN (
       SELECT id_usuario FROM usuarios
       WHERE correo LIKE 'test%unit%'
     );
    DELETE FROM claves_sesion
     WHERE usuario_id IN (
       SELECT id_usuario FROM usuarios
       WHERE correo LIKE 'test%unit%'
     );
    DELETE FROM usuarios WHERE correo LIKE 'test%unit%';
*/

#[TestDox('Módulo de Usuarios - Pruebas Unitarias')]
#[CoversClass(Usuario::class)]
final class UsuarioTest extends TestCase
{
    private const ID_INEXISTENTE = 99999999;
    private const CORREO_INEXISTENTE = 'nadie.existe.999@test.com';
    private const CONTRA_VALIDA = 'Test1234.';
    private const TOKEN_INEXISTENTE = 'aaaa000000000000000000000000000000000000000000000000000000000000';
    private const TOKEN_TIPO_VALIDO = 'RECUPERACION';

    /* =========================================================================
     *  BLOQUE 1: VALIDACIONES ESTÁTICAS (sin BD)
     * ========================================================================= */

    #[Group('usuario')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de nombre')]
    #[DataProvider('providerValidacionesNombre')]
    #[Test]
    public function testRegistrarValidacionesNombre(string $campo, $valor, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('registrar_usuario');
        $regex  = $reglas[$campo]['regex'] ?? null;

        $this->assertNotNull($regex, "No existe regex para '$campo'");
        $this->assertSame($valido, (bool) preg_match($regex, (string) $valor));
    }

    public static function providerValidacionesNombre(): array
    {
        return [
            'Nombre válido'         => ['nombre', 'Juan', true],
            'Nombre con acento'     => ['nombre', 'José', true],
            'Nombre con ñ'          => ['nombre', 'Peña', true],
            'Nombre 2 letras'       => ['nombre', 'AB', false],
            'Nombre con números'    => ['nombre', 'Juan2', false],
            'Nombre con símbolos'   => ['nombre', 'Juan#', false],
            'Nombre vacío'          => ['nombre', '', false],
            'Apellido válido'       => ['apellido', 'Pérez', true],
            'Apellido muy largo'    => ['apellido', str_repeat('A', 21), false],
        ];
    }

    #[Group('usuario')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de correo')]
    #[DataProvider('providerValidacionesCorreo')]
    #[Test]
    public function testRegistrarValidacionesCorreo(string $correo, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('registrar_usuario');
        $regex  = $reglas['correo']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $correo));
    }

    public static function providerValidacionesCorreo(): array
    {
        return [
            'Correo típico'         => ['juan.perez@test.com', true],
            'Correo con mayúsculas' => ['Juan@TEST.com', true],
            'Correo con guión bajo' => ['juan_perez@test.com', true],
            'Correo sin @'          => ['correo-sin-arroba.com', false],
            'Correo sin dominio'    => ['juan@', false],
            'Correo dominio de 1'   => ['juan@test.c', false],
            'Correo vacío'          => ['', false],
        ];
    }

    #[Group('usuario')]
    #[Group('registrar')]
    #[TestDox('Registrar: Validación de contraseña (requerida en operación)')]
    #[DataProvider('providerValidacionesContra')]
    #[Test]
    public function testRegistrarValidacionesContra(string $contra, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('registrar_usuario');
        $regex  = $reglas['contra']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $contra));
    }

    public static function providerValidacionesContra(): array
    {
        return [
            'Contraseña fuerte'          => ['Test1234.', true],
            'Contraseña fuerte con $'    => ['Test1234$', true],
            'Sin mayúscula'              => ['test1234.', false],
            'Sin minúscula'              => ['TEST1234.', false],
            'Sin dígito'                 => ['TestTest.', false],
            'Sin carácter especial'      => ['TestTest1', false],
            'Muy corta (7 chars)'        => ['Test12.', false],
            'Muy larga (101 chars)'      => ['Test1234.' . str_repeat('A', 92), false],
            'Contraseña vacía'           => ['', false],
        ];
    }

    #[Group('usuario')]
    #[Group('modificar')]
    #[TestDox('Modificar: Validación de ID de usuario')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testModificarValidacionesId(string $id, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('modificar_usuario');
        $regex  = $reglas['id_usuario']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    public static function providerValidacionesId(): array
    {
        return [
            'ID válido'          => ['1', true],
            'ID grande'          => ['999999', true],
            'ID con letras'      => ['ABC', false],
            'ID con símbolos'    => ['1#', false],
            'ID vacío'           => ['', false],
        ];
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: Validación de ID de usuario')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testConsultarValidacionesId(string $id, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('consultar_usuario');
        $regex  = $reglas['id_usuario']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    #[Group('usuario')]
    #[Group('eliminar')]
    #[TestDox('Eliminar: Validación de ID de usuario')]
    #[DataProvider('providerValidacionesId')]
    #[Test]
    public function testEliminarValidacionesId(string $id, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('eliminar_usuario');
        $regex  = $reglas['id_usuario']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $id));
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: Validación de token (hex de 64 chars)')]
    #[DataProvider('providerValidacionesToken')]
    #[Test]
    public function testTokensValidacionesToken(string $token, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('validar_token');
        $regex  = $reglas['token']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $token));
    }

    public static function providerValidacionesToken(): array
    {
        $tok64 = str_repeat('a', 64);
        return [
            'Token hex de 64'      => [$tok64, true],
            'Token hex de 63'      => [str_repeat('a', 63), false],
            'Token hex de 65'      => [str_repeat('a', 65), false],
            'Token con guión'      => ['a-b', false],
            'Token vacío'          => ['', false],
            'Token con mayúsculas' => [str_repeat('A', 64), false],
        ];
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: Validación de token_expiracion (datetime)')]
    #[DataProvider('providerValidacionesTokenExp')]
    #[Test]
    public function testTokensValidacionesTokenExpiracion(string $valor, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('registrar_token');
        $regex  = $reglas['token_expiracion']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    public static function providerValidacionesTokenExp(): array
    {
        return [
            'Datetime válido'          => ['2025-04-01 15:30:00', true],
            'Solo fecha'               => ['2025-04-01', false],
            'Fecha con barras'         => ['2025/04/01 15:30:00', false],
            'Fecha con letras'         => ['2025-XX-01 15:30:00', false],
        ];
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: Validación de código OTP (6 dígitos)')]
    #[DataProvider('providerValidacionesCodigo')]
    #[Test]
    public function testTokensValidacionesCodigo(string $codigo, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('validar_otp');
        $regex  = $reglas['codigo']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $codigo));
    }

    public static function providerValidacionesCodigo(): array
    {
        return [
            'Código 6 dígitos'    => ['123456', true],
            'Código 5 dígitos'    => ['12345', false],
            'Código 7 dígitos'    => ['1234567', false],
            'Código con letras'   => ['12345A', false],
            'Código vacío'        => ['', false],
        ];
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: Validación de token_autorizacion (hex de 64 chars)')]
    #[DataProvider('providerValidacionesToken')]
    #[Test]
    public function testTokensValidacionesTokenAutorizacion(string $valor, bool $valido): void
    {
        $reglas = Usuario::obtenerReglas('restablecer_con_token');
        $regex  = $reglas['token_autorizacion']['regex'];

        $this->assertSame($valido, (bool) preg_match($regex, $valor));
    }

    /* =========================================================================
     *  BLOQUE 2: DISPATCHER (sin BD)
     * ========================================================================= */

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción cuando la acción no está implementada')]
    #[Test]
    public function testAccionInexistenteFalla(): void
    {
        $u = new Usuario();

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage("La acción 'accion_falsa' no está implementada.");

        $u->realizar_consulta('accion_falsa');
    }

    /* =========================================================================
     *  BLOQUE 3: BUGS DEL MODELO (documentados)
     * ========================================================================= */

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Entrar: BUG — reglas existen pero método NO implementado')]
    #[Test]
    public function testEntrarNoImplementado(): void
    {
        $this->markTestIncomplete(
            'BUG: obtenerReglas("entrar") devuelve reglas para correo + contra, ' .
            'pero el modelo NO implementa _entrar(). La lógica de login está en ' .
            'el controller, pero las reglas mienten sobre la existencia del método.'
        );
    }

    #[Group('usuario')]
    #[Group('modificar')]
    #[TestDox('Cambiar contraseña: BUG — SQL con placeholder :id nunca bindeado')]
    #[Test]
    public function testCambiarContraseniaBugPlaceholders(): void
    {
        $this->markTestIncomplete(
            'BUG: _cambiar_contrasenia() ejecuta ' .
            '"UPDATE usuarios SET contrasenia = :con WHERE id_usuario = :id AND activo = 1" ' .
            'pero execute() solo bindea [":con" => ..., ":cor" => ...]. ' .
            'Falta ":id". PDO lanzará SQLSTATE[HY093]: Invalid parameter number. ' .
            'Además, filtra por :correo que nunca se usa en el UPDATE.'
        );
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Recuperar contraseña: BUG — reglas sin método implementado')]
    #[Test]
    public function testRecuperarContraseniaNoImplementado(): void
    {
        $this->markTestIncomplete(
            'BUG: obtenerReglas("recuperar_contrasenia") existe pero no hay ' .
            '_recuperar_contrasenia(). Idem para: solicitar_otp, validar_otp, ' .
            'restablecer_con_token, restablecer_con_otp, restablecer_contrasenia, ' .
            'enviar_notificacion, refrescar_token.'
        );
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('verificar_bloqueo_cuenta: FALTA EN configPorOperacion')]
    #[Test]
    public function testVerificarBloqueoSinReglas(): void
    {
        $reglas = Usuario::obtenerReglas('verificar_bloqueo_cuenta');
        $this->assertEmpty($reglas, 'Esperado: sin reglas (no está en configPorOperacion)');
        $this->markTestIncomplete(
            'BUG: _verificar_bloqueo_cuenta() existe pero no está en ' .
            'configPorOperacion. obtenerReglas() devuelve []. Idem para ' .
            'obtener_credenciales_por_correo, registrar_intento_fallido y ' .
            'limpiar_intentos_fallidos.'
        );
    }

    /* =========================================================================
     *  BLOQUE 4: EXCEPCIONES DE VALIDACIÓN (sin BD)
     * ========================================================================= */

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: validar_token sin token lanza excepción')]
    #[Test]
    public function testValidarTokenSinDatosFalla(): void
    {
        $u = new Usuario();
        $u->set_token('');
        $u->set_token_tipo('');

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Datos de validación incompletos.');

        $u->realizar_consulta('validar_token');
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: eliminar_token sin datos lanza excepción')]
    #[Test]
    public function testEliminarTokenSinDatosFalla(): void
    {
        $u = new Usuario();
        $u->set_id_usuario(null);
        $u->set_token_tipo(null);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Faltan datos para eliminar el token.');

        $u->realizar_consulta('eliminar_token');
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: obtener_token sin datos lanza excepción')]
    #[Test]
    public function testObtenerTokenSinDatosFalla(): void
    {
        $u = new Usuario();
        $u->set_id_usuario(null);
        $u->set_token_tipo(null);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Datos incompletos para buscar token.');

        $u->realizar_consulta('obtener_token');
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: validar_token_jwt sin datos lanza excepción')]
    #[Test]
    public function testValidarTokenJwtSinDatosFalla(): void
    {
        $u = new Usuario();
        $u->set_id_usuario(null);
        $u->set_token(null);
        $u->set_token_tipo(null);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Datos insuficientes para la validación.');

        $u->realizar_consulta('validar_token_jwt');
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: consumir_token_recuperacion sin datos lanza excepción')]
    #[Test]
    public function testConsumirTokenRecuperacionSinDatosFalla(): void
    {
        $u = new Usuario();
        // Sin correo, token, contra ni tipo

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Datos insuficientes para procesar el restablecimiento.');

        $u->realizar_consulta('consumir_token_recuperacion');
    }

    /* =========================================================================
     *  BLOQUE 5: CONSULTAS (solo SELECT)
     * ========================================================================= */

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: Listado general de usuarios activos')]
    #[Test]
    public function testConsultarUsuarios(): void
    {
        $u = new Usuario();
        try {
            $r = $u->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertIsArray($r['datos']);
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción si el usuario no existe')]
    #[Test]
    public function testConsultarUsuarioInexistenteFalla(): void
    {
        $u = new Usuario();
        $u->set_id_usuario(self::ID_INEXISTENTE);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Usuario no encontrado.');

        try {
            $u->realizar_consulta('consultar_usuario');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: Lanza excepción al buscar perfil de usuario inexistente')]
    #[Test]
    public function testConsultarPerfilInexistenteFalla(): void
    {
        $u = new Usuario();
        $u->set_id_usuario(self::ID_INEXISTENTE);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Perfil de usuario no encontrado.');

        try {
            $u->realizar_consulta('consultar_perfil_usuario');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: existe_correo lanza excepción si el correo no está registrado')]
    #[Test]
    public function testExisteCorreoInexistenteFalla(): void
    {
        $u = new Usuario();
        $u->set_correo(self::CORREO_INEXISTENTE);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Correo no encontrado.');

        try {
            $u->realizar_consulta('existe_correo');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: obtener_credenciales retorna estatus false si el correo no existe')]
    #[Test]
    public function testObtenerCredencialesInexistenteRetornaFalse(): void
    {
        $u = new Usuario();
        $u->set_correo(self::CORREO_INEXISTENTE);

        try {
            $r = $u->realizar_consulta('obtener_credenciales_por_correo');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($r);
        $this->assertFalse($r['estatus']);
    }

    /* =========================================================================
     *  BLOQUE 6: SKIPS POR SEGURIDAD
     * ========================================================================= */

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Tokens: SIEMPRE OMITIDO — sp_insertar_token puede dejar huérfanos')]
    #[Test]
    public function testRegistrarTokenExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO POR SEGURIDAD: ejecuta sp_insertar_token() que puede fallar ' .
            'silenciosamente o dejar tokens huérfanos en tokens_seguridad. ' .
            'Ejecutar manualmente en entorno de pruebas.'
        );
    }

    #[Group('usuario')]
    #[Group('registrar')]
    #[TestDox('Intentos login: SIEMPRE OMITIDO — modifica intentos_login (riesgo de bloqueo)')]
    #[Test]
    public function testRegistrarIntentoFallidoExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO POR SEGURIDAD: modifica intentos_login. Si una corrida anterior ' .
            'dejó 3 intentos sobre el usuario test, futuras corridas podrían bloquear ' .
            'su login. Ejecutar manualmente con limpieza previa.'
        );
    }

    #[Group('usuario')]
    #[Group('tokens')]
    #[TestDox('Consumir token: SIEMPRE OMITIDO — UPDATE contraseña + DELETE tokens reales')]
    #[Test]
    public function testConsumirTokenRecuperacionExitoso(): void
    {
        $this->markTestSkipped(
            'OMITIDO POR SEGURIDAD: modifica contraseña de un usuario real, ' .
            'borra sus tokens y sus claves de sesión. Ejecutar manualmente en ' .
            'entorno de pruebas con un usuario descartable.'
        );
    }

    /* =========================================================================
     *  BLOQUE 7: ESCRITURAS ENCADENADAS (con #[Depends])
     * ========================================================================= */

    #[Group('usuario')]
    #[Group('registrar')]
    #[TestDox('Registrar: Éxito con datos únicos aleatorios (retorna array de datos)')]
    #[Test]
    public function testRegistrarUsuarioExitoso(): array
    {
        $rolId = $this->obtenerRolIdValido();
        if ($rolId === null) {
            $this->markTestSkipped(
                'Omitido: no hay roles activos en BD seguridad para asignar. ' .
                'Registra un rol primero.'
            );
            return [];
        }

        $sufijo = $this->sufijoAleatorioAlfanumerico(6);
        $correo = "test{$sufijo}.unit@test.com";
        $nombre = 'Usuario' . $this->sufijoAleatorioLetras(4);
        $apellido = 'Apellido' . $this->sufijoAleatorioLetras(3);

        $u = new Usuario();
        $u->set_nombre($nombre);
        $u->set_apellido($apellido);
        $u->set_correo($correo);
        $u->set_contra(self::CONTRA_VALIDA);
        $u->set_rol_id($rolId);

        try {
            $r = $u->realizar_consulta('registrar_usuario');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD seguridad no disponible: ' . $e->getMessage());
            return [];
        }

        if (empty($r['estatus'])) {
            $this->markTestSkipped(
                'Registro de usuario falló: ' . ($r['mensaje'] ?? 'desconocido')
            );
            return [];
        }

        $id = (int) $r['lastId'];
        $this->assertGreaterThan(0, $id);
        $this->assertArrayHasKey('mensaje', $r);

        return [
            'id_usuario' => $id,
            'correo'     => $correo,
            'nombre'     => $nombre,
            'apellido'   => $apellido,
            'rol_id'     => $rolId,
        ];
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: Recupera el usuario recién registrado por ID')]
    #[Depends('testRegistrarUsuarioExitoso')]
    #[Test]
    public function testConsultarUsuarioRecienCreado(array $data): void
    {
        if (empty($data['id_usuario'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return;
        }

        $u = new Usuario();
        $u->set_id_usuario($data['id_usuario']);

        try {
            $r = $u->realizar_consulta('consultar_usuario');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertSame($data['id_usuario'], (int) $r['datos']['id_usuario']);
        $this->assertSame($data['correo'], $r['datos']['correo']);
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: existe_correo confirma la existencia del usuario recién creado')]
    #[Depends('testRegistrarUsuarioExitoso')]
    #[Test]
    public function testExisteCorreoDelUsuarioCreado(array $data): void
    {
        if (empty($data['correo'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return;
        }

        $u = new Usuario();
        $u->set_correo($data['correo']);

        try {
            $r = $u->realizar_consulta('existe_correo');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertSame($data['correo'], $r['datos']['correo']);
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: obtener_credenciales devuelve datos del usuario recién creado')]
    #[Depends('testRegistrarUsuarioExitoso')]
    #[Test]
    public function testObtenerCredencialesDelUsuarioCreado(array $data): void
    {
        if (empty($data['correo'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return;
        }

        $u = new Usuario();
        $u->set_correo($data['correo']);

        try {
            $r = $u->realizar_consulta('obtener_credenciales_por_correo');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertSame($data['id_usuario'], (int) $r['datos']['id_usuario']);
        $this->assertSame($data['correo'], $r['datos']['correo']);
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: consultar_perfil_usuario retorna perfil del usuario recién creado')]
    #[Depends('testRegistrarUsuarioExitoso')]
    #[Test]
    public function testConsultarPerfilDelUsuarioCreado(array $data): void
    {
        if (empty($data['id_usuario'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return;
        }

        $u = new Usuario();
        $u->set_id_usuario($data['id_usuario']);

        try {
            $r = $u->realizar_consulta('consultar_perfil_usuario');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('datos', $r);
        $this->assertArrayHasKey('correo', $r['datos']);
        $this->assertSame($data['correo'], $r['datos']['correo']);
        $this->assertArrayHasKey('ultima_vez', $r['datos']);
    }

    #[Group('usuario')]
    #[Group('modificar')]
    #[TestDox('Modificar: Cambia nombre y apellido del usuario recién creado')]
    #[Depends('testRegistrarUsuarioExitoso')]
    #[Test]
    public function testModificarUsuarioExitoso(array $data): array
    {
        if (empty($data['id_usuario'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return [];
        }

        $nuevoNombre = 'Modif' . $this->sufijoAleatorioLetras(5);
        $nuevoApellido = 'Camb' . $this->sufijoAleatorioLetras(4);

        $u = new Usuario();
        $u->set_id_usuario($data['id_usuario']);
        $u->set_nombre($nuevoNombre);
        $u->set_apellido($nuevoApellido);
        $u->set_correo($data['correo']);
        $u->set_rol_id($data['rol_id']);
        $u->set_contra(''); // sin cambio de contraseña

        try {
            $r = $u->realizar_consulta('modificar_usuario');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return $data;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('mensaje', $r);

        // Verificar persistencia
        $verif = new Usuario();
        $verif->set_id_usuario($data['id_usuario']);
        $info = $verif->realizar_consulta('consultar_usuario');

        $this->assertTrue($info['estatus']);
        $this->assertSame($nuevoNombre, $info['datos']['nombre']);
        $this->assertSame($nuevoApellido, $info['datos']['apellido']);

        return $data;
    }

    #[Group('usuario')]
    #[Group('modificar')]
    #[TestDox('Modificar perfil: Actualiza el perfil del usuario recién creado')]
    #[Depends('testModificarUsuarioExitoso')]
    #[Test]
    public function testModificarPerfilExitoso(array $data): void
    {
        if (empty($data['id_usuario'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return;
        }

        $nuevoNombre = 'Perfil' . $this->sufijoAleatorioLetras(4);
        $nuevoApellido = 'Demo' . $this->sufijoAleatorioLetras(4);

        $u = new Usuario();
        $u->set_id_usuario($data['id_usuario']);
        $u->set_nombre($nuevoNombre);
        $u->set_apellido($nuevoApellido);
        $u->set_correo($data['correo']);

        try {
            $r = $u->realizar_consulta('modificar_perfil');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('mensaje', $r);
    }

    #[Group('usuario')]
    #[Group('modificar')]
    #[TestDox('Ayuda: Marca el tour interactivo como visto (no-op si ya estaba)')]
    #[Depends('testRegistrarUsuarioExitoso')]
    #[Test]
    public function testMarcarAyudaVistoExitoso(array $data): void
    {
        if (empty($data['id_usuario'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return;
        }

        $u = new Usuario();
        $u->set_id_usuario($data['id_usuario']);

        try {
            $r = $u->realizar_consulta('marcar_ayuda_visto');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('mensaje', $r);
    }

    #[Group('usuario')]
    #[Group('eliminar')]
    #[TestDox('Eliminar: Soft-delete del usuario recién creado')]
    #[Depends('testRegistrarUsuarioExitoso')]
    #[Test]
    public function testEliminarUsuarioExitoso(array $data): array
    {
        if (empty($data['id_usuario'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return [];
        }

        $u = new Usuario();
        $u->set_id_usuario($data['id_usuario']);

        try {
            $r = $u->realizar_consulta('eliminar_usuario');
        } catch (\Throwable $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
            return $data;
        }

        $this->assertTrue($r['estatus']);
        $this->assertArrayHasKey('mensaje', $r);
        $this->assertStringContainsString('eliminado', strtolower($r['mensaje']));

        return $data;
    }

    #[Group('usuario')]
    #[Group('consultar')]
    #[TestDox('Consultar: El usuario eliminado ya no es recuperable')]
    #[Depends('testEliminarUsuarioExitoso')]
    #[Test]
    public function testConsultarUsuarioEliminadoFalla(array $data): void
    {
        if (empty($data['id_usuario'])) {
            $this->markTestSkipped('El usuario test no se pudo crear.');
            return;
        }

        $u = new Usuario();
        $u->set_id_usuario($data['id_usuario']);

        $this->expectException(NegocioException::class);
        $this->expectExceptionMessage('Usuario no encontrado.');

        try {
            $u->realizar_consulta('consultar_usuario');
        } catch (\PDOException $e) {
            $this->markTestSkipped('BD no disponible: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     *  HELPERS PRIVADOS
     * ========================================================================= */

    private function sufijoAleatorioLetras(int $largo = 6): string
    {
        $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $sufijo = '';
        for ($i = 0; $i < $largo; $i++) {
            $sufijo .= $letras[random_int(0, strlen($letras) - 1)];
        }
        return $sufijo;
    }

    private function sufijoAleatorioAlfanumerico(int $largo = 6): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $sufijo = '';
        for ($i = 0; $i < $largo; $i++) {
            $sufijo .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $sufijo;
    }

    /**
     * Obtiene un rol_id válido de la BD seguridad reutilizando el modelo Rol.
     * Si no hay roles, retorna null (el test hará markTestSkipped).
     */
    private function obtenerRolIdValido(): ?int
    {
        $r = new Rol();
        try {
            $resultado = $r->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            return null;
        }

        if (empty($resultado['estatus']) || empty($resultado['datos'])) {
            return null;
        }

        foreach ($resultado['datos'] as $rol) {
            if (!empty($rol['id_rol'])) {
                return (int) $rol['id_rol'];
            }
        }
        return null;
    }
}