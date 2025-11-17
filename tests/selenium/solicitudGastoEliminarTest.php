<?php
// tests/Selenium/solicitudesEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class solicitudGastoEliminarTest extends TestCase
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

    public function testEliminarSolicitudExitosoUI()
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
        // PASO 3: CREAR una Solicitud (Para luego eliminarla)
        // -----------------------------------------------------------------

        // 3.1 Abrir Modal
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_solicitud_gasto']"))->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_solicitud_gasto'))
        );

        // 3.2 Seleccionar Presupuesto (Lógica AJAX)
        // Esperamos opciones
        $this->driver->wait(5)->until(function($driver) {
            return count($driver->findElement(WebDriverBy::id('selector_mes'))->findElements(WebDriverBy::tagName('option'))) > 1;
        });

        $selectMes = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('selector_mes')));
        $selectMes->selectByIndex(1); 

        $selectAnio = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('selector_anio')));
        $selectAnio->selectByIndex(1);

        // Forzar evento change para activar buscarPresupuesto()
        $this->driver->executeScript("
            var event = new Event('change');
            document.getElementById('selector_anio').dispatchEvent(event);
        ");

        // Esperar campos ocultos
        $camposOcultos = WebDriverBy::id('campos_formulario_completo');
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($camposOcultos)
        );

        // 3.3 Llenar datos únicos
        $unique_id = rand(10000, 99999);
        $test_descripcion = 'Solicitud Eliminar ' . $unique_id;
        $test_monto = '10.00';

        $this->driver->findElement(WebDriverBy::id('fecha'))->sendKeys(date('d-m-Y'));
        $this->driver->findElement(WebDriverBy::id('nombre'))->sendKeys('Usuario Test Eliminar');
        $this->driver->findElement(WebDriverBy::id('monto_estimado'))->sendKeys($test_monto);
        
        $selectPrioridad = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('prioridad')));
        $selectPrioridad->selectByValue('3'); // Baja

        $this->driver->findElement(WebDriverBy::id('descripcion'))->sendKeys($test_descripcion);

        // 3.4 Guardar
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // Confirmar creación
        $this->driver->wait(10)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?'));
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        
        // Cerrar éxito creación
        $this->driver->wait(10)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), 'Atencion'));
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // -----------------------------------------------------------------
        // PASO 4: BUSCAR Y ELIMINAR
        // -----------------------------------------------------------------

        // 4.1 Buscar por la descripción única
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_solicitud_gasto_filter']//input[@type='search']")
        );
        $searchInput->clear();
        $searchInput->sendKeys($test_descripcion);

        // 4.2 Esperar botón Eliminar en la fila correcta
        // Buscamos la fila que contiene la descripción única Y el botón eliminar
        $botonEliminarXPath = "//table[@id='tabla_solicitud_gasto']/tbody/tr[contains(., '$test_descripcion')]//button[contains(@class, 'eliminar')]";
        
        $botonEliminar = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath($botonEliminarXPath))
        );

        usleep(500000); // Pausa de estabilidad

        // 4.3 Clic en Eliminar
        $botonEliminar->click();

        // -----------------------------------------------------------------
        // PASO 5: Confirmar Eliminación
        // -----------------------------------------------------------------

        // 1. Alerta "¿Estás seguro?" (Tu JS muestra este título y texto "¿Deseas eliminar esta solicitud?")
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Alerta Éxito ("Atencion" -> "La operacion se ha realizado correctamente")
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), 'Atencion')
        );
        // Validamos el contenido del mensaje también
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'correctamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Verificación Final (ASSERT)
        // -----------------------------------------------------------------

        // 6.1 Esperar cierre alerta
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // 6.2 Verificar tabla vacía
        // Como filtramos por un ID único que acabamos de borrar, la tabla debe decir "No se encontraron resultados"
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_solicitud_gasto']/tbody");

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados"
            ),
            "FALLO: La solicitud eliminada ($test_descripcion) sigue apareciendo en la tabla."
        );

        $this->assertTrue(true, "Ciclo completo: Solicitud creada y eliminada exitosamente.");
    }
}