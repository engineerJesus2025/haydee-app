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
        $this->driver->get('http://localhost/haydee-app/?pagina=inicio_controlador.php&accion=inicio');
        
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('b_gastos'));
            },
            "FALLO: El login no pareció exitoso."
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Apartamentos
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=apartamentos_controlador.php&accion=inicio');
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
        
        // 1. Esperar modal de lista de habitantes
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_vista_previa')
            )
        );
        
        // 2. Esperar que la tabla de habitantes cargue
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_habitantes_processing')
            )
        );

        // 3. Clic en "Editar Habitante" (de la fila 1 de la tabla_habitantes)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody/tr[1]//button[@title='Editar Habitante']")
            )
        )->click();

        // 4. Esperar que el modal de registro (#modal_habitantes) aparezca
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_habitantes')
            )
        );

        // 5. Esperar que el formulario cargue datos (ej: cédula no esté vacía)
        $this->driver->wait(10)->until(
             WebDriverExpectedCondition::not(
                WebDriverExpectedCondition::elementValueIs(WebDriverBy::id('cedula'), '')
            ),
            "El modal de modificar se abrió pero no cargó los datos del habitante."
        );
        
        // -----------------------------------------------------------------
        // PASO 5: Modificar Formulario
        // -----------------------------------------------------------------

        // 1. Capturar la Cédula (para buscarla después)
        $cedula_modificada = $this->driver
            ->findElement(WebDriverBy::id('cedula'))
            ->getAttribute('value');

        // 2. Cambiar el teléfono
        $telefonoField = $this->driver->findElement(WebDriverBy::id('telefono'));
        $telefonoField->clear();
        $telefonoField->sendKeys($this->test_telefono);

        // 3. Enviar
        $this->driver->findElement(WebDriverBy::id('boton_formulario_habitantes'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Manejar Alertas y Verificar
        // -----------------------------------------------------------------
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // "Sí, Editar"

        // Mensaje de éxito del controlador
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'actualizados correctamente'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // "Aceptar"
        
        // Esperar que el modal de registro (#modal_habitantes) desaparezca
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('modal_habitantes')
            )
        );

        // Esperar que la tabla_habitantes (en #modal_vista_previa) se refresque
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_habitantes_processing')
            )
        );

        // Verificar que el nuevo teléfono existe en la tabla
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $this->test_telefono // Buscamos el nuevo teléfono
            ),
            "FALLO: El habitante (Cedula: " . $cedula_modificada . ") no mostró el nuevo teléfono (" . $this->test_telefono . ") en la tabla."
        );

        $this->assertTrue(true, "Modificación de habitante exitosa y verificada.");
    }
}