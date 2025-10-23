<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Test;
require_once "modelo/pagos_modelo.php";

final class PagoTest extends TestCase
{
    protected Pagos $pagos;

    protected function setUp(): void
    {
        $this->pagos = new Pagos();
    }

    // #[TestDox('Registro Correctamente un Pago con datos válidos')]
    // #[Test]
    // public function testRegistroExitoso()
    // {
    //     $this->pagos->set_estado('Procesado');
    //     $this->pagos->set_observacion('Pago Observacion');

    //     $resultado = $this->pagos->realizar_consulta('registrar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertTrue($resultado['estatus'], 'El registro debe retornar estatus true');
    // }

    // #[TestDox('Prueba de ingreso de datos (validaciones back-end de Pago)')]
    // #[DataProvider('providerDatosInvalidosRegistrar')]
    // #[Test]
    // public function testValidacionesDelBackendRegistrar(array $input, string $expectedMessageFragment)
    // {
    //     if (isset($input['estado']))
    //         $this->pagos->set_estado($input['estado']);
    //     if (isset($input['observacion']))
    //         $this->pagos->set_observacion($input['observacion']);

    //     $resultado = $this->pagos->realizar_consulta('registrar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertArrayHasKey('mensaje', $resultado);
    //     $this->assertFalse($resultado['estatus']);
    //     $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    // }

    // public static function providerDatosInvalidosRegistrar(): array
    // {
    //     return [
    //         'Campos no recibidos correctamente' => [
    //             [],
    //             "Uno o varios de los campos requeridos no se recibieron correctamente"
    //         ],
    //         'Campos vacíos' => [
    //             ['estado' => '', 'observacion' => ''],
    //             "Uno o varios de los campos requeridos estan vacios"
    //         ],
    //         'Estado inválido (no string)' => [
    //             ['estado' => 123, 'observacion' => 'Todo bien'],
    //             "El campo 'Estado' no posee un valor valido"
    //         ],
    //     ];
    // }

    // #[TestDox('Consultar todos los pagos')] // Consultar
    // #[Test]

    // public function testConsultar()
    // {
    //     try {
    //         $res = $this->pagos->realizar_consulta('consultar');
    //     } catch (\Throwable $e) {
    //         $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
    //         return;
    //     }

    //     $this->assertIsArray($res);
    // }

    // #[TestDox('Modifica correctamente un Pago existente')] // Modificar
    // #[Test]
    // public function testModificarExitoso()
    // {
    //     $this->pagos->set_estado('No Procesado');
    //     $this->pagos->set_observacion('Pago Observacion');
    //     $resRegistro = $this->pagos->realizar_consulta('registrar');
    //     $this->assertTrue($resRegistro['estatus'], 'El registro debe retornar estatus true');

    //     $last = $this->pagos->realizar_consulta('lastId');
    //     $nuevoId = intval($last['last_id']);
    //     $this->assertGreaterThan(0, $nuevoId);

    //     $this->pagos->set_id_pago($nuevoId);
    //     $this->pagos->set_estado('Procesado');
    //     $resModificar = $this->pagos->realizar_consulta('modificar');

    //     $this->assertIsArray($resModificar);
    //     $this->assertTrue($resModificar['estatus'], 'La modificación debe retornar estatus true');
    // }

    // #[TestDox('Prueba de ingreso de datos inválidos al modificar un pago (validaciones back-end)')] // Modificar Validaciones
    // #[DataProvider('providerDatosInvalidosModificar')]
    // #[Test]
    // public function testValidacionesDelBackendModificar(array $input, string $expectedMessageFragment)
    // {
    //     if (isset($input['id_pago']))
    //         $this->pagos->set_id_pago($input['id_pago']);
    //     if (isset($input['estado']))
    //         $this->pagos->set_estado($input['estado']);
    //     if (isset($input['observacion']))
    //         $this->pagos->set_observacion($input['observacion']);

    //     $resultado = $this->pagos->realizar_consulta('modificar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertFalse($resultado['estatus'], 'Debe retornar estatus false para datos inválidos');
    //     $this->assertArrayHasKey('mensaje', $resultado);
    //     $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    // }

    // public static function providerDatosInvalidosModificar(): array
    // {
    //     return [
    //         'Campos no recibidos correctamente' => [
    //             [],
    //             "El id del Pago requerido no se recibio correctamente"
    //         ],
    //         'ID Vacio o nulo' => [
    //             ['id_pago' => ''],
    //             "El id del Pago requerido esta vacio"
    //         ],
    //         'Campos Vacios' => [
    //             ['id_pago' => 102, 'estado' => '', 'observacion' => ''],
    //             "Uno o varios de los campos requeridos estan vacios"
    //         ],
    //         'Estado inválido (no string)' => [
    //             ['id_pago' => 102, 'estado' => 123, 'observacion' => 'Todo bien'],
    //             "El campo 'Estado' no posee un valor valido"
    //         ],
    //     ];
    // }

    #[TestDox('Elimina correctamente un pago existente')] // Eliminar
    #[Test]
    public function testEliminarExitoso()
    {
        $this->pagos->set_estado('Procesado');
        $this->pagos->set_observacion('Pago Observacion');
        $resRegistro = $this->pagos->realizar_consulta('registrar');
        $this->assertTrue($resRegistro['estatus'], 'El registro debe retornar estatus true');

        $last = $this->pagos->realizar_consulta('lastId');
        $nuevoId = intval($last['last_id']);
        $this->assertGreaterThan(0, $nuevoId);

        $this->pagos->set_id_pago($nuevoId);
        $resEliminar = $this->pagos->realizar_consulta('eliminar');

        $this->assertIsArray($resEliminar);
        $this->assertTrue($resEliminar['estatus'], 'La eliminación debe retornar estatus true');
    }

    #[TestDox('Prueba de eliminación de pagos (validaciones back-end)')] // Eliminar Validaciones
    #[DataProvider('providerEliminarInvalido')]
    #[Test]
    public function testValidacionesEliminar(array $input, string $expectedMessageFragment)
    {
        if (isset($input['id_pago']))
            $this->pagos->set_id_pago($input['id_pago']);

        $resultado = $this->pagos->realizar_consulta('eliminar');

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
                "El id del Pago requerido no se recibio correctamente"
            ],
            'ID vacío' => [
                ['id_pago' => ''],
                "El id del Pago requerido esta vacio"
            ],
            'ID no numérico' => [
                ['id_pago' => 'ABC'],
                "El id del Pago debe ser un valor numerico entero"
            ],
            'ID inexistente' => [
                ['id_pago' => 999999],
                "pago seleccionado no existe"
            ],
        ];
    }
}
?>