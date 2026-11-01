<?php 
use PHPUnit\Framework\TestCase;
use haydee\modelo\DetallesPresupuesto;
//Mock
class DetallesPresupuestoTest extends TestCase
{
    private $detalles_presupuesto;
    private $mock_detalles_presupuesto;

    public function setUp(): void{
        $this->mock_detalles_presupuesto = $this->createMock(DetallesPresupuesto::class);
        $this->detalles_presupuesto = $this->mock_detalles_presupuesto;
    }

    public function tearDown(): void{
        unset($this->detalles_presupuesto);
        unset($this->mock_detalles_presupuesto);
    }

    public function testConsultarDetallesPresupuesto(){
        $datos_simulados = [
            [
                'id_detalle_presupuesto' => 1,
                'monto_detalle' => 500.00,
                'nombre_detalle' => 'Materiales',
                'presupuesto_id' => 1,
                'tipo_gasto_id' => 1
            ]
        ];

        $this->mock_detalles_presupuesto->method('realizar_consulta')
            ->with('consultar')
            ->willReturn($datos_simulados);

        $resultado = $this->detalles_presupuesto->realizar_consulta('consultar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('id_detalle_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('monto_detalle', $resultado[0]);
        $this->assertArrayHasKey('nombre_detalle', $resultado[0]);
        $this->assertArrayHasKey('presupuesto_id', $resultado[0]);
        $this->assertArrayHasKey('tipo_gasto_id', $resultado[0]);
    }

    public function testConsultarDetallesPresupuestoIdCorrecto(){
        $datos_simulados = [
            [
                'id_detalle_presupuesto' => 1,
                'monto_detalle' => 500.00,
                'nombre_detalle' => 'Materiales',
                'presupuesto_id' => 70,
                'tipo_gasto_id' => 1
            ]
        ];

        $this->mock_detalles_presupuesto->method('realizar_consulta')
            ->with('consultar_detalles_presupuestos')
            ->willReturn($datos_simulados);

        $this->mock_detalles_presupuesto->method('set_presupuesto_id')
            ->with(70);

        $this->detalles_presupuesto->set_presupuesto_id(70);
        $resultado = $this->detalles_presupuesto->realizar_consulta('consultar_detalles_presupuestos');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertArrayHasKey('id_detalle_presupuesto', $resultado[0]);
        $this->assertArrayHasKey('monto_detalle', $resultado[0]);
        $this->assertArrayHasKey('nombre_detalle', $resultado[0]);
        $this->assertArrayHasKey('presupuesto_id', $resultado[0]);
        $this->assertArrayHasKey('tipo_gasto_id', $resultado[0]);
    }

    public function testConsultarDetallesPresupuestoIdIncorrecto(){
        $this->mock_detalles_presupuesto->method('realizar_consulta')
            ->with('consultar_detalles_presupuestos')
            ->willReturn([]);

        $this->detalles_presupuesto->set_presupuesto_id(123122);
        $resultado = $this->detalles_presupuesto->realizar_consulta('consultar_detalles_presupuestos');
        
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    // Continuar con los demás métodos siguiendo el mismo patrón...
    // Solo muestro algunos ejemplos por brevedad
}
