<?php 
use PHPUnit\Framework\TestCase;
use haydee\modelo\MovimientosCaja;
// vendor\bin\phpunit tests
class MovimientosCajaTest extends TestCase
{
    private $movimientos_caja;
    private $mock_movimientos_caja; // Mock

    // IDs para simular entradas
    private $id_consultar = 21;
    private $id_editar = 22;
    private $id_caja = 23;
    private $id_eliminar = 24;
    private $id_gasto_modificar = 116;
    private $id_gasto = 109;

    public function setUp(): void{
        // Crear el mock
        $this->mock_movimientos_caja = $this->createMock(MovimientosCaja::class);
        $this->movimientos_caja = $this->mock_movimientos_caja;
    }

    public function tearDown(): void{
        unset($this->movimientos_caja);
        unset($this->mock_movimientos_caja);
    }

    //Metodo consultar_movimientos_caja
    public function testConsultarMovimientosCaja(){
        // Datos simulados
        $datos_simulados = [
            [
                'id_movimiento_caja' => 1,
                'concepto' => 'Concepto de prueba',
                'monto' => 100.00,
                'fecha' => '2025-01-01',
                'estado' => 'Pendiente',
                'caja_chica_id' => $this->id_caja,
                'gasto_id' => null
            ]
        ];

        // Configurar mock
        $this->mock_movimientos_caja->method('set_caja_chica_id')->with($this->id_caja);
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('consultar_movimientos_caja')
            ->willReturn($datos_simulados);

        $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        $resultado = $this->movimientos_caja->realizar_consulta('consultar_movimientos_caja');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(7, $resultado[0]);
        $this->assertArrayHasKey('id_movimiento_caja', $resultado[0]);
    }

    // Metodo consultar_movimiento
    public function testConsultarMovimientoIdCorrecto(){
        $datos_simulados = [
            'id_movimiento_caja' => $this->id_consultar,
            'concepto' => 'Concepto consulta',
            'monto' => 50.00,
            'fecha' => '2025-01-02',
            'estado' => 'Reposado',
            'caja_chica_id' => $this->id_caja,
            'gasto_id' => $this->id_gasto
        ];

        $this->mock_movimientos_caja->method('set_id_movimiento_caja')->with($this->id_consultar);
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('consultar_movimiento')
            ->willReturn($datos_simulados);

        $this->movimientos_caja->set_id_movimiento_caja($this->id_consultar);
        $resultado = $this->movimientos_caja->realizar_consulta('consultar_movimiento');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(7, $resultado);
        $this->assertArrayHasKey('id_movimiento_caja', $resultado);
    }

    public function testConsultarMovimientoIdIncorrecto(){
        $this->mock_movimientos_caja->method('set_id_movimiento_caja')->with(123122);
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('consultar_movimiento')
            ->willReturn(false); // Simula el false de la aserción

        $this->movimientos_caja->set_id_movimiento_caja(123122);
        $resultado = $this->movimientos_caja->realizar_consulta('consultar_movimiento');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarMovimientoDatosVacios(){
        $this->mock_movimientos_caja->method('set_id_movimiento_caja')->with('');
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('consultar_movimiento')
            ->willReturn(false);
        
        $this->movimientos_caja->set_id_movimiento_caja('');
        $resultado = $this->movimientos_caja->realizar_consulta('consultar_movimiento');
        
        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    // Metodo registrar
    public function testRegistrarMovimientoCajaDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Registro exitoso",
            "lastId" => 25 // ID simulado
        ];
        
        $this->mock_movimientos_caja->method('set_concepto')->with("prueba de registro");
        $this->mock_movimientos_caja->method('set_monto')->with('20');
        $this->mock_movimientos_caja->method('set_fecha')->with("2025-10-10");
        $this->mock_movimientos_caja->method('set_estado')->with("Pendiente por reposicion");
        $this->mock_movimientos_caja->method('set_caja_chica_id')->with($this->id_caja);
        $this->mock_movimientos_caja->method('set_gasto_id')->with(null);
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_concepto("prueba de registro");
        $this->movimientos_caja->set_monto('20');
        $this->movimientos_caja->set_fecha("2025-10-10");
        $this->movimientos_caja->set_estado("Pendiente por reposicion");
        $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        $this->movimientos_caja->set_gasto_id(null);
        $resultado = $this->movimientos_caja->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(3, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
        $this->assertIsNumeric($resultado["lastId"]);
    }

    public function testRegistrarMovimientoCajaDatosIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'fecha' no posee un valor valido"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_concepto("prueba de registro incorrecto");
        $this->movimientos_caja->set_monto('100');
        $this->movimientos_caja->set_fecha("fecha_incorrecta");
        $this->movimientos_caja->set_estado("Pendiente por reposicion");
        $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        $this->movimientos_caja->set_gasto_id(null);
        $resultado = $this->movimientos_caja->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testRegistrarMovimientoCajaIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El id de la caja chica seleccionada no existe"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_concepto("prueba de registro incorrecto");
        $this->movimientos_caja->set_monto('100');
        $this->movimientos_caja->set_fecha("2025-10-10");
        $this->movimientos_caja->set_estado("Pendiente por reposicion");
        $this->movimientos_caja->set_caja_chica_id(24234); //Inexistente
        $this->movimientos_caja->set_gasto_id(null);
        $resultado = $this->movimientos_caja->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El id de la caja chica seleccionada no existe", $resultado["mensaje"]);
    }

    public function testRegistrarMovimientoCajaDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "Uno o varios de los campos requeridos estan vacios"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('registrar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_concepto("");
        $this->movimientos_caja->set_monto('');
        $this->movimientos_caja->set_fecha("");
        $this->movimientos_caja->set_estado("");
        $this->movimientos_caja->set_caja_chica_id('');
        $this->movimientos_caja->set_gasto_id('');
        $resultado = $this->movimientos_caja->realizar_consulta('registrar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo editar
    public function testEditarMovimientoCajaDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Edición exitosa"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);
        
        $this->movimientos_caja->set_id_movimiento_caja($this->id_editar);
        $this->movimientos_caja->set_concepto("prueba de edicion");
        $this->movimientos_caja->set_monto('15');
        $this->movimientos_caja->set_fecha("2025-10-10");
        $this->movimientos_caja->set_estado("Reposado");
        $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        $this->movimientos_caja->set_gasto_id($this->id_gasto_modificar);
        $resultado = $this->movimientos_caja->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEditarMovimientoCajaDatosIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'fecha' no posee un valor valido"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_id_movimiento_caja($this->id_editar);
        $this->movimientos_caja->set_concepto("prueba de edicion");
        $this->movimientos_caja->set_monto('15');
        $this->movimientos_caja->set_fecha("fecha_incorrecta");
        $this->movimientos_caja->set_estado("Reposado");
        $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        $this->movimientos_caja->set_gasto_id(116);
        $resultado = $this->movimientos_caja->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'fecha' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testEditarMovimientoCajaIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "EL movimiento de la caja seleccionada no existe"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_id_movimiento_caja(123123); //id inexistente
        $this->movimientos_caja->set_concepto("prueba de edicion");
        $this->movimientos_caja->set_monto('15');
        $this->movimientos_caja->set_fecha("fecha_incorrecta");
        $this->movimientos_caja->set_estado("Reposado");
        $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        $this->movimientos_caja->set_gasto_id(116);
        $resultado = $this->movimientos_caja->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("EL movimiento de la caja seleccionada no existe", $resultado["mensaje"]);
    }

    public function testEditarMovimientoCajaDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "Uno o varios de los campos requeridos estan vacios"
        ];
        
        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('editar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_id_movimiento_caja($this->id_editar);
        $this->movimientos_caja->set_concepto("");
        $this->movimientos_caja->set_monto('');
        $this->movimientos_caja->set_fecha("");
        $this->movimientos_caja->set_estado("");
        $this->movimientos_caja->set_caja_chica_id('');
        $this->movimientos_caja->set_gasto_id('');
        $resultado = $this->movimientos_caja->realizar_consulta('editar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("Uno o varios de los campos requeridos estan vacios", $resultado["mensaje"]);
    }

    //Metodo eliminar
    public function testEliminarMovimientosCajaDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Eliminación exitosa"
        ];
        
        $this->mock_movimientos_caja->method('set_id_movimiento_caja')->with($this->id_eliminar);
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('eliminar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_id_movimiento_caja($this->id_eliminar); //Existente
        $resultado = $this->movimientos_caja->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testEliminarMovimientosCajaIDIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "EL movimiento de la caja seleccionada no existe"
        ];
        
        $this->mock_movimientos_caja->method('set_id_movimiento_caja')->with(12312312);
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('eliminar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_id_movimiento_caja(12312312); // Id inexistente
        $resultado = $this->movimientos_caja->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("EL movimiento de la caja seleccionada no existe", $resultado["mensaje"]);
    }

    public function testEliminarMovimientosCajaUnicoDatosVacios(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El ID requerido esta vacío"
        ];
        
        $this->mock_movimientos_caja->method('set_id_movimiento_caja')->with('');
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('eliminar')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_id_movimiento_caja('');
        $resultado = $this->movimientos_caja->realizar_consulta('eliminar');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El ID requerido esta vacío", $resultado["mensaje"]);
    }

    // Metodo reponer_caja
    public function testReponerMovimientoCajaDatosCorrectos(){
        $resultado_esperado = [
            "estatus" => true,
            "mensaje" => "OK: Reposición exitosa"
        ];
        
        $this->mock_movimientos_caja->method('set_monto')->with('100');
        $this->mock_movimientos_caja->method('set_caja_chica_id')->with($this->id_caja);
        $this->mock_movimientos_caja->method('set_gasto_id')->with($this->id_gasto);
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('reponer_caja')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_monto('100');        
        $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        $this->movimientos_caja->set_gasto_id($this->id_gasto);
        $resultado = $this->movimientos_caja->realizar_consulta('reponer_caja');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado["estatus"]);
        $this->assertStringContainsString('OK', $resultado["mensaje"]);
    }

    public function testReponerMovimientoCajaMontoIncorrecto(){
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'monto' no posee un valor valido"
        ];

        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('reponer_caja')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_monto('monto incorrecto');        
        $this->movimientos_caja->set_caja_chica_id($this->id_caja);
        $this->movimientos_caja->set_gasto_id($this->id_gasto);
        $resultado = $this->movimientos_caja->realizar_consulta('reponer_caja');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'monto' no posee un valor valido", $resultado["mensaje"]);
    }

    public function testReponerMovimientoCajaDatosVacios(){
        // Nota: La aserción espera "monto... invalido", no "vacío"
        $resultado_esperado = [
            "estatus" => false,
            "mensaje" => "El campo 'monto' no posee un valor valido" 
        ];
        
        // ... (configuración de setters) ...
        $this->mock_movimientos_caja->method('realizar_consulta')
            ->with('reponer_caja')
            ->willReturn($resultado_esperado);

        $this->movimientos_caja->set_monto('');        
        $this->movimientos_caja->set_caja_chica_id('');
        $this->movimientos_caja->set_gasto_id('');
        $resultado = $this->movimientos_caja->realizar_consulta('reponer_caja');
        
        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
        $this->assertCount(2, $resultado);
        $this->assertFalse($resultado["estatus"]);
        $this->assertStringContainsString("El campo 'monto' no posee un valor valido", $resultado["mensaje"]);
    }
}
