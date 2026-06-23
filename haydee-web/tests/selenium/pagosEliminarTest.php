<?php
// tests/Selenium/pagosEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class pagosEliminarTest extends TestCase
{
    private $driver;
    private $test_observacion;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        
        $this->test_observacion = "TestDel-" . rand(10000, 99999);
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

    public function testEliminarPagoUI()
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
                return $this->driver->findElement(WebDriverBy::id('b_gastos'));
            },
            "FALLO: El login no pareció exitoso."
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Pagos
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=pagos&accion=inicio');
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
        // PASO 3: Registrar un Pago para Eliminar
        // (USANDO LA LÓGICA DEL 'pagosRegistrarTest.php' QUE SÍ FUNCIONA)
        // -----------------------------------------------------------------
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                 WebDriverBy::xpath("//button[@data-bs-target='#modal_pagos']")
            )
        )->click();
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_pagos')
            )
        );
        
        // -- Datos que SABEMOS que funcionan --
        $ID_APARTAMENTO_EXISTENTE = '11'; 
        $ID_MENSUALIDAD_EXISTENTE = '331';
        $MONTO_A_REGISTRAR = '123.45'; // Usaremos este para buscarlo
        $TASA_A_REGISTRAR = '30.5';
        $DOLAR_A_REGISTRAR = '4.05';
        $ESTADO_A_REGISTRAR = 'No verificado'; // Un estado fácil de encontrar
        // --

        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('apartamento_id'))))
            ->selectByValue($ID_APARTAMENTO_EXISTENTE);

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                 WebDriverBy::xpath("//select[@id='mensualidad_id']/option[@value='" . $ID_MENSUALIDAD_EXISTENTE . "']")
            ),
            "FALLO: El AJAX de mensualidades no cargó (no se encontró option value='" . $ID_MENSUALIDAD_EXISTENTE . "'). Revisa si esta mensualidad ya fue pagada."
        );
         (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('mensualidad_id'))))
            ->selectByValue($ID_MENSUALIDAD_EXISTENTE);
        
        $fecha_test = '2024-10-10'; // Fecha válida (no futura)
        $this->driver->executeScript(
            "document.getElementsByClassName('fecha_admin')[0].value = '" . $fecha_test . "';"
        );
        
        $metodoPagoSelect = new WebDriverSelect($this->driver->findElement(WebDriverBy::className('tipo_pago_admin')));
        $metodoPagoSelect->selectByValue('Efectivo');
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::className('monto')
            )
        );

        $montoField = $this->driver->findElement(WebDriverBy::className('monto'));
        $montoField->clear();
        $montoField->sendKeys($MONTO_A_REGISTRAR);
        
        $tasaField = $this->driver->findElement(WebDriverBy::className('tasa_dolar'));
        $tasaField->clear();
        $tasaField->sendKeys($TASA_A_REGISTRAR);

        $dolarField = $this->driver->findElement(WebDriverBy::className('tasa_dolar'));
        $dolarField->clear();
        $dolarField->sendKeys($DOLAR_A_REGISTRAR);
        
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('estado'))))
            ->selectByValue($ESTADO_A_REGISTRAR);
        $this->driver->findElement(WebDriverBy::id('observacion'))
            ->sendKeys($this->test_observacion);

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('boton_formulario')
            )
        )->click();
        
        // Alertas de registro (Clic forzado con JS)
        // ESTA ES LA LÍNEA 161 (APROX) QUE ESTABA FALLANDO
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            ),
            "FALLO: La validación del formulario de registro falló. La alerta 'Manejar Alertas y Verificar' no apareció."
        );
        $selectorConfirmarSeguro = WebDriverBy::xpath(
            "//div[contains(@class, 'swal2-popup') and contains(@class, 'swal2-show')]//button[contains(@class, 'swal2-confirm')]"
        );
        $botonSeguro = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($selectorConfirmarSeguro)
        );
        $this->driver->executeScript("arguments[0].click();", [$botonSeguro]);

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente'
            )
        );
        $selectorConfirmarExito = WebDriverBy::xpath(
            "//div[contains(@class, 'swal2-popup') and contains(@class, 'swal2-show')]//button[contains(@class, 'swal2-confirm')]"
        );
        $botonExito = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($selectorConfirmarExito)
        );
        $this->driver->executeScript("arguments[0].click();", [$botonExito]);
        
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('modal_pagos')
            )
        );
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_pagos_processing')
            )
        );
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody/tr")
            )
        );

        // -----------------------------------------------------------------
        // PASO 4: Buscar y Eliminar el Pago Creado
        // -----------------------------------------------------------------
        
        $searchSelector = WebDriverBy::xpath(
            "//div[@id='tabla_pagos_filter']//input[@type='search']"
        );
        
        $searchInput = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($searchSelector),
            "FALLO: El campo de búsqueda de la tabla nunca apareció en el DOM."
        );
        
        // Buscamos por el MONTO y ESTADO (visibles)
        $cadena_busqueda = $MONTO_A_REGISTRAR . " " . $ESTADO_A_REGISTRAR; // "123.45 No verificado"

        $this->driver->executeScript("arguments[0].value = arguments[1];", [$searchInput, $cadena_busqueda]);
        $this->driver->executeScript("arguments[0].dispatchEvent(new Event('input', { bubbles: true }));", [$searchInput]);
        

        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody");
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $MONTO_A_REGISTRAR
            ),
            "FALLO: No se encontró el pago recién creado (" . $cadena_busqueda . ") para eliminarlo."
        );
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                 WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody/tr[1]//button[@title='Eliminar']")
            )
        )->click();
        
        // -----------------------------------------------------------------
        // PASO 5: Manejar Alertas de Eliminación y Verificar
        // -----------------------------------------------------------------
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        
        $selectorEliminarSeguro = WebDriverBy::xpath(
            "//div[contains(@class, 'swal2-popup') and contains(@class, 'swal2-show')]//button[contains(@class, 'swal2-confirm')]"
        );
        $botonEliminarSeguro = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($selectorEliminarSeguro)
        );
        $this->driver->executeScript("arguments[0].click();", [$botonEliminarSeguro]);
        
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro ha sido eliminado correctamente'
            )
        );
        
        $selectorEliminarExito = WebDriverBy::xpath(
            "//div[contains(@class, 'swal2-popup') and contains(@class, 'swal2-show')]//button[contains(@class, 'swal2-confirm')]"
        );
        $botonEliminarExito = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($selectorEliminarExito)
        );
        $this->driver->executeScript("arguments[0].click();", [$botonEliminarExito]);
        
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::className('swal2-popup')
            )
        );
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados" 
            ),
            "FALLO: El pago eliminado (" . $cadena_busqueda . ") todavía se encuentra en la tabla."
        );

        $this->assertTrue(true, "Eliminación de pago exitosa: Creado, eliminado y verificado.");
    }
}