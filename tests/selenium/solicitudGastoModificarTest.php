<?php
// tests/Selenium/solicitudesModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys; // Necesario para borrado seguro

class solicitudGastoModificarTest extends TestCase
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

    public function testModificarSolicitudExitosoUI()
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
        // PASO 2: Navegar a Solicitudes
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=solicitud_gasto_controlador.php&accion=inicio');

        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR SOLICITUD DE GASTOS')]"))
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_solicitud_gasto']//td[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Seleccionar la PRIMERA solicitud para editar
        // -----------------------------------------------------------------

        // Buscamos el botón con la clase 'editar' dentro de la primera fila
        $editButtonSelector = WebDriverBy::xpath("//table[@id='tabla_solicitud_gasto']/tbody/tr[1]//button[contains(@class, 'editar')]");
        
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($editButtonSelector)
        );
        $editButton->click();

        // -----------------------------------------------------------------
        // PASO 4: Esperar Carga de Datos (AJAX)
        // -----------------------------------------------------------------

        // 4.1 Esperar visibilidad del formulario
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_solicitud_gasto'))
        );

        // 4.2 CRUCIAL: Esperar a que el JS llene los campos.
        // Verificamos que el campo 'nombre' tenga valor antes de intentar editarlo.
        $nombreInputSelector = WebDriverBy::id('nombre');
        $this->driver->wait(10)->until(function ($driver) use ($nombreInputSelector) {
            return $driver->findElement($nombreInputSelector)->getAttribute('value') != '';
        });

        // -----------------------------------------------------------------
        // PASO 5: Modificar Datos
        // -----------------------------------------------------------------

        $nuevo_nombre = 'Solicitante Modificado ' . rand(100, 999);
        $nueva_descripcion = 'Descripcion act via Selenium ' . rand(100, 999);

        // 5.1 Modificar Nombre
        $nombreField = $this->driver->findElement($nombreInputSelector);
        $nombreField->click();
        $nombreField->clear();
        // Borrado seguro (Ctrl+A -> Backspace) por si el JS interfiere
        if ($nombreField->getAttribute('value') != '') {
            $nombreField->sendKeys(array(WebDriverKeys::CONTROL, 'a'));
            $nombreField->sendKeys(WebDriverKeys::BACKSPACE);
        }
        $nombreField->sendKeys($nuevo_nombre);

        // 5.2 Modificar Descripción
        $descripcionField = $this->driver->findElement(WebDriverBy::id('descripcion'));
        $descripcionField->click();
        $descripcionField->clear();
        if ($descripcionField->getAttribute('value') != '') {
            $descripcionField->sendKeys(array(WebDriverKeys::CONTROL, 'a'));
            $descripcionField->sendKeys(WebDriverKeys::BACKSPACE);
        }
        $descripcionField->sendKeys($nueva_descripcion);

        // NOTA: No modificamos el MONTO para evitar problemas con el presupuesto disponible,
        // ya que tu JS tiene validaciones complejas allí (disponible + original). 
        // Modificar solo textos es más seguro para el test de UI.

        // -----------------------------------------------------------------
        // PASO 6: Guardar Cambios
        // -----------------------------------------------------------------

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // -----------------------------------------------------------------
        // PASO 7: Manejar Alertas
        // -----------------------------------------------------------------

        // 1. Confirmación "¿Estás seguro?"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Éxito (Tu función consulta_completada() muestra titulo "Atencion")
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), 'Atencion')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 8: Verificación Final
        // -----------------------------------------------------------------
        
        // Esperar cierre del popup
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // Verificar que la nueva descripción aparezca en la tabla
        $tableSelector = WebDriverBy::id('tabla_solicitud_gasto');
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $nueva_descripcion
            ),
            "FALLO: La descripción modificada ($nueva_descripcion) no se encontró en la tabla."
        );

        $this->assertTrue(true, "Modificación de solicitud exitosa y verificada.");
    }
}