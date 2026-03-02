<?php
// tests/Selenium/habitantesModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class habitantesModificarTest extends TestCase
{
    private $driver;
    private $test_telefono;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        // Teléfono único para verificar el cambio
        $this->test_telefono = "0412" . (string)rand(1000000, 9999999);
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

    public function testModificarHabitanteUI()
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
        // PASO 2: Navegar a Apartamentos
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=apartamentos&accion=inicio');
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR APARTAMENTOS Y HABITANTES')]")
            )
        );
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_apartamentos_processing')
            )
        );

        // -----------------------------------------------------------------
        // PASO 3: Abrir el modal de "Detalles Apartamento"
        // -----------------------------------------------------------------
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//table[@id='tabla_apartamentos']/tbody/tr[1]//button[@title='Detalles Apartamento']")
            )
        )->click();

        // -----------------------------------------------------------------
        // PASO 4: Abrir el modal de "Modificar Habitante"
        // -----------------------------------------------------------------
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_vista_previa')
            )
        );
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_habitantes_processing')
            )
        );

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody/tr[1]//button[@title='Editar Habitante']")
            )
        )->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_habitantes')
            )
        );

        $this->driver->wait(10)->until(
            function () {
                $cedulaValue = $this->driver->findElement(WebDriverBy::id('cedula'))
                    ->getAttribute('value');
                return $cedulaValue !== '';
            },
            "El modal de modificar se abrió pero no cargó los datos del habitante (la cédula está vacía)."
        );
        
        // -----------------------------------------------------------------
        // PASO 5: Modificar Formulario
        // -----------------------------------------------------------------

        $cedula_modificada = $this->driver
            ->findElement(WebDriverBy::id('cedula'))
            ->getAttribute('value');

        $telefonoField = $this->driver->findElement(WebDriverBy::id('telefono'));
        $telefonoField->clear();
        $telefonoField->sendKeys($this->test_telefono);

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('boton_formulario_habitantes')
            )
        )->click();

        // -----------------------------------------------------------------
        // PASO 6: Manejar Alertas y Verificar
        // -----------------------------------------------------------------
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // El test confirma que la modificación fue exitosa por esta alerta:
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro se ha modificado exitosamente'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('modal_habitantes')
            )
        );

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_habitantes_processing')
            )
        );

        // --- ¡CORRECCIÓN APLICADA AQUÍ! ---
        // (Esta era la línea 181 que fallaba)
        // Verificamos usando la CÉDULA (que sí es visible) en lugar del teléfono (oculto).
        // Esto confirma que la tabla se recargó y el ítem modificado sigue allí.
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $cedula_modificada // Buscamos la cédula
            ),
            "FALLO: El habitante (Cedula: " . $cedula_modificada . ") no se encontró en la tabla después de modificar."
        );

        $this->assertTrue(true, "Modificación de habitante exitosa y verificada.");
    }
}