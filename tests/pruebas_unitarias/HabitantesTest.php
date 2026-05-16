<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Test;
use haydee\modelo\Habitantes;

final class HabitantesTest extends TestCase
{
    protected Habitantes $habitante;

    protected function setUp(): void
    {
        $this->habitante = new Habitantes();
    }

    // #[TestDox('Registro Correctamente un habitante con datos válidos')]
    // #[Test]
    // public function testRegistroExitoso()
    // {
    //     $this->habitante->set_nombre('Rafael');
    //     $this->habitante->set_apellido('Gutierrez');
    //     $this->habitante->set_cedula('30353397');
    //     $this->habitante->set_telefono('04127721822');
    //     $this->habitante->set_correo('correo@gmail.com');
    //     $this->habitante->set_fecha_nacimiento('2002-12-26');
    //     $this->habitante->set_sexo('Masculino');

    //     $resultado = $this->habitante->realizar_consulta('registrar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertTrue($resultado['estatus'], 'El registro debe retornar estatus true');
    // }

    #[TestDox('Prueba de ingreso de datos (validaciones back-end de Habitantes)')]
    #[DataProvider('providerDatosInvalido')]
    #[Test]
    public function testValidacionesDelBackend(array $input, string $expectedMessageFragment)
    {
        if (isset($input['nombre']))
            $this->habitante->set_nombre($input['nombre']);
        if (isset($input['apellido']))
            $this->habitante->set_apellido($input['apellido']);
        if (isset($input['cedula']))
            $this->habitante->set_cedula($input['cedula']);
        if (isset($input['telefono']))
            $this->habitante->set_telefono($input['telefono']);
        if (isset($input['correo']))
            $this->habitante->set_correo($input['correo']);
        if (isset($input['fecha_nacimiento']))
            $this->habitante->set_fecha_nacimiento($input['fecha_nacimiento']);
        if (isset($input['sexo']))
            $this->habitante->set_sexo($input['sexo']);

        $resultado = $this->habitante->realizar_consulta('registrar');

        // Verificar estructura del resultado
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertArrayHasKey('mensaje', $resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    }

    public static function providerDatosInvalido(): array
    {
        return [
            'Cedula Invalida' => [
                ['cedula' => '12A45B', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
                "Cedula"
            ],
            'Nombre Invalido' => [
                ['cedula' => '12345678', 'nombre' => 'C@rlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
                "Nombre"
            ],
            'Apellido Invalido' => [
                ['cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Men$za', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
                "Apellido"
            ],
            'Sexo Invalido' => [
                ['cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 123, 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
                "Sexo"
            ],
            'Telefono Invalido' => [
                ['cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04A2123456', 'correo' => 'carlos@gmail.com'],
                "Telefono"
            ],
            'Correo Invalido' => [
                ['cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@@@gmail'],
                "Correo"
            ],
            'Fecha de Nacimiento Invalida' => [
                ['cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => 20000510, 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
                "Fecha de Nacimiento"
            ],
            'Campos Vacíos' => [
                ['cedula' => '', 'nombre' => '', 'apellido' => '', 'fecha_nacimiento' => '', 'sexo' => '', 'telefono' => '', 'correo' => ''],
                "Uno o varios de los campos requeridos estan vacios"
            ],
        ];
    }

    // #[TestDox('Validar existencia de Habitante por Cedula')] // Validar
    // #[DataProvider('providerValidar')]
    // #[Test]
    // public function testValidar(array $input, bool $expectedExists)
    // {
    //     $this->habitante->set_cedula($input['cedula']);

    //     try {
    //         $res = $this->habitante->realizar_consulta('validar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }

    //     $this->assertIsArray($res);
    //     if ($expectedExists) {
    //         $this->assertTrue($res['estatus']);
    //         $this->assertEquals('cedula', $res['busqueda']);
    //     } else {
    //         $this->assertFalse($res['estatus']);
    //     }
    // }

    // public static function providerValidar(): array
    // {
    //     return [
    //         'Habitante Existente' => [['cedula' => '30353397'], true],
    //         'Habitante NO Existente' => [['cedula' => '99999999'], false],
    //     ];
    // }

    // #[TestDox('Consultar todos los habitantes')] // Consultar
    // #[Test]

    // public function testConsultar()
    // {
    //     try {
    //         $res = $this->habitante->realizar_consulta('consultar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }

    //     $this->assertIsArray($res);
    // }

    // #[TestDox('Modifica correctamente un Habitante existente')] // Modificar
    // #[Test]
    // public function testModificarExitoso()
    // {
    //     $this->habitante->set_nombre('Daniel');
    //     $this->habitante->set_apellido('Rojas');
    //     $this->habitante->set_cedula('20252297');
    //     $this->habitante->set_telefono('04127721822');
    //     $this->habitante->set_correo('correo@gmail.com');
    //     $this->habitante->set_fecha_nacimiento('2002-12-26');
    //     $this->habitante->set_sexo('Masculino');
    //     $resRegistro = $this->habitante->realizar_consulta('registrar');
    //     $this->assertTrue($resRegistro['estatus'], 'El registro debe retornar estatus true');

    //     $last = $this->habitante->realizar_consulta('lastId');
    //     $nuevoId = intval($last['last_id']);
    //     $this->assertGreaterThan(0, $nuevoId);

    //     $this->habitante->set_id_habitante($nuevoId);
    //     $this->habitante->set_nombre('Rafael');
    //     $resModificar = $this->habitante->realizar_consulta('modificar');

    //     $this->assertIsArray($resModificar);
    //     $this->assertTrue($resModificar['estatus'], 'La modificación debe retornar estatus true');
    // }

    // #[TestDox('Prueba de ingreso de datos inválidos al modificar un habitante (validaciones back-end)')] // Modificar Validaciones
    // #[DataProvider('providerDatosInvalidosModificar')]
    // #[Test]
    // public function testValidacionesDelBackendModificar(array $input, string $expectedMessageFragment)
    // {
    //     // Asignar campos desde el array de entrada
    //     if (isset($input['id_habitante'])) $this->habitante->set_id_habitante($input['id_habitante']);
    //     if (isset($input['nombre'])) $this->habitante->set_nombre($input['nombre']);
    //     if (isset($input['apellido'])) $this->habitante->set_apellido($input['apellido']);
    //     if (isset($input['cedula'])) $this->habitante->set_cedula($input['cedula']);
    //     if (isset($input['telefono'])) $this->habitante->set_telefono($input['telefono']);
    //     if (isset($input['correo'])) $this->habitante->set_correo($input['correo']);
    //     if (isset($input['fecha_nacimiento'])) $this->habitante->set_fecha_nacimiento($input['fecha_nacimiento']);
    //     if (isset($input['sexo'])) $this->habitante->set_sexo($input['sexo']);

    //     $resultado = $this->habitante->realizar_consulta('modificar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertFalse($resultado['estatus'], 'Debe retornar estatus false para datos inválidos');
    //     $this->assertArrayHasKey('mensaje', $resultado);
    //     $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    // }

    // public static function providerDatosInvalidosModificar(): array
    // {
    //     return [
    //         'Cedula Invalida' => [
    //             ['id_habitante' => '1', 'cedula' => '12A45B', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
    //             "Cedula"
    //         ],
    //         'Nombre Invalido' => [
    //             ['id_habitante' => '1', 'cedula' => '12345678', 'nombre' => 'C@rlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
    //             "Nombre"
    //         ],
    //         'Apellido Invalido' => [
    //             ['id_habitante' => '1', 'cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Men$za', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
    //             "Apellido"
    //         ],
    //         'Sexo Invalido' => [
    //             ['id_habitante' => '1', 'cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 123, 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
    //             "Sexo"
    //         ],
    //         'Telefono Invalido' => [
    //             ['id_habitante' => '1', 'cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04A2123456', 'correo' => 'carlos@gmail.com'],
    //             "Telefono"
    //         ],
    //         'Correo Invalido' => [
    //             ['id_habitante' => '1', 'cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => '2000-05-10', 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@@@gmail'],
    //             "Correo"
    //         ],
    //         'Fecha de Nacimiento Invalida' => [
    //             ['id_habitante' => '1', 'cedula' => '12345678', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'fecha_nacimiento' => 20000510, 'sexo' => 'Masculino', 'telefono' => '04121234567', 'correo' => 'carlos@gmail.com'],
    //             "Fecha de Nacimiento"
    //         ],
    //         'Campos Vacíos' => [
    //             ['id_habitante' => '1', 'cedula' => '', 'nombre' => '', 'apellido' => '', 'fecha_nacimiento' => '', 'sexo' => '', 'telefono' => '', 'correo' => ''],
    //             "Uno o varios de los campos requeridos estan vacios"
    //         ],
    //     ];
    // }

    #[TestDox('Elimina correctamente un habitante existente')] // Eliminar
    #[Test]
    public function testEliminarExitoso()
    {
        $this->habitante->set_nombre('Daniel');
        $this->habitante->set_apellido('Rojas');
        $this->habitante->set_cedula('20252297');
        $this->habitante->set_telefono('04127721822');
        $this->habitante->set_correo('correo@gmail.com');
        $this->habitante->set_fecha_nacimiento('2002-12-26');
        $this->habitante->set_sexo('Masculino');
        $resRegistro = $this->habitante->realizar_consulta('registrar');
        $this->assertTrue($resRegistro['estatus'], 'El registro debe retornar estatus true');

        $last = $this->habitante->realizar_consulta('lastId');
        $nuevoId = intval($last['last_id']);
        $this->assertGreaterThan(0, $nuevoId);

        $this->habitante->set_id_habitante($nuevoId);
        $resEliminar = $this->habitante->realizar_consulta('eliminar');

        $this->assertIsArray($resEliminar);
        $this->assertTrue($resEliminar['estatus'], 'La eliminación debe retornar estatus true');
    }

    #[TestDox('Prueba de eliminación de Habitante (validaciones back-end)')] // Eliminar Validaciones
    #[DataProvider('providerEliminarInvalido')]
    #[Test]
    public function testValidacionesEliminar(array $input, string $expectedMessageFragment)
    {
        if (isset($input['id_habitante']))
            $this->habitante->set_id_habitante($input['id_habitante']);

        $resultado = $this->habitante->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertArrayHasKey('mensaje', $resultado);

        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    }

    public static function providerEliminarInvalido(): array
    {
        return [
            'ID no recibido' => [
                [],
                "id del Habitante requerido no se recibio correctamente"
            ],
            'ID vacío' => [
                ['id_habitante' => ''],
                "id del Habitante requerido esta vacio"
            ],
            'ID no numérico' => [
                ['id_habitante' => 'ABC'],
                "id del Habitante debe ser un valor numerico entero"
            ],
            'ID inexistente' => [
                ['id_habitante' => 999999],
                "habitante seleccionado no existe"
            ],
        ];
    }
}
