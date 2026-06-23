<?php
use PHPUnit\Framework\TestCase;
use haydee\modelo\TipoGasto;

class TipoGastoTest extends TestCase{
    private $tipo_gasto;

    public function setUp(): void{
        $this->tipo_gasto = new TipoGasto();

        $this->tipo_gasto->get_conex()->beginTransaction();
    }

    public function tearDown(): void{
        $this->tipo_gasto->get_conex()->rollBack();
        unset($this->tipo_gasto);
    }

    public function testConsultarTiposGasto(){
        $resultado = $this->tipo_gasto->realizar_consulta('consultar');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_tipo_gasto', $resultado[0]);
        $this->assertArrayHasKey('nombre_tipo_gasto', $resultado[0]);
    }

    public function testConsultarTipoGastoUnicoIdCorrecto(){
        $this->tipo_gasto->set_id_tipo_gasto(1);

        $resultado = $this->tipo_gasto->realizar_consulta('consultar_tipo_gasto');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);

        $this->assertArrayHasKey('id_tipo_gasto', $resultado);
        $this->assertArrayHasKey('nombre_tipo_gasto', $resultado);
    }

    public function testConsultarTipoGastoUnicoIdIncorrecto(){
        $this->tipo_gasto->set_id_tipo_gasto(9999);

        $resultado = $this->tipo_gasto->realizar_consulta('consultar_tipo_gasto');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarTipoGastoUnicoDatosVacios(){
        $this->tipo_gasto->set_id_tipo_gasto('');

        $resultado = $this->tipo_gasto->realizar_consulta('consultar_tipo_gasto');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    // -------------- REGISTRAR --------------//

    public function testRegistrarTipoGastoDatosCorrectos(){
        $this->tipo_gasto->set_nombre_tipo_gasto('Prueba Unitaria');

        $resultado = $this->tipo_gasto->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testRegistrarTipoGastoDatosVacios(){
        $this->tipo_gasto->set_nombre_tipo_gasto('');

        $resultado = $this->tipo_gasto->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El nombre del tipo de gasto no puede estar vacío', $resultado['mensaje']);
    }


    public function testRegistrarTipoGastoDatosIncorrectos(){
        $this->tipo_gasto->set_nombre_tipo_gasto('Prueba123'); // Dato incorrecto (con números)
        $resultado = $this->tipo_gasto->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('solo puede contener letras y espacios', $resultado['mensaje']);
    }

    // -------------- MODIFICAR --------------//
    public function testModificarTipoGastoDatosCorrectos(){
        $this->tipo_gasto->set_id_tipo_gasto(1);
        $this->tipo_gasto->set_nombre_tipo_gasto('Modificacion Unitaria');

        $resultado = $this->tipo_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testModificarTipoGastoIdInexistente(){
        $this->tipo_gasto->set_id_tipo_gasto(95499129); // Id inexistente
        $this->tipo_gasto->set_nombre_tipo_gasto('Modificacion Inexistente');

        $resultado = $this->tipo_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El tipo de gasto seleccionado no existe', $resultado['mensaje']);
    }

    public function testModificarTipoGastoDatosVacios(){
        $this->tipo_gasto->set_id_tipo_gasto(1);
        $this->tipo_gasto->set_nombre_tipo_gasto('');

        $resultado = $this->tipo_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El nombre del tipo de gasto no puede estar vacío', $resultado['mensaje']);
    }
    public function testModificarTipoGastoDatosIncorrectos(){
        $this->tipo_gasto->set_id_tipo_gasto(1);
        $this->tipo_gasto->set_nombre_tipo_gasto('Modificacion123'); // Dato incorrecto (con números)

        $resultado = $this->tipo_gasto->realizar_consulta('modificar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);

        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('solo puede contener letras y espacios', $resultado['mensaje']);
    }

// -------------- ELIMINAR --------------//
    public function testEliminarTipoGastoDatosCorrectos(){
        $this->tipo_gasto->set_id_tipo_gasto(2); // Id existente

        $resultado = $this->tipo_gasto->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarTipoGastoIDIncorrecto(){
        $this->tipo_gasto->set_id_tipo_gasto(12312312); // Id inexistente

        $resultado = $this->tipo_gasto->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El tipo de gasto seleccionado no existe", $resultado["mensaje"]);
    }

    public function testEliminarTipoGastoDatosVacios(){
        $this->tipo_gasto->set_id_tipo_gasto('');

        $resultado = $this->tipo_gasto->realizar_consulta('eliminar');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id del tipo de gasto requerido esta vacio", $resultado["mensaje"]);
    }
}