<?php
// tests/Selenium/anioFiscalEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class anioFiscalEliminarTest extends TestCase
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

    public function testEliminarAnioFiscalUI()
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
        // PASO 2: Navegar a Años fiscales
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=anio_fiscal&accion=inicio');

        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR AÑOS FISCALES')]")
            )
        );
       $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_anio_fiscal']//h4[contains(text(), 'Cargando...')]");


        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: CREAR un año fiscal (Para luego eliminarlo)
        // -----------------------------------------------------------------
        
        $unique_id = rand(10000, 99999);
        $test_descripcion = 'anio Eliminar ' . $unique_id; // Nombre único para buscar
        $test_monto = '25.00';
        $test_fecha = date('d-m-Y');
        
        // 3.1 Clic en botón Nueva Solicitud
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_anio_fiscal']"))->click();

        // 3.2 Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_anio_fiscal'))
        );

        // 1. Fecha del detalle
        // Usamos CSS Selector porque es una clase dentro del array de detalles
        $this->driver->findElement(WebDriverBy::cssSelector('#fecha_inicio'))->sendKeys($test_fecha);


        // 2. Descripción
        $this->driver->findElement(WebDriverBy::id('descripcion'))->sendKeys($test_descripcion);

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
            WebDriverBy::xpath("//div[@id='tabla_anio_fiscal_filter']//input[@type='search']")
        );
        $searchInput->clear();
        $searchInput->sendKeys($test_descripcion);

        // 4.2 Esperar a que aparezca el botón ELIMINAR en la fila correcta
        // Buscamos la fila (tr) que contiene el texto de la descripción y dentro el botón eliminar
        $botonEliminarXPath = "//table[@id='tabla_anio_fiscal']/tbody/tr[contains(., '$test_descripcion')]//button[contains(@class, 'eliminar')]";
        
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
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro ha sido eliminado correctamente')
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
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_anio_fiscal']/tbody");

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados"
            ),
            "FALLO: El año eliminado ($test_descripcion) sigue apareciendo en la tabla."
        );

        $this->assertTrue(true, "Ciclo completo: año creado y eliminado exitosamente.");
    }
}