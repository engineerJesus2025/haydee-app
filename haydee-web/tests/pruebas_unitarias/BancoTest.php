<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Test;
use haydee\modelo\Banco;
//require_once 'vendor/autoload.php';

// para usarlo en el cmd: vendor\bin\phpunit --testdox tests\pruebas_unitarias\BancoTest.php

// para usarlo en el cmd de vs: E:\Programas\xampp\php\php.exe vendor\bin\phpunit --testdox tests\pruebas_unitarias\BancoTest.php

/**
 * Pruebas para el modelo Banco (archivo: modelo/banco_modelo.php)
 */
final class BancoTest extends TestCase
{
    protected Banco $banco;

    protected function setUp(): void
    {
        $this->banco = new Banco();
    }

    // #[TestDox('Registro Correctamente un banco con datos válidos')]
    // #[Test]
    // public function testRegistroBancoExitoso()
    // {
    //     $this->banco->set_nombre_banco('BancoTest');
    //     $this->banco->set_codigo('1234');
    //     $this->banco->set_numero_cuenta('123456789012345678');
    //     $this->banco->set_telefono_afiliado('04141234567');
    //     $this->banco->set_cedula_afiliada('12345678');

    //     $resultado = $this->banco->realizar_consulta('registrar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertTrue($resultado['estatus'], 'El registro debe retornar estatus true');
    // }

    // #[TestDox('Modifica correctamente un banco existente')]
    // #[Test]
    // public function testModificarBancoExitoso()
    // {
    //     // Primero registra un banco para obtener un id válido
    //     $this->banco->set_nombre_banco('BancoMod');
    //     $this->banco->set_codigo('5678');
    //     $this->banco->set_numero_cuenta('876543210987654321');
    //     $this->banco->set_telefono_afiliado('04141234567');
    //     $this->banco->set_cedula_afiliada('87654321');
    //     $resRegistro = $this->banco->realizar_consulta('registrar');
    //     $this->assertTrue($resRegistro['estatus'], 'El registro debe retornar estatus true');

    //     // Obtiene el último id insertado
    //     $last = $this->banco->realizar_consulta('lastId');
    //     $nuevoId = intval($last['last_id']);
    //     $this->assertGreaterThan(0, $nuevoId);

    //     // Modifica el banco
    //     $this->banco->set_id_banco($nuevoId);
    //     $this->banco->set_nombre_banco('BancoModificado');
    //     $resModificar = $this->banco->realizar_consulta('modificar');

    //     $this->assertIsArray($resModificar);
    //     $this->assertTrue($resModificar['estatus'], 'La modificación debe retornar estatus true');
    // }

    #[TestDox('Elimina correctamente un banco existente')]
    #[Test]
    public function testEliminarBancoExitoso()
    {
        // Primero registra un banco para obtener un id válido
        $this->banco->set_nombre_banco('BancoElim');
        $this->banco->set_codigo('4321');
        $this->banco->set_numero_cuenta('123456789012345679');
        $this->banco->set_telefono_afiliado('04141234568');
        $this->banco->set_cedula_afiliada('12345679');
        $resRegistro = $this->banco->realizar_consulta('registrar');
        $this->assertTrue($resRegistro['estatus'], 'El registro debe retornar estatus true');

        // Obtiene el último id insertado
        $last = $this->banco->realizar_consulta('lastId');
        $nuevoId = intval($last['last_id']);
        $this->assertGreaterThan(0, $nuevoId);

        // Elimina el banco
        $this->banco->set_id_banco($nuevoId);
        $resEliminar = $this->banco->realizar_consulta('eliminar');

        $this->assertIsArray($resEliminar);
        $this->assertTrue($resEliminar['estatus'], 'La eliminación debe retornar estatus true');
    }

    // #[TestDox('Comprueba getters y setters del modelo Banco con distintos datos')]
    // #[DataProvider('providerSettersGetters')]
    // #[Test]
    // public function testSettersGetters($id, $nombre, $codigo, $numero_cuenta, $telefono, $cedula)
    // {
    //     $this->banco->set_id_banco($id);
    //     $this->banco->set_nombre_banco($nombre);
    //     $this->banco->set_codigo($codigo);
    //     $this->banco->set_numero_cuenta($numero_cuenta);
    //     $this->banco->set_telefono_afiliado($telefono);
    //     $this->banco->set_cedula_afiliada($cedula);

    //     $this->assertSame($id, $this->banco->get_id_banco());
    //     $this->assertSame($nombre, $this->banco->get_nombre_banco());
    //     $this->assertSame($codigo, $this->banco->get_codigo());
    //     $this->assertSame($numero_cuenta, $this->banco->get_numero_cuenta());
    //     $this->assertSame($telefono, $this->banco->get_telefono_afiliado());
    //     $this->assertSame($cedula, $this->banco->get_cedula_afiliada());
    // }

