<?php
// tests/Selenium/pagosConsultarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class pagosConsultarTest extends TestCase
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

    public function testConsultarPagoUI()
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
                return $this->driver->findElement(WebDriverBy::id('b_gastos'));
            },
            "FALLO: El login no pareció exitoso."
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Pagos
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=pagos_controlador.php&accion=inicio');
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR PAGOS')]")
            )
        );
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_pagos_processing')
            )
        );

        // -----------------------------------------------------------------
        // PASO 3: Capturar datos de la tabla principal
        // -----------------------------------------------------------------
        
        // --- ¡CORRECCIÓN APLICADA AQUÍ! ---
        // Se actualizó la lógica de espera para que ignore el texto "Cargando...".
        
        $fecha_tabla = $this->driver->wait(10)->until(
            function () {
                try {
                    $element = $this->driver->findElement(
                        WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody/tr[1]/td[1]")
                    );
                    $text = $element->getText();
                    
                    // Condición mejorada:
                    // El texto NO debe estar vacío Y NO debe ser "Cargando..."
                    if ($text !== '' && $text !== 'Cargando...') {
                        return $text;
                    }
                    return false; // Sigue esperando
                    
                } catch (Facebook\WebDriver\Exception\StaleElementReferenceException $e) {
                    return false;
                } catch (Facebook\WebDriver\Exception\NoSuchElementException $e) {
                    return false;
                }
            },
            "FALLO: No se pudo leer el texto de la celda (td[1]) o solo se leyó 'Cargando...'."
        );
        // -----------------------------------------------------------------
        
        $this->assertNotEmpty($fecha_tabla, "La fecha en la tabla (fila 1) no puede estar vacía.");

        // -----------------------------------------------------------------
        // PASO 4: Abrir Modal de Consulta (Vista Previa)
        // -----------------------------------------------------------------
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                 WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody/tr[1]//button[@title='Vista previa']")
            )
        )->click();
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_vista_previa')
            )
        );

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::not(
                WebDriverExpectedCondition::elementTextIs(WebDriverBy::id('vista_fecha'), '')
            ),
            "El modal de vista previa se abrió, pero no cargó los datos (la fecha está vacía)."
        );

        $fecha_modal = $this->driver->findElement(WebDriverBy::id('vista_fecha'))->getText();
        
        // (Esta era la línea 133 que fallaba)
        $this->assertEquals($fecha_tabla, $fecha_modal, "La FECHA de la tabla no coincide con la del modal.");

        // -----------------------------------------------------------------
        // PASO 5: Verificar Sub-Modal de Detalles
        // -----------------------------------------------------------------

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_detalles_pagos_processing')
            )
        );

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::xpath("//table[@id='tabla_detalles_pagos']/tbody/tr[1]/td[1]")
            )
        );
        $fecha_detalle_tabla = $this->driver->findElement(
            WebDriverBy::xpath("//table[@id='tabla_detalles_pagos']/tbody/tr[1]/td[1]")
        )->getText();
         $monto_detalle_tabla = $this->driver->findElement(
            WebDriverBy::xpath("//table[@id='tabla_detalles_pagos']/tbody/tr[1]/td[2]")
        )->getText();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                 WebDriverBy::xpath("//table[@id='tabla_detalles_pagos']/tbody/tr[1]//button[@title='Informacion']")
            )
        )->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_vista_previa_detalles')
            )
        );
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::not(
                WebDriverExpectedCondition::elementTextIs(WebDriverBy::id('vista_fecha_detalles'), '')
            )
        );

        $fecha_detalle_modal = $this->driver->findElement(WebDriverBy::id('vista_fecha_detalles'))->getText();
        $monto_detalle_modal = $this->driver->findElement(WebDriverBy::id('vista_monto_detalles'))->getText(); 

        // -----------------------------------------------------------------
        // PASO 6: Comparar (Assert) y Finalizar
        // -----------------------------------------------------------------

        $this->assertEquals($fecha_detalle_tabla, $fecha_detalle_modal, "La FECHA del detalle (tabla vs modal) no coincide.");
        $this->assertStringStartsWith($monto_detalle_modal, $monto_detalle_tabla, "El MONTO del detalle (tabla vs modal) no coincide.");

        $this->assertTrue(true, "Consulta de Pago y Detalle de Pago exitosa.");
    }
}