<?php
// tests/Selenium/gastosEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class gastosEliminarTest extends TestCase
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

    public function testEliminarGastoExitosoUI()
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
        // PASO 2: Navegar a Gastos
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=gastos&accion=inicio');

        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR GASTOS')]"))
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_gastos']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: CREAR un Gasto (Para luego eliminarlo)
        // -----------------------------------------------------------------
        
        $unique_id = rand(10000, 99999);
        $test_descripcion_gasto = 'Gasto Eliminar ' . $unique_id; // Nombre único para buscar
        $test_monto = '25.00';
        
        // Abrir Modal
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_gastos']"))->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_gastos'))
        );

        // Llenar Cabecera
        $selectTipo = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('tipo')));
        $selectTipo->selectByValue('variable');

        $selectTipoGasto = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('tipo_gasto')));
        $selectTipoGasto->selectByIndex(1);

        $this->driver->findElement(WebDriverBy::id('descripcion_gasto'))->sendKeys($test_descripcion_gasto);

        $selectProveedor = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('proveedor')));
        $selectProveedor->selectByIndex(1);

        $selectSolicitud = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('solicitud')));
        $selectSolicitud->selectByIndex(1);

        // Llenar Detalle (Efectivo)
        $this->driver->findElement(WebDriverBy::cssSelector('.fecha_detalle'))->sendKeys(date('d-m-Y'));
        
        $selectMetodo = new WebDriverSelect($this->driver->findElement(WebDriverBy::cssSelector('.metodo_pago')));
        $selectMetodo->selectByValue('Efectivo');

        $this->driver->findElement(WebDriverBy::cssSelector('.monto'))->sendKeys($test_monto);
        $this->driver->findElement(WebDriverBy::cssSelector('.descripcion_detalle'))->sendKeys('Detalle temporal');

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
        // PASO 4: BUSCAR Y ELIMINAR el Gasto creado
        // -----------------------------------------------------------------

        // 4.1 Buscar por la descripción única
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_gastos_filter']//input[@type='search']")
        );
        $searchInput->clear();
        $searchInput->sendKeys($test_descripcion_gasto);

        // 4.2 Esperar a que aparezca el botón ELIMINAR en la fila correcta
        // Buscamos la fila (tr) que contiene el texto de la descripción y dentro el botón eliminar
        $botonEliminarXPath = "//table[@id='tabla_gastos']/tbody/tr[contains(., '$test_descripcion_gasto')]//button[contains(@class, 'eliminar')]";
        
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
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'La operacion se ha realizado correctamente')
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
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_gastos']/tbody");

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados"
            ),
            "FALLO: El gasto eliminado ($test_descripcion_gasto) sigue apareciendo en la tabla."
        );

        $this->assertTrue(true, "Ciclo completo: Gasto creado y eliminado exitosamente.");
    }
}