    // public static function providerSettersGetters(): array
    // {
    //     return [
    //         'banco_valido_1' => [1, 'BancoPrueba', '1234', '123456789012345678', '04141234567', '12345678'],
    //         'banco_valido_2' => [2, 'OtroBanco', '0001', '987654321098765432', '04141230000', '87654321'],
    //     ];
    // }

    // #[TestDox('Prueba de ingreso de datos (validaciones back-end)')]
    // #[DataProvider('providerDatosInvalido')]
    // #[Test]
    // public function testValidacionesDelBackend(array $input, string $expectedMessageFragment)
    // {
    //     // asignar campos desde el array
    //     if (isset($input['nombre_banco']))
    //         $this->banco->set_nombre_banco($input['nombre_banco']);
    //     if (isset($input['codigo']))
    //         $this->banco->set_codigo($input['codigo']);
    //     if (isset($input['numero_cuenta']))
    //         $this->banco->set_numero_cuenta($input['numero_cuenta']);
    //     if (isset($input['telefono_afiliado']))
    //         $this->banco->set_telefono_afiliado($input['telefono_afiliado']);
    //     if (isset($input['cedula_afiliada']))
    //         $this->banco->set_cedula_afiliada($input['cedula_afiliada']);

    //     $resultado = $this->banco->realizar_consulta('registrar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertFalse($resultado['estatus']);
    //     $this->assertArrayHasKey('mensaje', $resultado);
    //     $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    // }

    // public static function providerDatosInvalido(): array
    // {
    //     return [
    //         'Nombre Invalido' => [
    //             ['nombre_banco' => 'AB', 'codigo' => '1234', 'numero_cuenta' => '123456789012345678', 'telefono_afiliado' => '04141234567', 'cedula_afiliada' => '1234567'],
    //             "Nombre del Banco"
    //         ],
    //         'Codigo Invalido' => [
    //             ['nombre_banco' => 'BancoValido', 'codigo' => '12', 'numero_cuenta' => '123456789012345678', 'telefono_afiliado' => '04141234567', 'cedula_afiliada' => '1234567'],
    //             "Codigo"
    //         ],
    //         'Numero de Cuenta Invalido' => [
    //             ['nombre_banco' => 'BancoValido', 'codigo' => '1234', 'numero_cuenta' => 'abc', 'telefono_afiliado' => '04141234567', 'cedula_afiliada' => '1234567'],
    //             "Numero de Cuenta"
    //         ],
    //         'Telefono Invalido' => [
    //             ['nombre_banco' => 'BancoValido', 'codigo' => '1234', 'numero_cuenta' => '123456789012345678', 'telefono_afiliado' => '0414', 'cedula_afiliada' => '1234567'],
    //             "Telefono Afiliado"
    //         ],
    //         'Cedula Invalida' => [
    //             ['nombre_banco' => 'BancoValido', 'codigo' => '1234', 'numero_cuenta' => '123456789012345678', 'telefono_afiliado' => '04141234567', 'cedula_afiliada' => '12'],
    //             "Cedula Afiliada"
    //         ],
    //         'Campos Vacios' => [
    //             ['nombre_banco' => '', 'codigo' => '', 'numero_cuenta' => '', 'telefono_afiliado' => '', 'cedula_afiliada' => ''],
    //             "Uno o varios de los campos requeridos estan vacios"
    //         ],
    //     ];
    // }

    // #[TestDox('Prueba de ingreso de datos inválidos al modificar un banco (validaciones back-end)')] // Modificar Validaciones
    // #[DataProvider('providerDatosInvalidosModificar')]
    // #[Test]
    // public function testValidacionesDelBackendModificar(array $input, string $expectedMessageFragment)
    // {
    //     // Asignar campos desde el array de entrada
    //     if (isset($input['id_banco']))
    //         $this->banco->set_id_banco($input['id_banco']);
    //     if (isset($input['nombre_banco']))
    //         $this->banco->set_nombre_banco($input['nombre_banco']);
    //     if (isset($input['codigo']))
    //         $this->banco->set_codigo($input['codigo']);
    //     if (isset($input['numero_cuenta']))
    //         $this->banco->set_numero_cuenta($input['numero_cuenta']);
    //     if (isset($input['telefono_afiliado']))
    //         $this->banco->set_telefono_afiliado($input['telefono_afiliado']);
    //     if (isset($input['cedula_afiliada']))
    //         $this->banco->set_cedula_afiliada($input['cedula_afiliada']);

    //     // Ejecutar la acción de modificación
    //     $resultado = $this->banco->realizar_consulta('modificar');

