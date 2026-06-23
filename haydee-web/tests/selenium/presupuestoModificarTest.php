<?php
// tests/Selenium/presupuestoModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys; // Importante para el borrado seguro

class presupuestoModificarTest extends TestCase
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

    public function testModificarPresupuestoUI()
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


        $this->driver->get('http://localhost/haydee-app/?pagina=presupuesto&accion=inicio');

        // 3.2. Esperamos a que aparezca el título H2
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR PRESUPUESTOS MENSUALES')]"));
            }
        );

        // -----------------------------------------------------------------
        // PASO 3: Seleccionar el PRIMER presupuesto para editar
        // -----------------------------------------------------------------
        
        // Buscamos el botón de editar en la primera fila de la tabla
        $editButtonSelector = WebDriverBy::xpath("//table[@id='tabla_presupuesto']/tbody/tr[1]//button[@title='Editar']");
        
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($editButtonSelector)
        );
        $editButton->click();

        // -----------------------------------------------------------------
        // PASO 4: Esperar Carga de Datos (AJAX)
        // -----------------------------------------------------------------

        // 4.1 Esperar a que el input 'observacion' sea visible
        $nombrepresupuestoSelector = WebDriverBy::id('observacion');
        $nombrepresupuestoField = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($nombrepresupuestoSelector)
        );

        // 4.2 CRUCIAL: Esperar a que el campo tenga datos cargados
        // Esto confirma que el AJAX terminó y los datos viejos están listos para ser editados
        $this->driver->wait(5)->until(function ($driver) use ($nombrepresupuestoSelector) {
            return $driver->findElement($nombrepresupuestoSelector)->getAttribute('value') != '';
        });

        // -----------------------------------------------------------------
        // PASO 5: Modificar Solo las Descripciones
        // -----------------------------------------------------------------
        $unique_id = rand(1000, 9999);
        $test_observacion = 'presupuesto modificado ' . $unique_id;

        // 5.1 Modificar nombre de presupuesto (ID: nombre)
        $nombrepresupuestoField->click();
        $nombrepresupuestoField->clear();
        
        // Borrado seguro si clear() falla
        if ($nombrepresupuestoField->getAttribute('value') != '') {
            $nombrepresupuestoField->sendKeys(array(WebDriverKeys::CONTROL, 'a'));
            $nombrepresupuestoField->sendKeys(WebDriverKeys::BACKSPACE);
        }
        $nombrepresupuestoField->sendKeys($test_observacion);

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
        $this->driver->wait(100)->until(
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

        // Verificar que la nueva descripción general aparezca en la tabla
        $tableSelector = WebDriverBy::id('tabla_presupuesto');
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $test_observacion
            ),
            "FALLO: la observacion modificada ($test_observacion) no se encontró en la tabla."
        );

        $this->assertTrue(true, "Modificación de observacion de presupuesto exitosa.");
    }
}