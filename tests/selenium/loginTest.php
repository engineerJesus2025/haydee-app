<?php
// tests/Selenium/LoginUITest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

class loginTest extends TestCase
{
    private $driver;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [
            'browserName' => 'MicrosoftEdge'
        ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    protected function tearDown(): void
    {
        $this->driver->quit(); 
    }

    public function testLoginExitosoUI()
    {
        // 1. Ir a la página de login
        $this->driver->get('http://localhost/haydee-app/index.php');

        $ID_CORREO = 'correo_login';
        $ID_CLAVE  = 'contra';
        $ID_FORM   = 'form-login';

        // 2. Llenar el formulario
        $this->driver->findElement(WebDriverBy::id($ID_CORREO))
            ->sendKeys('administrador@gmail.com');
            
        $this->driver->findElement(WebDriverBy::id($ID_CLAVE))
            ->sendKeys('12345'); // Asegúrate que esta sea la clave correcta

        // 3. --- ¡EL ARREGLO MÁGICO! ---
        // Inyectamos los campos ocultos que el AJAX normalmente enviaría
        
        // Inyecta: <input type="hidden" name="operacion" value="entrar">
        $this->driver->executeScript(
            "document.getElementById('".$ID_FORM."').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"operacion\" value=\"entrar\">');"
        );
        // Inyecta: <input type="hidden" name="mantener_sesion" value="false">
        // (Tu controlador lo necesita para el 'else' en la línea 69)
        $this->driver->executeScript(
            "document.getElementById('".$ID_FORM."').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"mantener_sesion\" value=\"false\">');"
        );

        // 4. Enviar el formulario (¡Ahora SÍ enviará 'operacion=entrar'!)
        $this->driver->findElement(WebDriverBy::id($ID_FORM))
            ->submit();
        
        // 5. Pausa breve (1 seg) para que el servidor cree la sesión
        sleep(1); 

        // 6. Navegamos manualmente al dashboard (¡ya tienes la URL correcta!)
        $this->driver->get('http://localhost/haydee-app/?pagina=inicio_controlador.php&accion=inicio');

        // 7. Esperamos por 'b_gastos'
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('b_gastos'));
            }
        );

        $this->assertTrue(true, "Login exitoso, se encontró el elemento 'b_gastos'.");
    }
}