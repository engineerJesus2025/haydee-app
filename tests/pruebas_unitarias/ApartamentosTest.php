<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Test;
use haydee\modelo\Apartamento;

final class ApartamentosTest extends TestCase
{
    protected Apartamento $apartamento;

    protected function setUp(): void
    {
        $this->apartamento = new Apartamento();
    }

    // #[TestDox('Registro Correctamente un apartamento con datos válidos')]
    // #[Test]
    // public function testRegistroApartamentoExitoso()
    // {
    //     $this->apartamento->set_nro_apartamento('3-3');
    //     $this->apartamento->set_porcentaje_participacion('5.25');
    //     $this->apartamento->set_gas('1');
    //     $this->apartamento->set_agua('1');
    //     $this->apartamento->set_alquilado('1');

    //     $resultado = $this->apartamento->realizar_consulta('registrar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertTrue($resultado['estatus'], 'El registro debe retornar estatus true');
    // }

    #[TestDox('Prueba de ingreso de datos (validaciones back-end de Apartamentos)')]
    #[DataProvider('providerDatosInvalidoApartamento')]
    #[Test]
    public function testValidacionesDelBackendApartamento(array $input, string $expectedMessageFragment)
    {
        // asignar campos desde el array
        if (isset($input['nro_apartamento']))
            $this->apartamento->set_nro_apartamento($input['nro_apartamento']);
        if (isset($input['porcentaje_participacion']))
            $this->apartamento->set_porcentaje_participacion($input['porcentaje_participacion']);
        if (isset($input['gas']))
            $this->apartamento->set_gas($input['gas']);
        if (isset($input['agua']))
            $this->apartamento->set_agua($input['agua']);
        if (isset($input['alquilado']))
            $this->apartamento->set_alquilado($input['alquilado']);

        // Ejecutar la validación (caso registrar)
        $resultado = $this->apartamento->realizar_consulta('registrar');

        // Verificar estructura del resultado
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertArrayHasKey('mensaje', $resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    }

    public static function providerDatosInvalidoApartamento(): array
    {
        return [
            'Nro de Apartamento Invalido' => [
                ['nro_apartamento' => 'A-10', 'porcentaje_participacion' => '10.5', 'gas' => 1, 'agua' => 1, 'alquilado' => 1],
                "Nro de Apartamento"
            ],
            'Porcentaje de Participacion Invalido' => [
                ['nro_apartamento' => '4-4', 'porcentaje_participacion' => 'abc', 'gas' => 1, 'agua' => 1, 'alquilado' => 1],
                "Porcentaje de Participación"
            ],
            'Gas Invalido' => [
                ['nro_apartamento' => '4-4', 'porcentaje_participacion' => '10.5', 'gas' => 'abc', 'agua' => 1, 'alquilado' => 1],
                "Gas"
            ],
            'Agua Invalido' => [
                ['nro_apartamento' => '4-4', 'porcentaje_participacion' => '10.5', 'gas' => 1, 'agua' => 'abc', 'alquilado' => 1],
                "Agua"
            ],
            'Alquilado Invalido' => [
                ['nro_apartamento' => '4-4', 'porcentaje_participacion' => '10.5', 'gas' => 1, 'agua' => 1, 'alquilado' => 'no'],
                "Alquilado"
            ],
            'Campos Vacios' => [
                ['nro_apartamento' => '', 'porcentaje_participacion' => '', 'gas' => '', 'agua' => '', 'alquilado' => ''],
                "Uno o varios de los campos requeridos estan vacios"
            ],
        ];
    }

    // #[TestDox('Validar existencia de Apartamento por Nro de Apartamento')] // Validar
    // #[DataProvider('providerValidarApartamento')]
    // #[Test]
    // public function testValidarApartamento(array $input, bool $expectedExists)
    // {
    //     $this->apartamento->set_nro_apartamento($input['nro_apartamento']);

    //     try {
    //         $res = $this->apartamento->realizar_consulta('validar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }

    //     $this->assertIsArray($res);
    //     if ($expectedExists) {
    //         $this->assertTrue($res['estatus']);
    //         $this->assertEquals('nro_apartamento', $res['busqueda']);
    //     } else {
    //         $this->assertFalse($res['estatus']);
    //     }
    // }

    // public static function providerValidarApartamento(): array
    // {
    //     return [
    //         'Apartamento Existente' => [['nro_apartamento' => '1-1'], true],
    //         'Apartamento NO Existente' => [['nro_apartamento' => '12-1'], false],
    //     ];
    // }

    #[TestDox('Consultar todos los apartamentos')] // Consultar
    #[Test]

    public function testConsultar()
    {
        try {
            $res = $this->apartamento->realizar_consulta('consultar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($res);
    }

    // #[TestDox('Modifica correctamente un Apartamento existente')] // Modificar
    // #[Test]
    // public function testModificarApartamentoExitoso()
    // {
    //     // Primero registra un apartamento para obtener un id válido
    //     $this->apartamento->set_nro_apartamento('5-1');
    //     $this->apartamento->set_porcentaje_participacion('5.25');
    //     $this->apartamento->set_gas(1);
    //     $this->apartamento->set_agua(1);
    //     $this->apartamento->set_alquilado(1);
    //     $resRegistro = $this->apartamento->realizar_consulta('registrar');
    //     $this->assertTrue($resRegistro['estatus'], 'El registro debe retornar estatus true');

    //     // Obtiene el último id insertado
    //     $last = $this->apartamento->realizar_consulta('lastId');
    //     $nuevoId = intval($last['last_id']);
    //     $this->assertGreaterThan(0, $nuevoId);

    //     // Modifica el apartamento
    //     $this->apartamento->set_id_apartamento($nuevoId);
    //     $this->apartamento->set_nro_apartamento('5-2');
    //     $resModificar = $this->apartamento->realizar_consulta('modificar');

    //     $this->assertIsArray($resModificar);
    //     $this->assertTrue($resModificar['estatus'], 'La modificación debe retornar estatus true');
    // }

    // #[TestDox('Prueba de ingreso de datos inválidos al modificar un apartamento (validaciones back-end)')] // Modificar Validaciones
    // #[DataProvider('providerDatosInvalidosModificar')]
    // #[Test]
    // public function testValidacionesDelBackendModificar(array $input, string $expectedMessageFragment)
    // {
    //     // Asignar campos desde el array de entrada
    //     if (isset($input['id_apartamento']))
    //         $this->apartamento->set_id_apartamento($input['id_apartamento']);
    //     if (isset($input['nro_apartamento']))
    //         $this->apartamento->set_nro_apartamento($input['nro_apartamento']);
    //     if (isset($input['porcentaje_participacion']))
    //         $this->apartamento->set_porcentaje_participacion($input['porcentaje_participacion']);
    //     if (isset($input['gas']))
    //         $this->apartamento->set_gas($input['gas']);
    //     if (isset($input['agua']))
    //         $this->apartamento->set_agua($input['agua']);
    //     if (isset($input['alquilado']))
    //         $this->apartamento->set_alquilado($input['alquilado']);

    //     // Ejecutar la acción de modificación
    //     $resultado = $this->apartamento->realizar_consulta('modificar');

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
    //         'Nro de Apartamento Invalido' => [
    //             ['id_apartamento' => 11, 'nro_apartamento' => 'A-10', 'porcentaje_participacion' => '10.5', 'gas' => 1, 'agua' => 1, 'alquilado' => 1],
    //             "Nro de Apartamento"
    //         ],
    //         'Porcentaje de Participacion Invalido' => [
    //             ['id_apartamento' => 11, 'nro_apartamento' => '4-4', 'porcentaje_participacion' => 'abc', 'gas' => 1, 'agua' => 1, 'alquilado' => 1],
    //             "Porcentaje de Participación"
    //         ],
    //         'Gas Invalido' => [
    //             ['id_apartamento' => 11, 'nro_apartamento' => '4-4', 'porcentaje_participacion' => '10.5', 'gas' => 'abc', 'agua' => 1, 'alquilado' => 1],
    //             "Gas"
    //         ],
    //         'Agua Invalido' => [
    //             ['id_apartamento' => 11, 'nro_apartamento' => '4-4', 'porcentaje_participacion' => '10.5', 'gas' => 1, 'agua' => 'abc', 'alquilado' => 1],
    //             "Agua"
    //         ],
    //         'Alquilado Invalido' => [
    //             ['id_apartamento' => 11, 'nro_apartamento' => '4-4', 'porcentaje_participacion' => '10.5', 'gas' => 1, 'agua' => 1, 'alquilado' => 'no'],
    //             "Alquilado"
    //         ],
    //         'Campos Vacios' => [
    //             ['id_apartamento' => 11, 'nro_apartamento' => '', 'porcentaje_participacion' => '', 'gas' => '', 'agua' => '', 'alquilado' => ''],
    //             "Uno o varios de los campos requeridos estan vacios"
    //         ],
    //     ];
    // }

    // #[TestDox('Elimina correctamente un apartamento existente')] // Eliminar
    // #[Test]
    // public function testEliminarExitoso()
    // {
    //     // Primero registra un apartamento para obtener un id válido
    //     $this->apartamento->set_nro_apartamento('6-1');
    //     $this->apartamento->set_porcentaje_participacion('5.25');
    //     $this->apartamento->set_gas(1);
    //     $this->apartamento->set_agua(1);
    //     $this->apartamento->set_alquilado(1);
    //     $resRegistro = $this->apartamento->realizar_consulta('registrar');
    //     $this->assertTrue($resRegistro['estatus'], 'El registro debe retornar estatus true');

    //     // Obtiene el último id insertado
    //     $last = $this->apartamento->realizar_consulta('lastId');
    //     $nuevoId = intval($last['last_id']);
    //     $this->assertGreaterThan(0, $nuevoId);

    //     // Elimina el apartamento
    //     $this->apartamento->set_id_apartamento($nuevoId);
    //     $resEliminar = $this->apartamento->realizar_consulta('eliminar');

    //     $this->assertIsArray($resEliminar);
    //     $this->assertTrue($resEliminar['estatus'], 'La eliminación debe retornar estatus true');
    // }

    // #[TestDox('Prueba de eliminación de Apartamento (validaciones back-end)')] // Eliminar Validaciones
    // #[DataProvider('providerEliminarInvalido')]
    // #[Test]
    // public function testValidacionesEliminar(array $input, string $expectedMessageFragment)
    // {
    //     // Asignar el campo según los datos de entrada
    //     if (isset($input['id_apartamento']))
    //         $this->apartamento->set_id_apartamento($input['id_apartamento']);

    //     // Ejecutar la validación del método eliminar
    //     $resultado = $this->apartamento->realizar_consulta('eliminar');

    //     // Afirmaciones
    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertArrayHasKey('mensaje', $resultado);

    //     // Como son pruebas de validaciones inválidas, debe dar falso
    //     $this->assertFalse($resultado['estatus']);
    //     $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    // }

    // public static function providerEliminarInvalido(): array
    // {
    //     return [
    //         'ID no recibido' => [
    //             [],
    //             "id del Apartamento requerido no se recibio correctamente"
    //         ],
    //         'ID vacío' => [
    //             ['id_apartamento' => ''],
    //             "id del Apartamento requerido esta vacio"
    //         ],
    //         'ID no numérico' => [
    //             ['id_apartamento' => 'ABC'],
    //             "id del Apartamento debe ser un valor numerico entero"
    //         ],
    //         'ID inexistente' => [
    //             ['id_apartamento' => 999999],
    //             "apartamento seleccionado no existe"
    //         ],
    //     ];
    // }
}
?>