<?php
// tests/Selenium/bancosEliminarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class bancosEliminarTest extends TestCase
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

    public function testEliminarBancoExitosoUI()
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

        $this->driver->get('http://localhost/haydee-app/?pagina=inicio&accion=inicio');
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::id('b_gastos'));
            }
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Bancos y esperar a que la tabla cargue
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=bancos&accion=inicio');
        
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
        // PASO 3: Registrar un banco nuevo para eliminarlo
        // -----------------------------------------------------------------
        
        // 3a. Generar datos únicos
        $random_letters = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5);
        $nombre_banco_a_eliminar = 'Banco Para Eliminar ' . $random_letters;
        
        // --- ¡CORRECCIÓN 1 AQUÍ! ---
        // Generamos un número de cuenta único para que pase la validación
        $unique_part = (string)time() . substr(microtime(), 2, 8);
        $test_num_cuenta = $unique_part . '00'; // 20 dígitos

        // 3b. Abrir modal
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_banco']"))
            ->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('titulo_modal'))
        );

        // 3c. Llenar formulario
        $this->driver->findElement(WebDriverBy::id('nombre_banco'))->sendKeys($nombre_banco_a_eliminar);
        $this->driver->findElement(WebDriverBy::id('codigo'))->sendKeys('1111');
        $this->driver->findElement(WebDriverBy::id('numero_cuenta'))->sendKeys($test_num_cuenta); // Usamos el num único
        $this->driver->findElement(WebDriverBy::id('telefono_afiliado'))->sendKeys('04161234567');
        $this->driver->findElement(WebDriverBy::id('cedula_afiliada'))->sendKeys('87654321');
        
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // 3d. Manejar Alertas de Registro
        // (Esta es la línea 97 que estaba fallando)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // --- ¡CORRECCIÓN 2 DE SINTAXIS! --- ($this.driver -> $this->driver)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // -----------------------------------------------------------------
        // PASO 4: Buscar el banco recién creado y hacer clic en "Eliminar"
        // -----------------------------------------------------------------

        // 4a. Buscar el banco
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_banco_filter']//input[@type='search']")
        );
        $searchInput->sendKeys($nombre_banco_a_eliminar);

        // 4b. Esperar a que la tabla se filtre
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_banco']/tbody");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $nombre_banco_a_eliminar
            )
        );

        // 4c. Hacer clic en el botón de eliminar
        $this->driver->findElement(
            WebDriverBy::xpath("//table[@id='tabla_banco']/tbody/tr[1]//button[contains(@class, 'eliminar')]")
        )->click();


        // -----------------------------------------------------------------
        // PASO 5: Manejar las Alertas SweetAlert de Eliminación
        // -----------------------------------------------------------------

        // 1. Esperar la alerta de CONFIRMACIÓN
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Esperar la alerta de ÉXITO
        // --- ¡CORRECCIÓN 3 DE SINTAXIS! --- ($this.driver -> $this->driver)
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

        // 2. Esperar a que la tabla muestre "No se encontraron resultados"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                "No se encontraron resultados"
            ),
            "FALLO: El banco eliminado ($nombre_banco_a_eliminar) todavía aparece en la tabla, o la tabla no mostró 'No se encontraron resultados'."
        );

        // 3. Si la espera anterior no falló, la prueba es un éxito.
        // --- ¡CORRECCIÓN 4 DE SINTAXIS! --- ($this.assertTrue -> $this->assertTrue)
        $this->assertTrue(true, "Eliminación exitosa: El banco fue creado, eliminado y verificado (no se encontró en la tabla).");
    }
}