<?php
// tests/Selenium/mensualidadEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class mensualidadEliminarTest extends TestCase
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

    public function testEliminarMensualidadUI()
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
        // PASO 2: Confirmar que estamos en el Dashboard
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=inicio_controlador.php&accion=inicio');

        // --- Corrección 1 (Línea 62) ---
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('contenido'));
            }
        );


        $this->driver->get('http://localhost/haydee-app/?pagina=mensualidad_controlador.php&accion=inicio');

        // 3.2. Esperamos a que aparezca el título H2
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR MENSUALIDAD')]"));
            }
        );

        // -----------------------------------------------------------------
        // PASO 3: CREAR una mensualidad (Para luego eliminarlo)
        // -----------------------------------------------------------------
        
        usleep(500000);

        $nuevo_numero = rand(1, 100);
        //valor de montos

        // 3.1 Clic en botón Nueva Solicitud
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_mensualidad']"))->click();

        // 3.2 Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('mes_select_asignar'))
        );

        // llenar datos
        $this->driver->findElement(WebDriverBy::cssSelector('#porcentaje_demora'))->sendKeys($nuevo_numero);

        $checkboxes = $this->driver->findElements(WebDriverBy::cssSelector("#tabla_mensualidad_asignar tbody tr td:nth-child(2) input[type='checkbox'")); //primer checkbox de cada fila

        foreach ($checkboxes as $checkbox) {
            $checkbox->click(); 
        }

        // Localiza el select por su ID (ajusta según tu HTML)
        $selectElement = $this->driver->findElement(WebDriverBy::id('mes_select_asignar'));

        // Crea el objeto WebDriverSelect
        $select = new WebDriverSelect($selectElement);

        // Obtener la opción seleccionada
        $selectedOption = $select->getFirstSelectedOption();

        // Obtener el texto visible
        $textoSeleccionado = $selectedOption->getText();

        // Guardar
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // Confirmar Creación
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        
        // Esperar cierre de alerta
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // -----------------------------------------------------------------
        // PASO 4: BUSCAR Y ELIMINAR el año creado
        // -----------------------------------------------------------------

        // 4.1 Buscar por la descripción única
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_mensualidad_filter']//input[@type='search']")
        );
        $searchInput->clear();
        $searchInput->sendKeys($textoSeleccionado);

        // 4.2 Esperar a que aparezca el botón ELIMINAR en la fila correcta
        // Buscamos la fila (tr) que contiene el texto de la descripción y dentro el botón eliminar
        $botonEliminarXPath = "//table[@id='tabla_mensualidad']/tbody/tr[contains(., '$textoSeleccionado')]//button[contains(@class, 'eliminar')]";
        
        $botonEliminar = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath($botonEliminarXPath))
        );

        // Pausa de seguridad para renderizado de tabla
        usleep(500000); 

        // 4.3 Clic en Eliminar
        $botonEliminar->click();

        // -----------------------------------------------------------------
        // PASO 5: Confirmar Eliminación
        // -----------------------------------------------------------------

        // 1. Alerta "¿Estás seguro de eliminar?"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Alerta "Eliminado correctamente"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'Se ha eliminado la mensualidad exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Verificación Final (ASSERT)
        // -----------------------------------------------------------------

        // 6.1 Esperar cierre de alerta
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // 6.2 Verificar que la tabla diga "No se encontraron resultados"
        // (Como el filtro sigue activo con el nombre único, la tabla debe quedar vacía)
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_mensualidad']/tbody");

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados"
            ),
            "FALLO: El mensualidad eliminado ($textoSeleccionado) sigue apareciendo en la tabla."
        );

        $this->assertTrue(true, "Ciclo completo: mensualidad creado y eliminado exitosamente.");
    }
}