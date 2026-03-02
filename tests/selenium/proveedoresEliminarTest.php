<?php
// tests/Selenium/proveedoresEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class proveedoresEliminarTest extends TestCase
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

    public function testEliminarProveedorExitosoUI()
    {
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

        $this->driver->get('http://localhost/haydee-app/?pagina=inicio&accion=inicio');
        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::id('contenido'))
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Proveedores y esperar a que la tabla cargue
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=proveedores&accion=inicio');

        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR PROVEEDORES')]"))
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_proveedores']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: REGISTRAR un Proveedor (Para luego eliminarlo)
        // -----------------------------------------------------------------
        usleep(100000); 
        // 3.1 Generar datos únicos
        $unique_part = rand(100000, 999999);
        $test_rif = 'v' . $unique_part;         // Ej: "v839201"
        $test_nombre = 'Proveedor Eliminar Selenium'; // Nombre único para buscar fácil
        $test_servicio = 'Servicio Temporal';
        $test_direccion = 'Direccion Temporal';

        // 3.2 Abrir modal
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_proveedores']"))
            ->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('titulo_modal'))
        );

        // 3.3 Rellenar Formulario
        $this->driver->findElement(WebDriverBy::id('nombre_proveedor'))->sendKeys($test_nombre);
        $this->driver->findElement(WebDriverBy::id('rif'))->sendKeys($test_rif);
        $this->driver->findElement(WebDriverBy::id('servicio'))->sendKeys($test_servicio);
        $this->driver->findElement(WebDriverBy::id('direccion'))->sendKeys($test_direccion);

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // 3.4 Confirmar Creación (Alertas)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        
        // Esperar a que desaparezca la alerta de éxito antes de intentar interactuar con la tabla
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // -----------------------------------------------------------------
        // PASO 4: BUSCAR Y ELIMINAR el Proveedor creado
        // -----------------------------------------------------------------

        // 4.1 Buscar el proveedor recién creado
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_proveedores_filter']//input[@type='search']")
        );
        $searchInput->clear();
        $searchInput->sendKeys($test_nombre);

        // 4.2 Esperar a que aparezca el botón ELIMINAR en la fila correcta
        // Usamos XPath para buscar la fila (tr) que contiene el texto ($test_nombre) 
        // y dentro el botón eliminar.
        $botonEliminarXPath = "//table[@id='tabla_proveedores']/tbody/tr[contains(., '$test_nombre')]//button[contains(@class, 'eliminar')]";
        
        $botonEliminar = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath($botonEliminarXPath))
        );

        // Pequeña pausa para asegurar que DataTables terminó de redibujar
        usleep(500000); 

        // 4.3 Clic en eliminar
        $botonEliminar->click();

        // -----------------------------------------------------------------
        // PASO 5: Confirmar la Eliminación (Alertas)
        // -----------------------------------------------------------------

        // 1. Alerta "¿Estás seguro de eliminar?"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), 'Atención')
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

        // 6.1 Esperar que cierre la alerta
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // 6.2 Verificar que la tabla diga "No se encontraron resultados"
        // (Como el filtro sigue activo con el nombre, la tabla debe quedar vacía)
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_proveedores']/tbody");

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados"
            ),
            "FALLO: El proveedor ($test_nombre) sigue apareciendo en la tabla después de eliminar."
        );

        $this->assertTrue(true, "Ciclo completo: Proveedor creado y eliminado exitosamente.");
    }
}