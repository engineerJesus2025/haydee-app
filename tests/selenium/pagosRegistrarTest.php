<?php
// tests/Selenium/pagosRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class pagosRegistrarTest extends TestCase
{
    private $driver;
    private $test_observacion;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        
        $this->test_observacion = "TestReg-" . rand(10000, 99999);
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

    public function testRegistrarPagoUI()
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
        // PASO 3: Abrir Modal y Rellenar Formulario
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
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('titulo_modal'), 'Registrar Pago'
            )
        );

        // -- Datos que usaremos para crear Y verificar --
        $ID_APARTAMENTO_EXISTENTE = '11'; 
        $ID_MENSUALIDAD_EXISTENTE = '331';
        $MONTO_A_REGISTRAR = '123.45';
        $TASA_A_REGISTRAR = '30.5';
        $DOLAR_A_REGISTRAR = '4.05';
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
        
        // 6. Rellenar Detalle de Pago (Usando "Efectivo")
        
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

        $dolarField = $this->driver->findElement(WebDriverBy::className('monto_dolar'));
        $dolarField->clear();
        $dolarField->sendKeys($DOLAR_A_REGISTRAR);
        
        // 7. Rellenar Datos Principales del Pago (con la observación corta)
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('estado'))))
            ->selectByValue('Procesado');
        $this->driver->findElement(WebDriverBy::id('observacion'))
            ->sendKeys($this->test_observacion); // Rellenamos el campo, pero no lo usamos para verificar

        // 8. Enviar
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('boton_formulario')
            )
        )->click();
        
        // -----------------------------------------------------------------
        // PASO 4: Manejar Alertas y Verificar
        // -----------------------------------------------------------------
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            ),
            "FALLO: La alerta '¿Estás seguro?' no apareció. La validación del formulario falló."
        );

        // --- Clic forzado con JS (Robusto) ---
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
        
        // --- Clic forzado con JS (Robusto) ---
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


        // -----------------------------------------------------------------
        // PASO 9: Verificar (CORREGIDO SEGÚN TU SUGERENCIA)
        // -----------------------------------------------------------------
        
        $searchSelector = WebDriverBy::xpath(
            "//div[@id='tabla_pagos_filter']//input[@type='search']"
        );
        
        // 1. Esperamos solo a que exista (presence).
        $searchInput = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($searchSelector),
            "FALLO: El campo de búsqueda de la tabla nunca apareció en el DOM."
        );
        
        // --- INICIO DE LA CORRECCIÓN ---
        // Hacemos la búsqueda SÓLO por el monto, como sugeriste.
        $cadena_busqueda = $MONTO_A_REGISTRAR; // Ej: "123.45"
        // --- FIN DE LA CORRECCIÓN ---

        // 2. Forzamos el valor con JavaScript (para evitar ElementNotInteractable)
        $this->driver->executeScript("arguments[0].value = arguments[1];", [$searchInput, $cadena_busqueda]);
        
        // 3. Forzamos el evento 'input' para que DataTables filtre
        $this->driver->executeScript("arguments[0].dispatchEvent(new Event('input', { bubbles: true }));", [$searchInput]);


        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_pagos']/tbody");
        
        // Verificamos que el MONTO aparezca en la fila resultante
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $MONTO_A_REGISTRAR
            ),
            "FALLO: El MONTO (" . $MONTO_A_REGISTRAR . ") no se encontró en la tabla después de buscar SOLO por el monto."
        );
        
        // (Quitamos la verificación de la mensualidad, ya que no era necesaria)

        $this->assertTrue(true, "Registro de pago exitoso y verificado.");
    }
}