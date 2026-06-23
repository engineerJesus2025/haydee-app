<?php
use PHPUnit\Framework\TestCase;
use haydee\modelo\CarteleraVirtual;
// .\vendor\bin\phpunit tests\pruebas_unitarias\CarteleraVirtualTest.php --testdox

class CarteleraVirtualTest extends TestCase{
    private $carteleraVirtual;

    public function setUp(): void
    {
        $this->carteleraVirtual = new CarteleraVirtual();
    }

    public function tearDown(): void
    {
        unset($this->carteleraVirtual);
    }

    public function testConsultarCarteleraVirtual()
    {
        $resultado = $this->carteleraVirtual->realizar_consulta('consultar');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_cartelera', $resultado[0]);
        $this->assertArrayHasKey('titulo', $resultado[0]);
        $this->assertArrayHasKey('fecha', $resultado[0]);
        $this->assertArrayHasKey('prioridad', $resultado[0]);
        $this->assertArrayHasKey('nombre_usuario', $resultado[0]);
    }

    public function testConsultarCarteleraVirtualIdCorrecto()
    {
        $this->carteleraVirtual->set_id_cartelera(13);

        $resultado = $this->carteleraVirtual->realizar_consulta('consultar_cartelera_id');

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);

        $this->assertArrayHasKey('id_cartelera', $resultado);
        $this->assertArrayHasKey('titulo', $resultado);
        $this->assertArrayHasKey('fecha', $resultado);
        $this->assertArrayHasKey('prioridad', $resultado);
        $this->assertArrayHasKey('nombre_usuario', $resultado);
    }

    public function testConsultarCarteleraVirtualIdIncorrecto()
    {
        $this->carteleraVirtual->set_id_cartelera(9999);

        $resultado = $this->carteleraVirtual->realizar_consulta('consultar_cartelera_id');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    public function testConsultarCarteleraVirtualIdVacio()
    {
        $this->carteleraVirtual->set_id_cartelera('');

        $resultado = $this->carteleraVirtual->realizar_consulta('consultar_cartelera_id');

        $this->assertIsBool($resultado);
        $this->assertFalse($resultado);
    }

    // ------------------- REGISTRAR --------------------
    public function testRegistrarPublicacionDatosCorrectos()
    {
        $this->carteleraVirtual->set_usuario_id(1); // Asume que el usuario 1 existe
        $this->carteleraVirtual->set_titulo('titulo prueba'); 
        $this->carteleraVirtual->set_descripcion('descripcion prueba');
        $this->carteleraVirtual->set_fecha(date('Y-m-d'));
        $this->carteleraVirtual->set_prioridad(1);
        $this->carteleraVirtual->set_imagen(''); // Sin imagen

        $resultado = $this->carteleraVirtual->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "El registro falló: " . ($resultado['mensaje'] ?? ''));
        $this->assertStringContainsString('OK', $resultado['mensaje']);
    }

    public function testRegistrarPublicacionDatosVacios()
    {
        $this->carteleraVirtual->set_usuario_id(1); // Válido
        $this->carteleraVirtual->set_titulo(''); // <-- Inválido
        $this->carteleraVirtual->set_descripcion('');
        $this->carteleraVirtual->set_fecha(date(''));
        $this->carteleraVirtual->set_prioridad('');

        $resultado = $this->carteleraVirtual->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('campos requeridos están vacíos', $resultado['mensaje']);
    }

    public function testRegistrarPublicacionDatosInexistentes()
    {
        $this->carteleraVirtual->set_usuario_id(99999); // <+-- Inválido
        $this->carteleraVirtual->set_titulo('titulo prueba');
        $this->carteleraVirtual->set_descripcion('titulo prueba');
        $this->carteleraVirtual->set_fecha(date('Y-m-d'));
        $this->carteleraVirtual->set_prioridad(1);

        $resultado = $this->carteleraVirtual->realizar_consulta('registrar');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El usuario asociado no existe', $resultado['mensaje']);
    }

    // ------------------- MODIFICAR --------------------
    public function testModificarPublicacionDatosCorrectos()
    {
        $this->carteleraVirtual->set_id_cartelera(13); 
        $this->carteleraVirtual->set_usuario_id(1);
        $this->carteleraVirtual->set_titulo('titulo modificado');
        $this->carteleraVirtual->set_descripcion('descripcion modificada');
        $this->carteleraVirtual->set_fecha(date('Y-m-d'));
        $this->carteleraVirtual->set_prioridad(2);
        $this->carteleraVirtual->set_imagen(''); // Sin imagen

        $resultado = $this->carteleraVirtual->realizar_consulta('editar_publicacion');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus'], "La modificación falló: " . ($resultado['mensaje'] ?? ''));
        $this->assertStringContainsString('Edición exitosa', $resultado['mensaje']);
    }

        public function testModificarPublicacionDatosVacios()
    {
        $this->carteleraVirtual->set_id_cartelera(13);
        $this->carteleraVirtual->set_usuario_id(1); 
        $this->carteleraVirtual->set_titulo(''); // Inválido
        $this->carteleraVirtual->set_descripcion('');
        $this->carteleraVirtual->set_fecha('');
        $this->carteleraVirtual->set_prioridad('');

        $resultado = $this->carteleraVirtual->realizar_consulta('editar_publicacion');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('campos requeridos están vacíos', $resultado['mensaje']);
    }
    
    public function testModificarPublicacionDatosInvalidos()
    {
        $this->carteleraVirtual->set_id_cartelera(13); // Asume que la publicación con ID 13 existe
        $this->carteleraVirtual->set_usuario_id(99999); // <-- Inválido
        $this->carteleraVirtual->set_titulo('titulo*/-3');
        $this->carteleraVirtual->set_descripcion('cx123123-* modificada');
        $this->carteleraVirtual->set_fecha(date('maloo'));
        $this->carteleraVirtual->set_prioridad('f');
        $this->carteleraVirtual->set_imagen(''); 

        $resultado = $this->carteleraVirtual->realizar_consulta('editar_publicacion');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El usuario asociado no existe', $resultado['mensaje']);
    }

    public function testModificarPublicacionIdInexistente()
    {
        $this->carteleraVirtual->set_id_cartelera(9999);
        $this->carteleraVirtual->set_usuario_id(1); 
        $this->carteleraVirtual->set_titulo('titulo modificado');
        $this->carteleraVirtual->set_descripcion('descripcion modificada');
        $this->carteleraVirtual->set_fecha(date('Y-m-d'));
        $this->carteleraVirtual->set_prioridad(2);
        $this->carteleraVirtual->set_imagen(''); // Sin imagen

        $resultado = $this->carteleraVirtual->realizar_consulta('editar_publicacion');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('La publicación seleccionada no existe', $resultado['mensaje']);
    }

    // ------------------- ELIMINAR --------------------
    public function testEliminarPublicacionDatosCorrectos()
    {
        $this->carteleraVirtual->set_id_cartelera(21); // ID Válido
        $resultado = $this->carteleraVirtual->realizar_consulta('eliminar_publicacion');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('estatus', $resultado);
        $this->assertTrue($resultado['estatus']);
        $this->assertStringContainsString('Eliminacion exitosa', $resultado['mensaje']);
    }

    public function testEliminarPublicacionDatosIncorrectos()
    {
        $this->carteleraVirtual->set_id_cartelera(9999); // ID Inválido
        $resultado = $this->carteleraVirtual->realizar_consulta('eliminar_publicacion');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('La publicación seleccionada no existe', $resultado['mensaje']);
    }

    public function testEliminarPublicacionDatosVacios()
    {
        $this->carteleraVirtual->set_id_cartelera(''); // ID Vacio
        $resultado = $this->carteleraVirtual->realizar_consulta('eliminar_publicacion');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['estatus']);
        $this->assertStringContainsString('El ID de la publicación es requerido', $resultado['mensaje']);
    }

    // ------------------- OTROS --------------------
    public function testObtenerImagenActualIdCorrecto()
    {
        $this->carteleraVirtual->set_id_cartelera(13); 
        
        $nombre_imagen = $this->carteleraVirtual->obtener_imagen_actual();
        $this->assertTrue(is_string($nombre_imagen) || is_null($nombre_imagen));
    }

    public function testObtenerImagenActualIdInexistente()
    {
        $this->carteleraVirtual->set_id_cartelera(99999); // ID Inexistente
        
        $resultado = $this->carteleraVirtual->obtener_imagen_actual();

        $this->assertNull($resultado);
    }

        public function testObtenerImagenActualIdVacio()
    {
        $this->carteleraVirtual->set_id_cartelera(''); // ID Vacio

        $resultado = $this->carteleraVirtual->obtener_imagen_actual();

        $this->assertNull($resultado);
    }

}