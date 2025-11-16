<?php
// tests/Selenium/habitantesRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class habitantesRegistrarTest extends TestCase
{
    private $driver;
    private $test_cedula;
    private $test_correo;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        
        $this->test_cedula = (string)rand(10000000, 99999999);
        $this->test_correo = "selenium" . rand(1000, 9999) . "@test.com";
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

    public function testRegistrarHabitanteUI()
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
        
        $viewInhabitantsButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                // Usando el title='Detalles Apartamento' que confirmaste
                WebDriverBy::xpath("//table[@id='tabla_apartamentos']/tbody/tr[1]//button[@title='Detalles Apartamento']")
            )
        );
        $viewInhabitantsButton->click();

        // -----------------------------------------------------------------
        // PASO 4: Abrir el modal de "Registrar Habitante"
        // -----------------------------------------------------------------

        // 1. Esperar que el modal de lista de habitantes (#modal_vista_previa) esté visible
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

        // --- ¡CORRECCIÓN APLICADA AQUÍ! ---
        // (Esta era la línea 119 que fallaba)
        // En lugar de findElement()->click(), esperamos a que el botón 
        // sea CLICABLE, para evitar la "race condition" con la animación del modal.
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('boton_registrar')
            )
        )->click();

        // 4. Esperar que el modal de registro (#modal_habitantes) aparezca
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_habitantes')
            )
        );
        
        // 5. Esperar por el título "Registrar Habitante"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('titulo_modal_habitantes'), 'Registrar Habitante'
            )
        );

        // -----------------------------------------------------------------
        // PASO 5: Rellenar Formulario de Registro
        // -----------------------------------------------------------------
        $this->driver->findElement(WebDriverBy::id('cedula'))
            ->sendKeys($this->test_cedula);
        $this->driver->findElement(WebDriverBy::id('nombre'))
            ->sendKeys('Selenium');
        $this->driver->findElement(WebDriverBy::id('apellido'))
            ->sendKeys('Registro');
        $this->driver->findElement(WebDriverBy::id('fecha_nacimiento'))
            ->sendKeys('01/01/1990');
        $this->driver->findElement(WebDriverBy::id('telefono'))
            ->sendKeys('04121234567');
        $this->driver->findElement(WebDriverBy::id('correo'))
            ->sendKeys($this->test_correo);

        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('sexo'))))
            ->selectByValue('Masculino');
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('tipo_vinculo'))))
            ->selectByValue('Habitante');
        
        $this->driver->findElement(WebDriverBy::id('boton_formulario_habitantes'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Manejar Alertas y Verificar
        // -----------------------------------------------------------------
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); 

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente'
            ),
            "La alerta de 'Éxito' no apareció o el texto no coincidía."
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

        // Verificar que la nueva cédula existe en la tabla
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $this->test_cedula
            ),
            "FALLO: El nuevo habitante (Cedula: " . $this->test_cedula . ") no se encontró en la tabla después de registrar."
        );

        $this->assertTrue(true, "Registro de habitante exitoso y verificado.");
    }
}