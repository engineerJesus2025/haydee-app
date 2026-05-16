<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Test;
use haydee\modelo\DetallesPago;

final class DetallesPagosTest extends TestCase
{
    protected DetallesPago $detalles_pago;

    protected function setUp(): void
    {
        $this->detalles_pago = new DetallesPago();
    }

    #[TestDox('Registro Correctamente un Detalle Pago con datos válidos')]
    #[Test]
    public function testRegistroExitoso()
    {
        $this->detalles_pago->set_fecha('2025-10-22');
        $this->detalles_pago->set_monto(100);
        $this->detalles_pago->set_monto_dolar(5);
        $this->detalles_pago->set_tipo_pago('Efectivo');
        $this->detalles_pago->set_pago_id(80);

        $resultado = $this->detalles_pago->realizar_consulta('registrar_detalles');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], 'El registro debe retornar estatus true');
    }

    #[TestDox('Prueba de ingreso de datos (validaciones back-end de Detalles de Pago)')]
    #[DataProvider('providerDatosInvalidosRegistrar')]
    #[Test]
    public function testValidacionesDelBackendRegistrar(array $input, string $expectedMessageFragment)
    {
        if (isset($input['fecha']))
            $this->detalles_pago->set_fecha($input['fecha']);
        if (isset($input['monto']))
            $this->detalles_pago->set_monto($input['monto']);
        if (isset($input['monto_dolar']))
            $this->detalles_pago->set_monto_dolar($input['monto_dolar']);
        if (isset($input['tipo_pago']))
            $this->detalles_pago->set_tipo_pago($input['tipo_pago']);
        if (isset($input['pago_id']))
            $this->detalles_pago->set_pago_id($input['pago_id']);

        $resultado = $this->detalles_pago->realizar_consulta('registrar_detalles');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertArrayHasKey('mensaje', $resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString($expectedMessageFragment, $resultado['mensaje']);
    }

    public static function providerDatosInvalidosRegistrar(): array
    {
        return [
            'Campos no recibidos correctamente' => [
                [],
                "Uno o varios de los campos requeridos no se recibieron correctamente"
            ],
            'Campos vacíos' => [
                ['fecha' => '', 'monto' => '', 'monto_dolar' => '', 'tipo_pago' => '', 'pago_id' => ''],
                "Uno o varios de los campos requeridos estan vacios"
            ],
            'Fecha inválida' => [
                ['fecha' => 20240510, 'monto' => 100, 'monto_dolar' => 3.5, 'tipo_pago' => 'Transferencia', 'pago_id' => 1],
                "Fecha"
            ],
            'Monto inválido (no numérico)' => [
                ['fecha' => '2024-05-10', 'monto' => 'ABC', 'monto_dolar' => 3.5, 'tipo_pago' => 'Transferencia', 'pago_id' => 1],
                "Monto"
            ],
            'Monto inválido (formato incorrecto)' => [
                ['fecha' => '2024-05-10', 'monto' => '100.999', 'monto_dolar' => 3.5, 'tipo_pago' => 'Transferencia', 'pago_id' => 1],
                "Monto"
            ],
            'Monto en dólar inválido (no numérico)' => [
                ['fecha' => '2024-05-10', 'monto' => 100, 'monto_dolar' => 'XYZ', 'tipo_pago' => 'Transferencia', 'pago_id' => 1],
                "Monto"
            ],
            'Monto en dólar inválido (formato incorrecto)' => [
                ['fecha' => '2024-05-10', 'monto' => 100, 'monto_dolar' => '3.555', 'tipo_pago' => 'Transferencia', 'pago_id' => 1],
                "Monto"
            ],
            'Tipo de pago inválido (no string)' => [
                ['fecha' => '2024-05-10', 'monto' => 100, 'monto_dolar' => 3.5, 'tipo_pago' => 123, 'pago_id' => 1],
                "Método de Pago"
            ],
            'ID de pago inválido (no numérico)' => [
                ['fecha' => '2024-05-10', 'monto' => 100, 'monto_dolar' => 3.5, 'tipo_pago' => 'Transferencia', 'pago_id' => 'A1'],
                "id del Pago asociado no posee un valor valido"
            ],
        ];
    }

}
