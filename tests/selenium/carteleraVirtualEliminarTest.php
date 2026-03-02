<?php
// tests/Selenium/carteleraEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect; 

class carteleraVirtualEliminarTest extends TestCase
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

    public function testEliminarPublicacionExitosoUI()
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
        // PASO 2: Navegar a Cartelera Virtual
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=cartelera_virtual&accion=inicio');

        // Esperar título
        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR CARTELERA VIRTUAL')]"))
        );

        // Esperar carga de tabla
        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_cartelera_virtual']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: REGISTRAR una Publicación (Para luego eliminarla)
        // -----------------------------------------------------------------
        
        // 3.1 Generar datos únicos
        $unique_code = rand(10000, 99999);
        $test_titulo = 'Aviso Eliminar ' . $unique_code; // Título único
        $test_descripcion = 'Esta publicación será eliminada automáticamente por Selenium.';
        $test_fecha = date('d-m-Y');

        // 3.2 Abrir modal
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_cartelera']"))->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('titulo_modal'))
        );

        // 3.3 Llenar formulario
        $this->driver->findElement(WebDriverBy::id('titulo'))->sendKeys($test_titulo);
        $this->driver->findElement(WebDriverBy::id('descripcion'))->sendKeys($test_descripcion);
        $this->driver->findElement(WebDriverBy::id('fecha'))->sendKeys($test_fecha);
        
        // Seleccionar prioridad
        $selectPrioridad = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('prioridad')));
        $selectPrioridad->selectByValue('3'); // Baja

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // 3.4 Confirmar registro
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'La operacion se ha realizado correctamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        
        // Esperar a que desaparezca el popup de éxito
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // -----------------------------------------------------------------
        // PASO 4: BUSCAR y ELIMINAR la Publicación creada
        // -----------------------------------------------------------------

        // 4.1 Buscar la publicación por título
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_cartelera_virtual_filter']//input[@type='search']")
        );
        $searchInput->clear();
        $searchInput->sendKeys($test_titulo);

        // 4.2 Esperar a que aparezca el botón ELIMINAR en la fila correcta
        // Buscamos la fila que contiene el título Y que tiene un botón de eliminar dentro
        $botonEliminarXPath = "//table[@id='tabla_cartelera_virtual']/tbody/tr[contains(., '$test_titulo')]//button[contains(@class, 'eliminar')]";
        
        // O si el botón eliminar usa title="Eliminar" en lugar de clase:
        // $botonEliminarXPath = "//table[@id='tabla_cartelera_virtual']/tbody/tr[contains(., '$test_titulo')]//button[@title='Eliminar']";

        $botonEliminar = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath($botonEliminarXPath))
        );

        // Pequeña pausa para estabilización del DOM tras el filtrado
        usleep(500000); 

        // 4.3 Clic en eliminar
        $botonEliminar->click();

        // -----------------------------------------------------------------
        // PASO 5: Confirmar Eliminación
        // -----------------------------------------------------------------

        // 1. Alerta "¿Estás seguro?"
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
        // PASO 6: Verificación Final
        // -----------------------------------------------------------------

        // 6.1 Esperar cierre de alerta
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // 6.2 Verificar que la tabla esté vacía (filtro activo + elemento borrado = "No se encontraron resultados")
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_cartelera_virtual']/tbody");

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados"
            ),
            "FALLO: La publicación eliminada ($test_titulo) sigue apareciendo en la tabla."
        );

        $this->assertTrue(true, "Ciclo completo: Publicación creada y eliminada exitosamente.");
    }
}