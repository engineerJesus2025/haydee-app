<?php
// tests/Selenium/pagosModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class pagosModificarTest extends TestCase
{
    private $driver;
    private $test_observacion;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        
        $this->test_observacion = "TestMod-" . rand(10000, 99999);
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

    public function testModificarPagoUI()
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
        // PASO 3: Abrir Modal de Modificación
        // -----------------------------------------------------------------

        // 1. Clic en "Editar" (title de pagos_ajax.js)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                 WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody/tr[1]//button[@title='Editar']")
            )
        )->click();
        
        // 2. Esperar que el modal cargue los datos (que #apartamento_id tenga valor)
        $this->driver->wait(10)->until(
            function () {
                $apartamentoValue = $this->driver->findElement(WebDriverBy::id('apartamento_id'))
                    ->getAttribute('value');
                return $apartamentoValue !== '';
            },
            "El modal de modificar se abrió pero no cargó los datos (Apartamento está vacío)."
        );
        
        // 3. Esperar que el AJAX de mensualidades cargue las OPCIONES
        $this->driver->wait(10)->until(
            function () {
                $optionsCount = count($this->driver->findElements(
                    WebDriverBy::xpath("//select[@id='mensualidad_id']/option")
                ));
                return $optionsCount > 1; // Espera a que las opciones de AJAX se carguen
            },
            "El modal cargó el Apartamento, pero el AJAX de Mensualidades no cargó las opciones."
        );


        // -----------------------------------------------------------------
        // PASO 4: Modificar el formulario
        // -----------------------------------------------------------------
        
        $NUEVO_ESTADO = 'No verificado';

        // Usamos selectByIndex(1) para escoger la PRIMERA mensualidad disponible
        try {
            (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('mensualidad_id'))))
                ->selectByIndex(1);
        } catch (\Exception $e) {
            $this->fail("FALLO: No se pudo seleccionar la primera mensualidad (índice 1). ¿Está el dropdown vacío? " . $e->getMessage());
        }

        // --- INICIO DE LA CORRECCIÓN (NaN) ---
        // El usuario reporta que el campo 'tasa_dolar' se llena con 'NaN'.
        // Lo limpiamos y aseguramos un valor válido.
        $tasaField = $this->driver->findElement(WebDriverBy::className('tasa_dolar'));
        $tasaField->clear();
        $tasaField->sendKeys('30'); // Valor solicitado por el usuario
        // --- FIN DE LA CORRECCIÓN (NaN) ---


        // Cambiamos el estado (sabemos que "No verificado" existe)
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('estado'))))
            ->selectByValue($NUEVO_ESTADO);
        
        // Cambiamos la observación
        $obsField = $this->driver->findElement(WebDriverBy::id('observacion'));
        $obsField->clear();
        $obsField->sendKeys($this->test_observacion);

        // 5. Enviar
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('boton_formulario')
            )
        )->click();

        // -----------------------------------------------------------------
        // PASO 5: Manejar Alertas y Verificar
        // -----------------------------------------------------------------
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        
        // Clic forzado con JS (Robusto)
        $selectorConfirmarSeguro = WebDriverBy::xpath(
            "//div[contains(@class, 'swal2-popup') and contains(@class, 'swal2-show')]//button[contains(@class, 'swal2-confirm')]"
        );
        $botonSeguro = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($selectorConfirmarSeguro)
        );
        $this->driver->executeScript("arguments[0].click();", [$botonSeguro]);
        
        
        // (Texto de pagos_ajax.js)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro se ha modificado exitosamente'
            )
        );
        
        // Clic forzado con JS (Robusto)
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
        
        // Espera para que la tabla se recargue
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody/tr")
            ),
            "FALLO: La tabla no se recargó con datos después de cerrar el modal."
        );


        // 6. Verificar
        
        $searchSelector = WebDriverBy::xpath(
            "//div[@id='tabla_pagos_filter']//input[@type='search']"
        );
        
        // Forzar búsqueda con JS
        $searchInput = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($searchSelector),
            "FALLO: El campo de búsqueda de la tabla nunca apareció en el DOM."
        );
        
        // Buscamos por el ESTADO, que sí es visible
        $cadena_busqueda = $NUEVO_ESTADO; // "No verificado"

        $this->driver->executeScript("arguments[0].value = arguments[1];", [$searchInput, $cadena_busqueda]);
        $this->driver->executeScript("arguments[0].dispatchEvent(new Event('input', { bubbles: true }));", [$searchInput]);
        

        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody");
        
        // Verificamos el NUEVO_ESTADO
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $NUEVO_ESTADO
            ),
            "FALLO: El nuevo estado (" . $NUEVO_ESTADO . ") no se encontró en la tabla."
        );
        
        $this->assertTrue(true, "Modificación de pago exitosa y verificada.");
    }
}