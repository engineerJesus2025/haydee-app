<?php
// tests/Selenium/cajaChicaRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class cajaChicaRegistrarTest extends TestCase
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

    public function testRegistrarCajaChicaUI()
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
        $this->driver->get('http://localhost/haydee-app/?pagina=inicio&accion=inicio');
        
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('contenido'));
            },
            "FALLO: El login no pareció exitoso."
        );

        // -----------------------------------------------------------------
        // PASO 2: Confirmar que estamos en el Dashboard
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=inicio&accion=inicio');

        // --- Corrección 1 (Línea 62) ---
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('contenido'));
            }
        );


        $this->driver->get('http://localhost/haydee-app/?pagina=caja_chica&accion=inicio');

        // 3.2. Esperamos a que aparezca el título H2
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR CAJA CHICA')]"));
            }
        );


        // -----------------------------------------------------------------
        // PASO 3: Abrir el modal de "regsitrar gasto de caja"
        // -----------------------------------------------------------------
        
        $unique_id = rand(1000, 9999);
        $test_descripcion = 'cocepto ' . $unique_id;
        $test_fecha = date('d-m-Y');
        $test_monto = 10;

        // 3.1 Clic en botón Nueva Solicitud
        $this->driver->wait(700, 1000)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_registro_gastos']"))->click();
            }
        );
        // 3.2 Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_registro_gasto'))
        );

        // 1. Fecha del detalle
        // Usamos CSS Selector porque es una clase dentro del array de detalles
        $this->driver->findElement(WebDriverBy::cssSelector('#fecha'))->sendKeys($test_fecha);

        // 2. Descripción
        $this->driver->findElement(WebDriverBy::id('concepto'))->sendKeys($test_descripcion);
        
        $this->driver->findElement(WebDriverBy::cssSelector('#monto'))->sendKeys($test_monto);
        
       // -----------------------------------------------------------------
        // PASO 4: Guardar y Confirmar
        // -----------------------------------------------------------------

        $this->driver->findElement(WebDriverBy::id('boton_gasto_caja'))->click();

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

        // Buscar por la descripción única
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_registros_sistema_filter']//input[@type='search']")
        );
        $searchInput->clear();
        $searchInput->sendKeys($test_fecha);

        // Verificar que la descripción del gasto aparezca en la tabla
        $tableSelector = WebDriverBy::id('tabla_registros_sistema'); // Asegúrate que tu tabla tenga este ID
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $test_descripcion
            ),
            "FALLO: El gasto de caja registrado ($test_descripcion) no apareció en la tabla."
        );

        $this->assertTrue(true, "Registro de gasto de caja completado exitosamente.");
    }
}