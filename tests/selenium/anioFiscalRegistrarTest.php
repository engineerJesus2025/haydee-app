<?php
// tests/Selenium/anioFiscalRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class anioFiscalRegistrarTest extends TestCase
{
    private $driver;
    

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

    public function testRegistrarAnioFiscalUI()
    {
        // -----------------------------------------------------------------
        // PASO 1: Login Y ESPERA DE DASHBOARD
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
        
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('contenido'));
            },
            "FALLO: El login no pareció exitoso."
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Años fiscales
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=anio_fiscal_controlador.php&accion=inicio');
        
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR AÑOS FISCALES')]")
            )
        );
       $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_anio_fiscal']//h4[contains(text(), 'Cargando...')]");


        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );


        // -----------------------------------------------------------------
        // PASO 3: Abrir el modal de "Años fiscales"
        // -----------------------------------------------------------------
        
        $unique_id = rand(1000, 9999);
        $test_descripcion = 'Años Varios Selenium ' . $unique_id;
        $test_fecha = date('d-m-Y');

        // 3.1 Clic en botón Nueva Solicitud
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_anio_fiscal']"))->click();

        // 3.2 Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_anio_fiscal'))
        );

        // 1. Fecha del detalle
        // Usamos CSS Selector porque es una clase dentro del array de detalles
        $this->driver->findElement(WebDriverBy::cssSelector('#fecha_inicio'))->sendKeys($test_fecha);


        // 2. Descripción
        $this->driver->findElement(WebDriverBy::id('descripcion'))->sendKeys($test_descripcion);
        
        
       // -----------------------------------------------------------------
        // PASO 4: Guardar y Confirmar
        // -----------------------------------------------------------------

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // 1. Alerta "¿Estás seguro?"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Alerta "Registro exitoso"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 5: Verificación Final
        // -----------------------------------------------------------------

        // Esperar cierre de alerta
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // Verificar que la descripción del gasto aparezca en la tabla
        $tableSelector = WebDriverBy::id('tabla_anio_fiscal'); // Asegúrate que tu tabla tenga este ID
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $test_descripcion
            ),
            "FALLO: El año registrado ($test_descripcion) no apareció en la tabla."
        );

        $this->assertTrue(true, "Registro de año completado exitosamente.");
    }
}