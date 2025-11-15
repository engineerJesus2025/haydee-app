<?php
// tests/Selenium/bancosModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class bancosModificarTest extends TestCase
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
                return $this->driver->findElement(WebDriverBy::id('b_gastos'));
            }
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Bancos y esperar a que la tabla cargue
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=bancos_controlador.php&accion=inicio');
        
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR BANCOS')]"));
            }
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_banco']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Hacer clic en el primer botón de "Editar"
        // -----------------------------------------------------------------
        
        $editButtonSelector = WebDriverBy::xpath("//table[@id='tabla_banco']/tbody/tr[1]//button[@title='Editar']");
        
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($editButtonSelector)
        );
        $editButton->click();

        // -----------------------------------------------------------------
        // PASO 4: Esperar Modal, Modificar y Enviar
        // -----------------------------------------------------------------

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('titulo_modal'), 'Modificar Banco')
        );

        // --- ¡¡CORRECCIÓN AQUÍ!! ---
        // Generamos un nombre aleatorio SÓLO CON LETRAS para que pase
        // la validación de Banco.php
        $random_letters = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5);
        $nuevo_nombre_banco = 'Banco Modificado ' . $random_letters; // Ej: "Banco Modificado qjweb"

        $nombreField = $this->driver->findElement(WebDriverBy::id('nombre_banco'));
        $nombreField->clear();
        $nombreField->sendKeys($nuevo_nombre_banco);
        
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
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha modificado exitosamente')
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
            WebDriverBy::xpath("//div[@id='tabla_banco_filter']//input[@type='search']")
        );

        // 6.3. Escribir el nuevo nombre del banco para buscarlo
        $searchInput->sendKeys($nuevo_nombre_banco);

        // 6.4. Esperamos a que el *cuerpo de la tabla* (tbody) contenga el texto.
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_banco']/tbody");

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $nuevo_nombre_banco
            ),
            "FALLO: El nuevo nombre de banco ($nuevo_nombre_banco) no se encontró en el 'tbody' de la tabla después de modificar Y BUSCAR."
        );

        // 6.5. Si la espera anterior no falló, la prueba es un éxito.
        // --- ¡CORRECCIÓN DE SINTAXIS FINAL! ($this.assertTrue -> $this->assertTrue) ---
        $this->assertTrue(true, "Modificación exitosa y verificada en la tabla mediante búsqueda.");
    }
}