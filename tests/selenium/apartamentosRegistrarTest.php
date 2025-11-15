<?php
// tests/Selenium/apartamentosRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect; // <-- CORREGIDO: Este es el namespace correcto

class apartamentosRegistrarTest extends TestCase
{
    private $driver;
    private $test_nro_apartamento;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities); // Corregido
        $this->test_nro_apartamento = (string)rand(500, 999);
    }

    protected function tearDown(): void
    {
        $this->driver->quit(); // Corregido
    }

    public function testRegistrarApartamentoUI()
    {
        // -----------------------------------------------------------------
        // PASO 1: Login y Navegación al Dashboard
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/index.php');
        $this->driver->findElement(WebDriverBy::id('correo_login'))
            ->sendKeys('administrador@gmail.com');
        $this->driver->findElement(WebDriverBy::id('contra'))
            ->sendKeys('12345'); 
        $this->driver->executeScript(
            "document.getElementById('form-login').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"operacion\" value=\"entrar\">');"
        );
        $this->driver->executeScript(
            "document.getElementById('form-login').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"mantener_sesion\" value=\"false\">');"
        );
        $this->driver->findElement(WebDriverBy::id('form-login'))->submit();
        sleep(1); 

        $this->driver->get('http://localhost/haydee-app/?pagina=inicio_controlador.php&accion=inicio');
        $this->driver->wait(10, 500)->until( // Corregido
            function () {
                return $this->driver->findElement(WebDriverBy::id('b_gastos')); // Corregido
            }
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Apartamentos y esperar la carga
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=apartamentos_controlador.php&accion=inicio');
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR APARTAMENTOS')]")
            )
        );
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_apartamentos_processing')
            )
        );

        // -----------------------------------------------------------------
        // PASO 3: Abrir Modal y Rellenar Formulario
        // -----------------------------------------------------------------
        $this->driver->findElement(
            WebDriverBy::xpath("//button[@data-bs-target='#modal_apartamentos']")
        )->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('titulo_modal'), 'Registrar Apartamento'
            )
        );

        $this->driver->findElement(WebDriverBy::id('nro_apartamento'))
            ->sendKeys($this->test_nro_apartamento);
        $this->driver->findElement(WebDriverBy::id('porcentaje_participacion')) // Corregido
            ->sendKeys('10.5'); 
        
        // (Esta era la línea 88-90 que dio el error)
        // <-- CORREGIDO: Se usa "WebDriverSelect" en lugar de "Select"
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('gas'))))
            ->selectByValue('1');
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('agua'))))
            ->selectByValue('2');
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('alquilado'))))
            ->selectByValue('1');

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // -----------------------------------------------------------------
        // PASO 4: Manejar Alertas y Verificar
        // -----------------------------------------------------------------
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // Corregido

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        
        $this->driver->wait(10)->until( // Corregido
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::className('swal2-popup')
            )
        );

        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_apartamentos_filter']//input[@type='search']")
        );
        $searchInput->sendKeys('Nro: ' . $this->test_nro_apartamento);

        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_apartamentos']/tbody");
        
        $this->driver->wait(10)->until( // Corregido
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                'Nro: ' . $this->test_nro_apartamento
            ),
            "FALLO: El nuevo apartamento (Nro: " . $this->test_nro_apartamento . ") no se encontró en la tabla después de registrar."
        );

        $this->assertTrue(true, "Registro exitoso y verificado en la tabla."); // Corregido
    }
}