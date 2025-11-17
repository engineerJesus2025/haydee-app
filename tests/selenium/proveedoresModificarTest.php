<?php
// tests/Selenium/bancosModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;

class proveedoresModificarTest extends TestCase
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

    public function testModificarBancoExitosoUI()
    {
        // ... (Pasos 1 y 2 - Login y Navegación - sin cambios) ...

        // -----------------------------------------------------------------
        // PASO 1: Login y Navegación al Dashboard
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
                return $this->driver->findElement(WebDriverBy::id('contenido'));
            }
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Proveedores y esperar a que la tabla cargue
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=proveedores_controlador.php&accion=inicio');
        
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR PROVEEDORES')]"));
            }
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_proveedor']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Hacer clic en el primer botón de "Editar"
        // -----------------------------------------------------------------
        
        $editButtonSelector = WebDriverBy::xpath("//table[@id='tabla_proveedores']/tbody/tr[1]//button[@title='Editar']");
        
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($editButtonSelector)
        );
        $editButton->click();

       // -----------------------------------------------------------------
        // PASO 4: Esperar Modal, Modificar y Enviar (CORREGIDO)
        // -----------------------------------------------------------------

        // 1. Esperar a que el INPUT sea visible (es más seguro que esperar el título)
        $inputSelector = WebDriverBy::id('nombre_proveedor');
        $nombreField = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($inputSelector)
        );

        // 2. CRUCIAL: Esperar a que el input tenga el valor "viejo" cargado por AJAX.
        // Esto evita borrar antes de tiempo.
        $this->driver->wait(5)->until(function ($driver) use ($inputSelector) {
            $element = $driver->findElement($inputSelector);
            return $element->getAttribute('value') != '';
        });

        $nuevo_nombre_proveedor = 'Proveedor Modificado Selenium';

        // 3. Borrado Robust: Click -> Clear -> (Si falla) Ctrl+A + Backspace
        $nombreField->click();
        $nombreField->clear();

        // Verificación de seguridad: Si clear() no borró todo (común en inputs con eventos JS)
        if ($nombreField->getAttribute('value') != '') {
            // Para usar esto necesitas: use Facebook\WebDriver\WebDriverKeys; arriba
            $nombreField->sendKeys(array(\Facebook\WebDriver\WebDriverKeys::CONTROL, 'a'));
            $nombreField->sendKeys(\Facebook\WebDriver\WebDriverKeys::BACKSPACE);
        }

        // 4. Escribir el nuevo nombre
        $nombreField->sendKeys($nuevo_nombre_proveedor);
        
        // 5. Guardar
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // -----------------------------------------------------------------
        // PASO 5: Manejar las Alertas SweetAlert
        // -----------------------------------------------------------------

        // 1. Esperar la alerta de CONFIRMACIÓN
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Esperar la alerta de ÉXITO (Ahora sí debería aparecer)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'La operacion se ha realizado correctamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Verificación Final (ASSERT)
        // -----------------------------------------------------------------
        
        // 6.1. Esperar a que la alerta de éxito desaparezca
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // 6.2. Usamos el buscador de DataTables
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_proveedores_filter']//input[@type='search']")
        );

        // 6.3. Escribir el nuevo nombre del proveedor para buscarlo
        $searchInput->sendKeys($nuevo_nombre_proveedor);

        // 6.4. Esperamos a que el *cuerpo de la tabla* (tbody) contenga el texto.
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_proveedores']/tbody");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $nuevo_nombre_proveedor
            ),
            "FALLO: El nuevo nombre de proveedor ($nuevo_nombre_proveedor) no se encontró en el 'tbody' de la tabla después de modificar Y BUSCAR."
        );

        // 6.5. Si la espera anterior no falló, la prueba es un éxito.
        $this->assertTrue(true, "Modificación exitosa y verificada en la tabla mediante búsqueda.");
    }
}