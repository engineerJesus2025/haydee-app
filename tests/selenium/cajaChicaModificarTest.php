<?php
// tests/Selenium/cajaChicaModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys; // Importante para el borrado seguro

class cajaChicaModificarTest extends TestCase
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

    public function testModificarCajaChicaUI()
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

        $this->driver->get('http://localhost/haydee-app/?pagina=inicio&accion=inicio');
        $this->driver->wait(10, 500)->until(fn() => $this->driver->findElement(WebDriverBy::id('contenido')));

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


        $this->driver->get('http://localhost/haydee-app/?pagina=caja_chica&accion=inicio');

        // 3.2. Esperamos a que aparezca el título H2
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR CAJA CHICA')]"));
            }
        );

        // -----------------------------------------------------------------
        // PASO 3: Seleccionar el PRIMER gasto de caja para editar
        // -----------------------------------------------------------------
        
        // Buscamos el botón de editar en la primera fila de la tabla
        $editButtonSelector = WebDriverBy::xpath("//table[@id='tabla_registros_sistema']/tbody/tr[1]//button[@title='Editar']");
        
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($editButtonSelector)
        );
        $editButton->click();

        // -----------------------------------------------------------------
        // PASO 4: Esperar Carga de Datos (AJAX)
        // -----------------------------------------------------------------

        // 4.1 Esperar a que el input 'concepto' sea visible
        $descAnioSelector = WebDriverBy::id('concepto');
        $descAnioField = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($descAnioSelector)
        );

        // 4.2 CRUCIAL: Esperar a que el campo tenga datos cargados
        // Esto confirma que el AJAX terminó y los datos viejos están listos para ser editados
        $this->driver->wait(5)->until(function ($driver) use ($descAnioSelector) {
            return $driver->findElement($descAnioSelector)->getAttribute('value') != '';
        });

        // -----------------------------------------------------------------
        // PASO 5: Modificar Solo las Descripciones
        // -----------------------------------------------------------------

        $nueva_descripcion = 'gasto Modificado ' . rand(1000, 9999);

        // 5.1 Modificar Descripción General (ID: descripcion)
        $descAnioField->click();
        $descAnioField->clear();
        
        // Borrado seguro si clear() falla
        if ($descAnioField->getAttribute('value') != '') {
            $descAnioField->sendKeys(array(WebDriverKeys::CONTROL, 'a'));
            $descAnioField->sendKeys(WebDriverKeys::BACKSPACE);
        }
        $descAnioField->sendKeys($nueva_descripcion);

        // -----------------------------------------------------------------
        // PASO 6: Guardar Cambios
        // -----------------------------------------------------------------

        $this->driver->findElement(WebDriverBy::id('boton_gasto_caja'))->click();

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
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha modificado exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 8: Verificación Final
        // -----------------------------------------------------------------
        
        // Esperar que cierre el popup
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // 4.1 Buscar por la descripción única
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_registros_sistema_filter']//input[@type='search']")
        );
        $searchInput->clear();
        $searchInput->sendKeys($test_descripcion);
        

        // Verificar que la nueva descripción general aparezca en la tabla
        $tableSelector = WebDriverBy::id('tabla_registros_sistema');
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $nueva_descripcion
            ),
            "FALLO: El concepto modificado ($nueva_descripcion) no se encontró en la tabla."
        );

        $this->assertTrue(true, "Modificación de concepto de gasto exitoso.");
    }
}