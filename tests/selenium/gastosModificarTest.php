<?php
// tests/Selenium/gastosModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys; // Importante para el borrado seguro

class gastosModificarTest extends TestCase
{
    private $driver;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [
            'browserName' => 'MicrosoftEdge'
        ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    protected function tearDown(): void
    {
        $this->driver->quit(); 
    }

    public function testModificarDescripcionGastoExitosoUI()
    {
        // -----------------------------------------------------------------
        // PASO 1: Login y Navegación
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/index.php');

        $this->driver->findElement(WebDriverBy::id('correo_login'))->sendKeys('administrador@gmail.com');
        $this->driver->findElement(WebDriverBy::id('contra'))->sendKeys('12345'); 

        $this->driver->executeScript("document.getElementById('form-login').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"operacion\" value=\"entrar\">');");
        $this->driver->executeScript("document.getElementById('form-login').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"mantener_sesion\" value=\"false\">');");
        $this->driver->findElement(WebDriverBy::id('form-login'))->submit();
        sleep(1); 

        $this->driver->get('http://localhost/haydee-app/?pagina=inicio_controlador.php&accion=inicio');
        $this->driver->wait(10, 500)->until(fn() => $this->driver->findElement(WebDriverBy::id('contenido')));

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Gastos
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=gastos_controlador.php&accion=inicio');
        
        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR GASTOS')]"))
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_gastos']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Seleccionar el PRIMER gasto para editar
        // -----------------------------------------------------------------
        
        // Buscamos el botón de editar en la primera fila de la tabla
        $editButtonSelector = WebDriverBy::xpath("//table[@id='tabla_gastos']/tbody/tr[1]//button[@title='Editar']");
        
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($editButtonSelector)
        );
        $editButton->click();

        // -----------------------------------------------------------------
        // PASO 4: Esperar Carga de Datos (AJAX)
        // -----------------------------------------------------------------

        // 4.1 Esperar a que el input 'descripcion_gasto' sea visible
        $descGastoSelector = WebDriverBy::id('descripcion_gasto');
        $descGastoField = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($descGastoSelector)
        );

        // 4.2 CRUCIAL: Esperar a que el campo tenga datos cargados
        // Esto confirma que el AJAX terminó y los datos viejos están listos para ser editados
        $this->driver->wait(5)->until(function ($driver) use ($descGastoSelector) {
            return $driver->findElement($descGastoSelector)->getAttribute('value') != '';
        });

        // -----------------------------------------------------------------
        // PASO 5: Modificar Solo las Descripciones
        // -----------------------------------------------------------------

        $nueva_descripcion_gasto = 'Gasto Modificado ' . rand(1000, 9999);
        $nueva_descripcion_detalle = 'Detalle actualizado via Selenium';

        // 5.1 Modificar Descripción General (ID: descripcion_gasto)
        $descGastoField->click();
        $descGastoField->clear();
        
        // Borrado seguro si clear() falla
        if ($descGastoField->getAttribute('value') != '') {
            $descGastoField->sendKeys(array(WebDriverKeys::CONTROL, 'a'));
            $descGastoField->sendKeys(WebDriverKeys::BACKSPACE);
        }
        $descGastoField->sendKeys($nueva_descripcion_gasto);

        // 5.2 Modificar Descripción del Detalle (Clase: .descripcion_detalle)
        // Al usar cssSelector('.clase'), Selenium selecciona el primero que encuentra (el primer detalle)
        $descDetalleField = $this->driver->findElement(WebDriverBy::cssSelector('.descripcion_detalle'));
        
        $descDetalleField->click();
        $descDetalleField->clear();
        // Borrado seguro para el detalle también
        if ($descDetalleField->getAttribute('value') != '') {
            $descDetalleField->sendKeys(array(WebDriverKeys::CONTROL, 'a'));
            $descDetalleField->sendKeys(WebDriverKeys::BACKSPACE);
        }
        $descDetalleField->sendKeys($nueva_descripcion_detalle);

        // -----------------------------------------------------------------
        // PASO 6: Guardar Cambios
        // -----------------------------------------------------------------

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // -----------------------------------------------------------------
        // PASO 7: Manejar Alertas
        // -----------------------------------------------------------------

        // Confirmación
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // Éxito
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El gasto se ha modificado exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 8: Verificación Final
        // -----------------------------------------------------------------
        
        // Esperar que cierre el popup
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // Verificar que la nueva descripción general aparezca en la tabla
        $tableSelector = WebDriverBy::id('tabla_gastos');
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $nueva_descripcion_gasto
            ),
            "FALLO: La descripción modificada ($nueva_descripcion_gasto) no se encontró en la tabla."
        );

        $this->assertTrue(true, "Modificación de descripciones de Gasto exitosa.");
    }
}