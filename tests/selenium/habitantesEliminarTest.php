<?php
// tests/Selenium/habitantesEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class habitantesEliminarTest extends TestCase
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
        $this->test_correo = "delete" . rand(1000, 9999) . "@test.com";
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

    public function testEliminarHabitanteUI()
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
        // PASO 4: Registrar un Habitante para Eliminar
        // -----------------------------------------------------------------

        // 1. Esperar modal y tabla de habitantes
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

        // 2. Clic en "Nuevo Habitante"
        $this->driver->findElement(WebDriverBy::id('boton_registrar'))->click();

        // 3. Esperar modal de registro
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_habitantes')
            )
        );

        // 4. Llenar formulario
        $this->driver->findElement(WebDriverBy::id('cedula'))
            ->sendKeys($this->test_cedula);
        $this->driver->findElement(WebDriverBy::id('nombre'))
            ->sendKeys('Temporal');
        $this->driver->findElement(WebDriverBy::id('apellido'))
            ->sendKeys('Para Borrar');
        $this->driver->findElement(WebDriverBy::id('fecha_nacimiento'))
            ->sendKeys('01/01/1990');
        $this->driver->findElement(WebDriverBy::id('telefono'))
            ->sendKeys('04129876543');
        $this->driver->findElement(WebDriverBy::id('correo'))
            ->sendKeys($this->test_correo);
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('sexo'))))
            ->selectByValue('Femenino');
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('tipo_vinculo'))))
            ->selectByValue('Habitante');
        $this->driver->findElement(WebDriverBy::id('boton_formulario_habitantes'))->click();

        // 5. Manejar Alertas de Registro
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'registrados correctamente'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('modal_habitantes')
            )
        );
        
        // 6. Esperar que la tabla se refresque con el nuevo registro
         $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_habitantes_processing')
            )
        );
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody"),
                $this->test_cedula
            )
        );

        // -----------------------------------------------------------------
        // PASO 5: Buscar y Eliminar el Habitante Creado
        // -----------------------------------------------------------------
        
        // 1. Ubicar la fila (tr) que contiene la cédula única
        $rowXPath = "//table[@id='tabla_habitantes']/tbody/tr[contains(., '" . $this->test_cedula . "')]";
        
        // 2. Ubicar el botón de eliminar DENTRO de esa fila
        $deleteButtonXPath = $rowXPath . "//button[@title='Eliminar Habitante']";

        // 3. Clic en el botón de eliminar
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath($deleteButtonXPath)
            )
        )->click();


        // -----------------------------------------------------------------
        // PASO 6: Manejar Alertas de Eliminación y Verificar
        // -----------------------------------------------------------------
        
        // 1. Alerta de Confirmación
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // "Si, Eliminar"

        // 2. Alerta de Éxito
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'eliminado correctamente'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // "Aceptar"
        
        // 3. Esperar que la alerta desaparezca
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::className('swal2-popup')
            )
        );

        // 4. Esperar que la tabla se refresque
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_habitantes_processing')
            )
        );

        // 5. Verificación Final: El texto de la cédula NO debe estar en la tabla
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::not(
                WebDriverExpectedCondition::elementTextContains($tableBodySelector, $this->test_cedula)
            ),
            "FALLO: El habitante eliminado (".$this->test_cedula.") todavía se encuentra en la tabla."
        );

        $this->assertTrue(true, "Eliminación de habitante exitosa: Creado, eliminado y verificado.");
    }
}