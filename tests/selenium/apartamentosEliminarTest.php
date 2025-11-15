<?php
// tests/Selenium/apartamentosEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect; // <-- CORREGIDO: Namespace correcto

class apartamentosEliminarTest extends TestCase
{
    private $driver;
    private $test_nro_apartamento;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        // Generamos un Nro de Apartamento único
        $this->test_nro_apartamento = (string)rand(500, 999);
    }

    protected function tearDown(): void
    {
        $this->driver->quit(); 
    }

    public function testEliminarApartamentoUI()
    {
        // -----------------------------------------------------------------
        // PASO 1: Login y Navegación
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
        
        // <-- CORREGIDO: $this.driver
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('b_gastos'));
            }
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Apartamentos y esperar la carga
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=apartamentos_controlador.php&accion=inicio');
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR APARTAMENTOS')]")
            )
        );
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_apartamentos_processing')
            )
        );
        
        // -----------------------------------------------------------------
        // PASO 3: Registrar un Apartamento para Eliminar
        // -----------------------------------------------------------------
        $this->driver->findElement(
            WebDriverBy::xpath("//button[@data-bs-target='#modal_apartamentos']")
        )->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('titulo_modal'), 'Registrar Apartamento'
            )
        );

        // Llenar formulario
        $this->driver->findElement(WebDriverBy::id('nro_apartamento'))
            ->sendKeys($this->test_nro_apartamento);
        $this->driver->findElement(WebDriverBy::id('porcentaje_participacion'))
            ->sendKeys('1.1'); 
        
        // <-- CORREGIDO: (new Select)
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('gas'))))
            ->selectByValue('1');
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('agua'))))
            ->selectByValue('1');
        (new WebDriverSelect($this->driver->findElement(WebDriverBy::id('alquilado'))))
            ->selectByValue('2');

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // Manejar Alertas de Registro
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // <-- CORREGIDO: $this.driver
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        
        // <-- CORREGIDO: $this.driver
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::className('swal2-popup')
            )
        );

        // -----------------------------------------------------------------
        // PASO 4: Buscar y Eliminar el Apartamento Creado
        // -----------------------------------------------------------------
        // 1. Buscar el apto para aislarlo
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_apartamentos_filter']//input[@type='search']")
        );
        $texto_buscar = 'Nro: ' . $this->test_nro_apartamento;
        $searchInput->sendKeys($texto_buscar);

        // 2. Esperar a que la tabla lo muestre
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_apartamentos']/tbody");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $texto_buscar
            )
        );

        // 3. Clic en el botón de eliminar (ahora es el único en la fila 1)
        // (referencia al evento 'eliminar')
        
        // <-- CORREGIDO: $this.driver
        $this->driver->findElement(
            WebDriverBy::xpath("//table[@id='tabla_apartamentos']/tbody/tr[1]//button[contains(@class, 'eliminar')]")
        )->click();

        // -----------------------------------------------------------------
        // PASO 5: Manejar Alertas de Eliminación
        // -----------------------------------------------------------------
        // 1. Alerta de Confirmación
        // <-- CORREGIDO: $this.driver
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // "Si, Eliminar"

        // 2. Alerta de Éxito
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro ha sido eliminado correctamente'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // "Aceptar"
        
        // 3. Esperar a que la alerta desaparezca
        // <-- CORREGIDO: $this.driver
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::className('swal2-popup')
            )
        );

        // -----------------------------------------------------------------
        // PASO 6: Verificación Final
        // -----------------------------------------------------------------
        // El buscador (paso 4.1) *aún* tiene el texto "Nro: 123".
        // La tabla se recarga y no encontrará nada.
        // Esperamos a que la tabla muestre el mensaje de "no hay resultados".
        //
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados"
            ),
            "FALLO: El apartamento eliminado (".$texto_buscar.") todavía se encuentra en la tabla, o la tabla no mostró 'No se encontraron resultados'."
        );

        // <-- CORREGIDO: $this.assertTrue
        $this->assertTrue(true, "Eliminación exitosa: Apartamento creado, eliminado y verificado.");
    }
}