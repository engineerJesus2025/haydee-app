<?php
// tests/Selenium/presupuestoRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class presupuestoRegistrarTest extends TestCase
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

    public function testRegistrarPresupuestoUI()
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


        $this->driver->get('http://localhost/haydee-app/?pagina=presupuesto&accion=inicio');

        // 3.2. Esperamos a que aparezca el título H2
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR PRESUPUESTOS MENSUALES')]"));
            }
        );

        // -----------------------------------------------------------------
        // PASO 3: Abrir el modal de "regsitrar presupuesto"
        // -----------------------------------------------------------------
        
        $unique_id = rand(1000, 9999);
        $test_cuota_reserva = 100;
        $test_observacion = 'presupuesto Varios Selenium ' . $unique_id;
        $test_monto = 100;
        //valor de montos

        // 3.1 Clic en botón Nueva Solicitud
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_presupuesto']"))->click();

        // 3.2 Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_presupuesto'))
        );

        // llenar datos
        $this->driver->findElement(WebDriverBy::cssSelector('#cuota_reserva'))->sendKeys($test_cuota_reserva);

        $this->driver->findElement(WebDriverBy::cssSelector('#observacion'))->sendKeys($test_observacion);

        $montoElements = $this->driver->findElement(WebDriverBy::cssSelector('#contenedor_presupuestos [value="0"]'))->sendKeys($test_monto);


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
        $tableSelector = WebDriverBy::id('tabla_presupuesto'); // Asegúrate que tu tabla tenga este ID
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $test_observacion
            ),
            "FALLO: El presupuesto registrado ($test_observacion) no apareció en la tabla."
        );

        $this->assertTrue(true, "Registro de presupuesto completado exitosamente.");
    }
}