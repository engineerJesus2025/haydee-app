<?php
use PHPUnit\Framework\TestCase;
use haydee\modelo\Proveedores;
// .\vendor\bin\phpunit tests\pruebas_unitarias\ProveedoresTest.php --testdox

class ProveedoresTest extends TestCase
{
    private $proveedores;

    public function setUp(): void
    {
        $this->proveedores = new Proveedores();

        $this->proveedores->get_conex()->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->proveedores->get_conex()->rollBack();
        unset($this->proveedores);
    }

    public function testConsultarProveedores()
    {
        $resultado = $this->proveedores->realizar_consulta('consultar');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_proveedor', $resultado[0]);
        $this->assertArrayHasKey('nombre_proveedor', $resultado[0]);
        $this->assertArrayHasKey('servicio', $resultado[0]);
        $this->assertArrayHasKey('rif', $resultado[0]);
        $this->assertArrayHasKey('direccion', $resultado[0]);
    }

    public function testConsultarPoveedorUnicoIdCorrecto()
    {
        $this->proveedores->set_id_proveedor(1);

        $resultado = $this->proveedores->realizar_consulta('consultar_proveedor');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(5, $resultado);

        $this->assertArrayHasKey('id_proveedor', $resultado);
        $this->assertArrayHasKey('nombre_proveedor', $resultado);
        $this->assertArrayHasKey('servicio', $resultado);
        $this->assertArrayHasKey('rif', $resultado);
        $this->assertArrayHasKey('direccion', $resultado);
    }

    public function testConsultarProveedorUnicoIdIncorrecto()
    {
        $this->proveedores->set_id_proveedor(9999);

        $resultado = $this->proveedores->realizar_consulta('consultar_proveedor');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarProveedorUnicoDatosVacios()
    {
        $this->proveedores->set_id_proveedor('');

        $resultado = $this->proveedores->realizar_consulta('consultar_proveedor');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    // -------------- REGISTRAR --------------//
    public function testRegistrarProveedorDatosCorrectos()
    {
        $this->proveedores->set_nombre_proveedor('Proveedor Prueba Unitaria');
        $this->proveedores->set_servicio('Servicio de Prueba Unitaria');
        $this->proveedores->set_rif('v123456789');
        $this->proveedores->set_direccion('Direccion de Prueba Unitaria');

        $resultado = $this->proveedores->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('Proveedor registrado correctamente', $resultado['mensaje']);
    }

    public function testRegistrarProveedorRifDuplicado()
    {
        $this->proveedores->set_nombre_proveedor('Proveedor de Prueba');
        $this->proveedores->set_servicio('Servicio de Prueba');
        $this->proveedores->set_rif('J123456789'); // RIF que vamos a duplicar
        $this->proveedores->set_direccion('Direccion de Prueba');
        $this->proveedores->realizar_consulta('registrar'); 

        $this->proveedores->set_nombre_proveedor('Otro Proveedor');
        $this->proveedores->set_servicio('Otro Servicio');
        $this->proveedores->set_rif('J123456789'); // RIF duplicado
        $this->proveedores->set_direccion('Otra Direccion');

        $resultado = $this->proveedores->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('Ya existe un proveedor con ese RIF', $resultado['mensaje']);
    }

    public function testRegistrarProveedorDatosVacios()
    {
        $this->proveedores->set_nombre_proveedor('');
        $this->proveedores->set_servicio('');
        $this->proveedores->set_rif('');
        $this->proveedores->set_direccion('');

        $resultado = $this->proveedores->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El nombre del proveedor no puede estar vacío', $resultado['mensaje']);

    }

    // -------------- MODIFICAR --------------//
    public function testModificarProveedorDatosCorrectos(){
        $this->proveedores->set_id_proveedor(1);
        $this->proveedores->set_nombre_proveedor('Proveedor Modificado');
        $this->proveedores->set_servicio('Servicio Modificado');
        $this->proveedores->set_rif('V111111111'); 
        $this->proveedores->set_direccion('Direccion Modificada');

        $resultado = $this->proveedores->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "La modificación falló: " . ($resultado['mensaje'] ?? 'Error desconocido'));
        $this->assertStringContainsString('Proveedor modificado correctamente', $resultado['mensaje']);
    }

    public function testModificarProveedorIdVacio(){
        $this->proveedores->set_id_proveedor(''); // ID vacio
        $this->proveedores->set_nombre_proveedor('Test Modificacion');
        $this->proveedores->set_servicio('Servicio');
        $this->proveedores->set_rif('VV222222222');
        $this->proveedores->set_direccion('Direccion');

        $resultado = $this->proveedores->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El id del proveedor requerido esta vacio', $resultado['mensaje']);
    }

    public function testModificarProveedorIdInexistente(){
        $this->proveedores->set_id_proveedor(99999); 
        $this->proveedores->set_nombre_proveedor('Intento de Modificacion');
        $this->proveedores->set_servicio('Servicio');
        $this->proveedores->set_rif('V-33333333-3');
        $this->proveedores->set_direccion('Direccion');

        $resultado = $this->proveedores->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El proveedor seleccionado no existe', $resultado['mensaje']);
    }

    public function testModificarProveedorDatosVacios(){
        $this->proveedores->set_id_proveedor(1);
        $this->proveedores->set_nombre_proveedor('');
        $this->proveedores->set_servicio('');
        $this->proveedores->set_rif(''); // <-- Dato Inválido
        $this->proveedores->set_direccion('');

        $resultado = $this->proveedores->realizar_consulta('');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('Acción no válida', $resultado['mensaje']);
    }

    // -------------- ELIMINAR --------------//
    public function testEliminarProveedorDatosCorrectos(){
        $this->proveedores->set_id_proveedor(2); // ID Válido
        $resultado = $this->proveedores->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('Proveedor eliminado correctamente', $resultado['mensaje']);
    }


    public function testEliminarProveedorIdInexistente(){
        $this->proveedores->set_id_proveedor(99999); // ID Inválido
        $resultado = $this->proveedores->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El proveedor seleccionado no existe', $resultado['mensaje']);
    }

    public function testEliminarProveedorIdVacio(){
        $this->proveedores->set_id_proveedor(''); // ID Inválido
        $resultado = $this->proveedores->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El id del proveedor requerido esta vacio', $resultado['mensaje']);
    }
}
