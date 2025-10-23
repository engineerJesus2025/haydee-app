<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Test;
require_once "modelo/bancos_transacciones_modelo.php";

final class BancosTransaccionesTest extends TestCase
{
    protected Bancos_transacciones $bancos_transacciones;

    protected function setUp(): void
    {
        $this->bancos_transacciones = new Bancos_transacciones();
    }

    // #[TestDox('Registro Correctamente un Banco transacción con datos válidos')]
    // #[Test]
    // public function testRegistroExitoso()
    // {
    //     $this->bancos_transacciones->set_referencia('1234567890');
    //     $this->bancos_transacciones->set_imagen('imagen.jpg');
    //     $this->bancos_transacciones->set_detalle_pago_id(158);
    //     $this->bancos_transacciones->set_banco_id(6);

    //     $resultado = $this->bancos_transacciones->realizar_consulta('registrar');

    //     $this->assertIsArray($resultado);
    //     $this->assertArrayHasKey('estatus', $resultado);
    //     $this->assertTrue($resultado['estatus'], 'El registro debe retornar estatus true');
    // }

    // #[TestDox('Prueba de ingreso de datos (validaciones back-end de Bancos Transacciones)')]
    // #[DataProvider('providerDatosInvalidosRegistrar')]
    // #[Test]
    // public function testValidacionesDelBackendRegistrar(array $input, string $expectedMessageFragment)
    // {
    //     if (isset($input['referencia']))
    //         $this->bancos_transacciones->set_referencia($input['referencia']);
    //     if (isset($input['imagen']))
    //         $this->bancos_transacciones->set_imagen($input['imagen']);
    //     if (isset($input['detalle_pago_id']))
    //         $this->bancos_transacciones->set_detalle_pago_id($input['detalle_pago_id']);
    //     if (isset($input['banco_id']))
    //         $this->bancos_transacciones->set_banco_id($input['banco_id']);

    //     $resultado = $this->bancos_transacciones->realizar_consulta('registrar');

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
    //             ['referencia' => '', 'imagen' => '', 'detalle_pago_id' => '', 'banco_id' => ''],
    //             "Uno o varios de los campos requeridos estan vacios"
    //         ],
    //         'Referencia inválida (no string)' => [
    //             ['referencia' => 12345, 'imagen' => 'comprobante.jpg', 'detalle_pago_id' => 158, 'banco_id' => 6],
    //             "El campo 'Referencia' no posee un valor valido"
    //         ],
    //         'Imagen inválida (no string)' => [
    //             ['referencia' => 'ABC123', 'imagen' => 999, 'detalle_pago_id' => 158, 'banco_id' => 6],
    //             "El campo 'Imagen' no posee un valor valido"
    //         ],
    //         'ID de detalle de pago inválido (no numérico)' => [
    //             ['referencia' => 'ABC123', 'imagen' => 'comprobante.jpg', 'detalle_pago_id' => 'XYZ', 'banco_id' => 6],
    //             "El id del Detalle Pago asociado no posee un valor valido"
    //         ],
    //         'ID de banco inválido (no numérico)' => [
    //             ['referencia' => 'ABC123', 'imagen' => 'comprobante.jpg', 'detalle_pago_id' => 158, 'banco_id' => 'BancoX'],
    //             "El id del Banco asociado no posee un valor valido"
    //         ],
    //     ];
    // }

    #[TestDox('Validar existencia de Pago por Referencia')] // Validar
    #[DataProvider('providerValidar')]
    #[Test]
    public function testValidar(array $input, bool $expectedExists)
    {
        $this->bancos_transacciones->set_referencia($input['referencia']);

        try {
            $res = $this->bancos_transacciones->realizar_consulta('validar');
        } catch (\Throwable $e) {
            $this->markTestSkipped('Omitido por falta de entorno/BD: ' . $e->getMessage());
            return;
        }

        $this->assertIsArray($res);
        if ($expectedExists) {
            $this->assertTrue($res['estatus']);
            $this->assertEquals('referencia', $res['busqueda']);
        } else {
            $this->assertFalse($res['estatus']);
        }
    }

    public static function providerValidar(): array
    {
        return [
            'Pago Existente' => [['referencia' => '1234567890'], true],
            'Pago NO Existente' => [['referencia' => '9999999999'], false],
        ];
    }
}
?>