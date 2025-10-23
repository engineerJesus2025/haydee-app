<?php
use PHPUnit\Framework\TestCase;
require_once "modelo/detalles_gastos_modelo.php";
// .\vendor\bin\phpunit tests\pruebas_unitarias\detallesGastosTest.php --testdox

class DetallesGastosTest extends TestCase
{
    private $detallesGastos;

    public function setUp(): void
    {
        $this->detallesGastos = new Detalles_gasto();

        $this->detallesGastos->get_conex()->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->detallesGastos->get_conex()->rollBack();
        unset($this->detallesGastos);
    }

    public function testConsultarDetallesGastos()
    {
        $resultado = $this->detallesGastos->realizar_consulta('consultar');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_detalle_gasto', $resultado[0]);
        $this->assertArrayHasKey('descripcion_detalle_gasto', $resultado[0]); 
        $this->assertArrayHasKey('monto', $resultado[0]);         
        $this->assertArrayHasKey('fecha', $resultado[0]);        
        $this->assertArrayHasKey('metodo_pago', $resultado[0]);
    }

    public function testConsultarDetalleGastoIdCorrecto()
    {
        $this->detallesGastos->set_id_detalle_gasto(117); 
        
        $resultado = $this->detallesGastos->realizar_consulta('consultar_detalle_gasto');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_detalle_gasto', $resultado);
        $this->assertArrayHasKey('descripcion_detalle_gasto', $resultado);
        $this->assertArrayHasKey('monto', $resultado);
        $this->assertArrayHasKey('fecha', $resultado);
        $this->assertArrayHasKey('metodo_pago', $resultado);
    }

    public function testConsultarDetalleGastoIdIncorrecto()
    {
        $this->detallesGastos->set_id_detalle_gasto(99999); // ID Inexistente
        
        $resultado = $this->detallesGastos->realizar_consulta('consultar_detalle_gasto');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('No se encontró el detalle', $resultado['mensaje']);
    }

    public function testConsultarDetalleGastoIdVacio()
    {
        $this->detallesGastos->set_id_detalle_gasto(''); // ID Vacío
        
        $resultado = $this->detallesGastos->realizar_consulta('consultar_detalle_gasto');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('No se encontró el detalle del gasto', $resultado['mensaje']);
    }

    // -------------- REGISTRAR -----------------
    public function testRegistrarDetalleDatosCorrectos()
    {
        $this->detallesGastos->set_fecha('2025-10-21');
        $this->detallesGastos->set_monto(150.75);
        $this->detallesGastos->set_metodo_pago('Transferencia');
        $this->detallesGastos->set_descripcion_detalle_gasto('Prueba de registro');
        $this->detallesGastos->set_gasto_id(94);

        $resultado = $this->detallesGastos->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "Falló: " . ($resultado['mensaje'] ?? ''));
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testRegistrarDetalleDatosVacios()
    {
        $this->detallesGastos->set_fecha(''); // <-- Vacío
        $this->detallesGastos->set_monto('');
        $this->detallesGastos->set_metodo_pago('');
        $this->detallesGastos->set_descripcion_detalle_gasto('');
        $this->detallesGastos->set_gasto_id('');

        $resultado = $this->detallesGastos->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        // Prueba la primera validación que falla
        $this->assertStringContainsString('La fecha no puede estar vacía', $resultado['mensaje']);
    }

    public function testRegistrarDetalleDatosInvalidos()
    {
        $this->detallesGastos->set_fecha('2025-10-21');
        $this->detallesGastos->set_monto(0); // Inválido
        $this->detallesGastos->set_metodo_pago('Efectivo23');
        $this->detallesGastos->set_descripcion_detalle_gasto('Monto 0');
        $this->detallesGastos->set_gasto_id(9999);

        $resultado = $this->detallesGastos->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('monto debe ser un número mayor a cero', $resultado['mensaje']);
    }

    // -------------- EDITAR -----------------

public function testEditarDetalleDatosCorrectos()
    {
        $this->detallesGastos->set_id_detalle_gasto(117);
        $this->detallesGastos->set_fecha('2025-11-25');
        $this->detallesGastos->set_monto(200.00);
        $this->detallesGastos->set_metodo_pago('Pago Movil');
        $this->detallesGastos->set_descripcion_detalle_gasto('Prueba de modificación');
        $this->detallesGastos->set_gasto_id(94);

        $resultado = $this->detallesGastos->realizar_consulta('editar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "Falló: " . ($resultado['mensaje'] ?? ''));
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testEditarDetalleIdInexistente()
    {
        $this->detallesGastos->set_id_detalle_gasto(99999); // ID Inválido
        $this->detallesGastos->set_fecha('2025-11-25');
        $this->detallesGastos->set_monto(200.00);
        $this->detallesGastos->set_metodo_pago('Pago Movil');
        $this->detallesGastos->set_descripcion_detalle_gasto('Prueba ID inválido');
        $this->detallesGastos->set_gasto_id(94);

        $resultado = $this->detallesGastos->realizar_consulta('editar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('detalle de gasto seleccionado no existe', $resultado['mensaje']);
    }

    public function testEditarDetalleDatosInvalidos()
    {
        $this->detallesGastos->set_id_detalle_gasto(117);
        $this->detallesGastos->set_fecha('fecha-mala');
        $this->detallesGastos->set_monto('monto malo');
        $this->detallesGastos->set_metodo_pago('Pago1Movil123');
        $this->detallesGastos->set_descripcion_detalle_gasto('Prueba fecha inválida');
        $this->detallesGastos->set_gasto_id(94);

        $resultado = $this->detallesGastos->realizar_consulta('editar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('formato de la fecha no es válido', $resultado['mensaje']);
    }

    public function testEditarDetalleDatosVacios()
    {
        $this->detallesGastos->set_id_detalle_gasto(117);
        $this->detallesGastos->set_fecha('');
        $this->detallesGastos->set_monto('');
        $this->detallesGastos->set_metodo_pago('');
        $this->detallesGastos->set_descripcion_detalle_gasto('');
        $this->detallesGastos->set_gasto_id('');

        $resultado = $this->detallesGastos->realizar_consulta('editar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('La fecha no puede estar vacía', $resultado['mensaje']);
    }

    // -------------- ELIMINAR -----------------
    public function testEliminarDetalleDatosCorrectos()
    {
        $this->detallesGastos->set_id_detalle_gasto(117);
        $resultado = $this->detallesGastos->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testEliminarDetalleIdInexistente()
    {
        $this->detallesGastos->set_id_detalle_gasto(99999);
        $resultado = $this->detallesGastos->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('detalle de gasto seleccionado no existe', $resultado['mensaje']);
    }

    public function testEliminarDetalleIdVacio()
    {
        $this->detallesGastos->set_id_detalle_gasto(''); 
        $resultado = $this->detallesGastos->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('id del detalle de gasto requerido esta vacio', $resultado['mensaje']);
    }

}