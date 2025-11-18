<?php
// tests/Selenium/usuarioConsultarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition; // Necesario para la espera

class usuarioConsultarTest extends TestCase
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

    public function testConsultarUsuarioUI()
    {
        // -----------------------------------------------------------------
        // PASO 1: Realizar el Login
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/index.php');

        $ID_CORREO = 'correo_login';
        $ID_CLAVE  = 'contra';
        $ID_FORM   = 'form-login';

        $this->driver->findElement(WebDriverBy::id($ID_CORREO))
            ->sendKeys('administrador@gmail.com');
            
        $this->driver->findElement(WebDriverBy::id($ID_CLAVE))
            ->sendKeys('12345'); 

        $this->driver->executeScript(
            "document.getElementById('".$ID_FORM."').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"operacion\" value=\"entrar\">');"
        );
        $this->driver->executeScript(
            "document.getElementById('".$ID_FORM."').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"mantener_sesion\" value=\"false\">');"
        );

        $this->driver->findElement(WebDriverBy::id($ID_FORM))
            ->submit();
        
        sleep(1); 

        // -----------------------------------------------------------------
        // PASO 2: Confirmar que estamos en el Dashboard
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=inicio_controlador.php&accion=inicio');

        // --- Corrección 1 (Línea 62) ---
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('contenido'));
            }
        );


        $this->driver->get('http://localhost/haydee-app/?pagina=usuario_controlador.php&accion=inicio');

        // 3.2. Esperamos a que aparezca el título H2
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR USUARIOS')]"));
            }
        );

        // 3.3. Definimos el *selector* del elemento "Cargando..."
        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_usuario']//h4[contains(text(), 'Cargando...')]");


        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // 3.5. Si la espera anterior no falló, la prueba es exitosa.
        $this->assertTrue(true, "La tabla de usuarios se cargó correctamente (desapareció 'Cargando...').");
    }
}