    //     // Validar la estructura de la respuesta
    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertFalse($resultado['estatus'], 'Debe retornar estatus false para datos inválidos');
    //     $this->assertArrayHasKey('mensaje', $resultado);
    //     $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    // }

    // public static function providerDatosInvalidosModificar(): array
    // {
    //     return [
    //         'Nombre Invalido' => [
    //             [
    //                 'id_banco' => 1,
    //                 'nombre_banco' => 'AB',
    //                 'codigo' => '1234',
    //                 'numero_cuenta' => '123456789012345678',
    //                 'telefono_afiliado' => '04141234567',
    //                 'cedula_afiliada' => '12345678'
    //             ],
    //             "Nombre del Banco"
    //         ],
    //         'Codigo Invalido' => [
    //             [
    //                 'id_banco' => 1,
    //                 'nombre_banco' => 'Banco Editado',
    //                 'codigo' => '12',
    //                 'numero_cuenta' => '123456789012345678',
    //                 'telefono_afiliado' => '04141234567',
    //                 'cedula_afiliada' => '12345678'
    //             ],
    //             "Codigo"
    //         ],
    //         'Numero de Cuenta Invalido' => [
    //             [
    //                 'id_banco' => 1,
    //                 'nombre_banco' => 'Banco Editado',
    //                 'codigo' => '1234',
    //                 'numero_cuenta' => 'abc',
    //                 'telefono_afiliado' => '04141234567',
    //                 'cedula_afiliada' => '12345678'
    //             ],
    //             "Numero de Cuenta"
    //         ],
    //         'Telefono Invalido' => [
    //             [
    //                 'id_banco' => 1,
    //                 'nombre_banco' => 'Banco Editado',
    //                 'codigo' => '1234',
    //                 'numero_cuenta' => '123456789012345678',
    //                 'telefono_afiliado' => '0414',
    //                 'cedula_afiliada' => '12345678'
    //             ],
    //             "Telefono Afiliado"
    //         ],
    //         'Cedula Invalida' => [
    //             [
    //                 'id_banco' => 1,
    //                 'nombre_banco' => 'Banco Editado',
    //                 'codigo' => '1234',
    //                 'numero_cuenta' => '123456789012345678',
    //                 'telefono_afiliado' => '04141234567',
    //                 'cedula_afiliada' => '12'
    //             ],
    //             "Cedula Afiliada"
    //         ],
    //         'Campos Vacíos' => [
    //             [
    //                 'id_banco' => 1,
    //                 'nombre_banco' => '',
    //                 'codigo' => '',
    //                 'numero_cuenta' => '',
    //                 'telefono_afiliado' => '',
    //                 'cedula_afiliada' => ''
    //             ],
    //             "Uno o varios de los campos requeridos estan vacios"
    //         ],
    //     ];
    // }

    #[TestDox('Prueba de eliminación de banco (validaciones back-end)')] // Eliminar Validaciones
    #[DataProvider('providerEliminarInvalido')]
    #[Test]
    public function testValidacionesEliminarBanco(array $input, string $expectedMessageFragment)
    {
        // Asignar el campo según los datos de entrada
        if (isset($input['id_banco']))
            $this->banco->set_id_banco($input['id_banco']);

        // Ejecutar la validación del método eliminar
        $resultado = $this->banco->realizar_consulta('eliminar');

        // Afirmaciones
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertArrayHasKey('mensaje', $resultado);

        // Como son pruebas de validaciones inválidas, debe dar falso
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    }

    public static function providerEliminarInvalido(): array
    {
        return [
            'ID no recibido' => [
                [],
                "id del Banco requerido no se recibio correctamente"
            ],
            'ID vacío' => [
                ['id_banco' => ''],
                "id del Banco requerido esta vacio"
            ],
            'ID no numérico' => [
                ['id_banco' => 'ABC'],
                "id del Banco debe ser un valor numerico entero"
            ],
            'ID inexistente' => [
                ['id_banco' => 999999],
                "banco seleccionado no existe"
            ],
        ];
    }

    // #[TestDox('Validar existencia de banco por número de cuenta')] // Validar
    // #[DataProvider('providerValidarBanco')]
    // #[Test]
    // public function testValidarBanco(array $input, bool $expectedExists)
    // {
    //     $this->banco->set_numero_cuenta($input['numero_cuenta']);

    //     try {
    //         $res = $this->banco->realizar_consulta('validar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }

    //     $this->assertIsArray($res);
    //     if ($expectedExists) {
    //         $this->assertTrue($res['estatus']);
    //         $this->assertEquals('numero_cuenta', $res['busqueda']);
    //     } else {
    //         $this->assertFalse($res['estatus']);
    //     }
    // }

