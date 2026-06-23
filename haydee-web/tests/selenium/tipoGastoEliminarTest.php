<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class tipoGastoEliminarTest extends TestCase
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

    public function testEliminarTipoGastoExitosoUI()
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
            function () {
                return $this->driver->findElement(WebDriverBy::id('contenido'));
            }
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Tipos de Gastos y esperar a que la tabla cargue
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=tipo_gasto&accion=inicio');
        
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR TIPOS DE GASTO')]"));
            }
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_tipo_gasto']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Registrar un tipo de gasto nuevo para eliminarlo
        // -----------------------------------------------------------------
        
        // 3a. Generar datos únicos

        $nombre_tipo_gasto_a_eliminar = 'Tipo de Gasto Para Eliminar ';
        
        // 3b. Abrir modal
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_tipo_gasto']"))
            ->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('titulo_modal'))
        );

        // 3c. Llenar formulario
        $this->driver->findElement(WebDriverBy::id('nombre_tipo_gasto'))->sendKeys($nombre_tipo_gasto_a_eliminar);
        
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // -----------------------------------------------------------------
        // PASO 4: Buscar el tipo de gasto y hacer clic en "Eliminar"
        // -----------------------------------------------------------------

        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_tipo_gasto_filter']//input[@type='search']")
        );
        $searchInput->clear(); 
        $searchInput->sendKeys($nombre_tipo_gasto_a_eliminar);


        $xpathBotonEliminar = "//table[@id='tabla_tipo_gasto']/tbody/tr[contains(., '$nombre_tipo_gasto_a_eliminar')]//button[contains(@class, 'eliminar')]";
        
        $botonEliminar = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath($xpathBotonEliminar))
        );

        // Pequeña pausa de seguridad para animaciones de la tabla
        usleep(500000); // 0.5 segundos

        // 4c. Hacer clic
        $botonEliminar->click();


        // -----------------------------------------------------------------
        // PASO 5: Manejar las Alertas SweetAlert de Eliminación
        // -----------------------------------------------------------------

        // 1. Esperar la alerta de CONFIRMACIÓN
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Esperar la alerta de ÉXITO
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro ha sido eliminado correctamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Verificación Final (ASSERT)
        // -----------------------------------------------------------------
        
        // 1. Esperar a que la alerta de éxito desaparezca
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // 2. Definimos el selector del cuerpo de la tabla nuevamente
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_tipo_gasto']/tbody");

        // 3. Esperar a que la tabla muestre "No se encontraron resultados"
        // IMPORTANTE: DataTables a veces tarda un momento en refrescar tras la eliminación.
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados" 
            ),
            "FALLO: El registro eliminado todavía aparece o el mensaje 'No se encontraron resultados' no se mostró."
        );

        $this->assertTrue(true, "Eliminación exitosa y verificada.");
    }
}