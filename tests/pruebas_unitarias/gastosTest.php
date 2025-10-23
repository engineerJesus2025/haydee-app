<?php
use PHPUnit\Framework\TestCase;
require_once "modelo/gastos_modelo.php";
// .\vendor\bin\phpunit tests\pruebas_unitarias\GastosTest.php --testdox

class GastosTest extends TestCase
{
    private $gastos;

    public function setUp(): void
    {
        $this->gastos = new Gastos();

        $this->gastos->get_conex()->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->gastos->get_conex()->rollBack();
        unset($this->gastos);
    }

    public function testConsultarGastos()
    {
        $resultado = $this->gastos->realizar_consulta('consultar');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        // Claves que SÍ devuelve tu consulta
        $this->assertArrayHasKey('id_gasto', $resultado[0]);
        $this->assertArrayHasKey('descripcion_gasto', $resultado[0]); 
        $this->assertArrayHasKey('monto_total', $resultado[0]);         
        $this->assertArrayHasKey('ultima_fecha', $resultado[0]);        
        $this->assertArrayHasKey('nombre_tipo_gasto', $resultado[0]);
        $this->assertArrayHasKey('nombre_proveedor', $resultado[0]);
        $this->assertArrayHasKey('metodo_pago_predominante', $resultado[0]);
    }

    public function testConsultarGastoIdCorrecto()
    {
        $this->gastos->set_id_gasto(94); 
        $resultado = $this->gastos->realizar_consulta('consultar_gasto');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_gasto', $resultado);
        $this->assertArrayHasKey('descripcion_gasto', $resultado);
        $this->assertArrayHasKey('nombre_proveedor', $resultado);
        $this->assertArrayHasKey('descripcion_necesidad', $resultado);
        $this->assertArrayHasKey('monto_total', $resultado);
        $this->assertArrayHasKey('nombre_tipo_gasto', $resultado);
    }

    public function testConsultarGastoIdIncorrecto()
    {
        $this->gastos->set_id_gasto(99999); // ID Inexistente
        $resultado = $this->gastos->realizar_consulta('consultar_gasto');
        
        $this->assertFalse($resultado);
    }

    public function testConsultarGastoIdVacio()
    {
        $this->gastos->set_id_gasto(''); // ID Vacío
        $resultado = $this->gastos->realizar_consulta('consultar_gasto');
        
        $this->assertFalse($resultado);
    }

    // ---------------- REGISTRAR ----------------
    public function testRegistrarGastoDatosCorrectos()
    {
        $this->gastos->set_tipo('fijo');
        $this->gastos->set_descripcion_gasto('Prueba de registro de gasto');
        $this->gastos->set_tipo_gasto_id(10);
        $this->gastos->set_proveedor_id(1); 
        $this->gastos->set_solicitud_id(8);

        $resultado = $this->gastos->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "El registro falló: " . ($resultado['mensaje'] ?? ''));
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testRegistrarGastoDatosVacios()
    {
        $this->gastos->set_tipo(''); // <-- Vacío
        $this->gastos->set_descripcion_gasto('');
        $this->gastos->set_tipo_gasto_id('');

        $resultado = $this->gastos->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString("El campo 'Tipo' no puede estar vacío", $resultado['mensaje']);
    }

    public function testRegistrarGastoDatosIncorrectos()
    {
        $this->gastos->set_tipo('123132'); // <-- Inválido
        $this->gastos->set_descripcion_gasto('Prueba de tipo inválido');
        $this->gastos->set_tipo_gasto_id(999999);

        $resultado = $this->gastos->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString("El valor para 'Tipo' no es válido", $resultado['mensaje']);
    }

    // ---------------- MODIFICAR ----------------

    public function testModificarGastoDatosCorrectos()
    {
        $this->gastos->set_id_gasto(101); // Asume que el ID 1 existe
        $this->gastos->set_tipo('variable');
        $this->gastos->set_descripcion_gasto('Descripción modificada por prueba');
        $this->gastos->set_tipo_gasto_id(1); // Asume que el tipo_gasto 1 existe
        $this->gastos->set_proveedor_id(1); 
        $this->gastos->set_solicitud_id(10);

        $resultado = $this->gastos->realizar_consulta('editar_gasto'); 

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "La modificación falló: " . ($resultado['mensaje'] ?? ''));
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testModificarGastoDatosIncorrectos()
    {
        $this->gastos->set_id_gasto(109);
        $this->gastos->set_tipo('errortipo12'); // <-- Inválido
        $this->gastos->set_descripcion_gasto('Descripción modificada por prueba');
        $this->gastos->set_tipo_gasto_id('tipoerror2'); 
        $this->gastos->set_proveedor_id('proveedorerror3'); 
        $this->gastos->set_solicitud_id('solicituderror1'); 

        $resultado = $this->gastos->realizar_consulta('editar_gasto');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString("El valor para 'Tipo' no es válido", $resultado['mensaje']);
    }

    public function testModificarGastoDatosVacios()
    {
        $this->gastos->set_id_gasto(110); 
        $this->gastos->set_tipo(''); 
        $this->gastos->set_descripcion_gasto('');
        $this->gastos->set_tipo_gasto_id('');
        $this->gastos->set_proveedor_id('');
        $this->gastos->set_solicitud_id('');

        $resultado = $this->gastos->realizar_consulta('editar_gasto');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString("El campo 'Tipo' no puede estar vacío", $resultado['mensaje']);
    }

    public function testModificarGastoIdVacio()
    {
        $this->gastos->set_id_gasto(''); // Inválido
        $this->gastos->set_tipo('fijo');
        $this->gastos->set_descripcion_gasto('Prueba');
        $this->gastos->set_tipo_gasto_id(1);

        $resultado = $this->gastos->realizar_consulta('editar_gasto');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString("El id del gasto requerido esta vacio", $resultado['mensaje']);
    }

    public function testModificarGastoIdInexistente()
    {
        $this->gastos->set_id_gasto(99999); // Inválido
        $this->gastos->set_tipo('fijo');
        $this->gastos->set_descripcion_gasto('Prueba');
        $this->gastos->set_tipo_gasto_id(1);

        $resultado = $this->gastos->realizar_consulta('editar_gasto');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString("El gasto seleccionado no existe", $resultado['mensaje']);
    }

    // ---------------- ELIMINAR ----------------
    public function testEliminarGastoDatosCorrectos()
    {
        $this->gastos->set_id_gasto(94); 
        
        $resultado = $this->gastos->realizar_consulta('eliminar_gasto'); 

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('Gasto y comprobantes eliminados correctamente', $resultado['mensaje']);
    }

    public function testEliminarGastoIdInexistente()
    {
        $this->gastos->set_id_gasto(99999); // Inválido
        $resultado = $this->gastos->realizar_consulta('eliminar_gasto');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString("El gasto seleccionado no existe", $resultado['mensaje']);
    }

    public function testEliminarGastoIdVacio()
    {
        $this->gastos->set_id_gasto(''); // Inválido
        $resultado = $this->gastos->realizar_consulta('eliminar_gasto');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString("El id del gasto requerido esta vacio", $resultado['mensaje']);
    }


    // --------------- OTROS --------------
    public function testListarMesesConGastos()
    {
        $resultado = $this->gastos->listar_meses_con_gastos();

        $this->assertIsArray($resultado);
        if (count($resultado) > 0) {
            $this->assertArrayHasKey('anio', $resultado[0]);
            $this->assertArrayHasKey('mes', $resultado[0]);
            $this->assertIsNumeric($resultado[0]['anio']);
            $this->assertIsNumeric($resultado[0]['mes']);
        }
    }


}