    // public static function providerValidarBanco(): array
    // {
    //     return [
    //         'Banco Existente' => [['numero_cuenta' => '123456789012345678'], true],
    //         'Banco NO Existente' => [['numero_cuenta' => '000000000000000000'], false],
    //     ];
    // }

    // #[TestDox('Consultar todos los bancos')]
    // #[Test]

    // public function testConsultar()
    // {
    //     try {
    //         $res = $this->banco->realizar_consulta('consultar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }

    //     $this->assertIsArray($res);
    // }

    // #[TestDox('Consultar banco por id')]
    // #[DataProvider('providerConsultaEspecifica')]
    // #[Test]
    // public function testConsultaEspecificaSkippable($id)
    // {
    //     $this->banco->set_id_banco($id);

    //     try {
    //         $res = $this->banco->realizar_consulta('consulta_especifica');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }

    //     $this->assertIsArray($res);
    // }

    // public static function providerConsultaEspecifica(): array
    // {
    //     return [
    //         'ID Existente' => [1],
    //         'ID Inexistente' => [9999999],
    //     ];
    // }

    // #[TestDox('lastId devuelve el último id (skippable)')]
    // #[Test]
    // public function testLastIdSkippable()
    // {
    //     try {
    //         $res = $this->banco->realizar_consulta('lastId');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }

    //     $this->assertIsArray($res);
    //     $this->assertArrayHasKey('last_id', $res);
    // }

    // #[TestDox('Flujo registrar -> modificar -> eliminar (skippable si no hay BD)')]
    // #[DataProvider('providerRegistrarFlow')]
    // #[Test]

    // public function testRegistrarModificarEliminarFlow(array $input)
    // {
    //     $this->banco->set_nombre_banco($input['nombre_banco']);
    //     $this->banco->set_codigo($input['codigo']);
    //     $this->banco->set_numero_cuenta($input['numero_cuenta']);
    //     $this->banco->set_telefono_afiliado($input['telefono_afiliado']);
    //     $this->banco->set_cedula_afiliada($input['cedula_afiliada']);

    //     // Registrar
    //     try {
    //         $resRegistrar = $this->banco->realizar_consulta('registrar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }
    //     $this->assertIsArray($resRegistrar);
    //     $this->assertTrue($resRegistrar['estatus'] ?? false, 'El registro debe retornar estatus true');

    //     // Obtener last id
    //     try {
    //         $last = $this->banco->realizar_consulta('lastId');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD al obtener lastId: ' . $e->getMessage());
    //         return;
    //     }
    //     $this->assertArrayHasKey('last_id', $last);
    //     $nuevoId = intval($last['last_id']);
    //     $this->assertGreaterThan(0, $nuevoId);

    //     // Modificar
    //     $this->banco->set_id_banco($nuevoId);
    //     $this->banco->set_nombre_banco($input['nombre_banco'] . ' Mod');
    //     try {
    //         $resModificar = $this->banco->realizar_consulta('modificar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD al modificar: ' . $e->getMessage());
    //         return;
    //     }
    //     $this->assertIsArray($resModificar);
    //     $this->assertTrue($resModificar['estatus'] ?? false, 'La modificación debe retornar estatus true');

    //     // Eliminar
    //     $this->banco->set_id_banco($nuevoId);
    //     try {
    //         $resEliminar = $this->banco->realizar_consulta('eliminar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD al eliminar: ' . $e->getMessage());
    //         return;
    //     }
    //     $this->assertIsArray($resEliminar);
    //     $this->assertTrue($resEliminar['estatus'] ?? false, 'La eliminación debe retornar estatus true');
    // }

    // public static function providerRegistrarFlow(): array
    // {
    //     $uniq = substr((string)microtime(true), -6);
    //     return [
    //         'flujo_completo_1' => [[
    //             'nombre_banco' => 'BancoTest',
    //             'codigo' => (1000 + (int)substr($uniq,0,3)),
    //             'numero_cuenta' => str_pad((string)rand(100000000000000000,999999999999999999), 18, '0', STR_PAD_LEFT),
    //             'telefono_afiliado' => '0414' . substr($uniq,0,7),
    //             'cedula_afiliada' => substr($uniq,0,8),
    //         ]],
    //     ];
    // }

    // public static function providerRegistrarFlow(): array
    // {
    //     $uniq = substr((string)microtime(true), -6);
    //     return [
    //         'flujo_completo_1' => [[
    //             'nombre_banco' => 'BancoTestDos',
    //             'codigo' => "4423",
    //             'numero_cuenta' => "423456789012345678",
    //             'telefono_afiliado' => '04147721822',
    //             'cedula_afiliada' => "30353397",
    //         ]],
    //     ];
    // }
}
