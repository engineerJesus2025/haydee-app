<?php
// tests/Selenium/bancosRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class bancosRegistrarTest extends TestCase
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

    public function testRegistrarBancoExitosoUI()
    {
        // -----------------------------------------------------------------
        // PASO 1: Login y Navegación al Dashboard
        // (Reutilizado de nuestros tests anteriores)
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
        // (Importante: no podemos interactuar hasta que la consulta inicial termine)
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=bancos&accion=inicio');
        
        // Esperar por el título
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR BANCOS')]"));
            }
        );

        // Esperar a que "Cargando..." desaparezca (la tabla está lista)
        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_banco']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Generar Datos y Abrir el Modal
        // -----------------------------------------------------------------
        
        // Generamos un número de cuenta único (20 dígitos)
        // Usamos time() (10) + 8 dígitos de microtime + "00"
        $unique_part = (string)time() . substr(microtime(), 2, 8);
        $test_num_cuenta = $unique_part . '00'; // 20 dígitos, cumple regex /^[0-9]{18,30}$/

        // Datos que pasan todas las validaciones de bancos_validar.js
        $test_nombre = 'Banco Prueba Selenium'; // Cumple /^[A-Za-z ]{3,30}$/
        $test_codigo = '9999';                 // Cumple /^[0-9]{4}$/
        $test_telefono = '04121234567';        // Cumple /^[0-9]{11}$/
        $test_cedula = '12345678';             // Cumple /^[0-9]{7,8}$/

        // Hacer clic en el botón "Nuevo Banco"
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_banco']"))
            ->click();

        // Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('titulo_modal'))
        );

        // -----------------------------------------------------------------
        // PASO 4: Rellenar y Enviar el Formulario
        // -----------------------------------------------------------------
        $this->driver->findElement(WebDriverBy::id('nombre_banco'))->sendKeys($test_nombre);
        $this->driver->findElement(WebDriverBy::id('codigo'))->sendKeys($test_codigo);
        $this->driver->findElement(WebDriverBy::id('numero_cuenta'))->sendKeys($test_num_cuenta);
        $this->driver->findElement(WebDriverBy::id('telefono_afiliado'))->sendKeys($test_telefono);
        $this->driver->findElement(WebDriverBy::id('cedula_afiliada'))->sendKeys($test_cedula);
        
        // Clic en el botón "Guardar"
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // -----------------------------------------------------------------
        // PASO 5: Manejar las Alertas SweetAlert
        // -----------------------------------------------------------------

        // 1. Esperar la alerta de CONFIRMACIÓN
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        // Clic en "Sí, Registrar"
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Esperar la alerta de ÉXITO
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente')
        );
        // Clic en "Aceptar" para cerrarla
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Verificación Final (ASSERT)
        // -----------------------------------------------------------------
        
        // Esperar a que la alerta de éxito desaparezca
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // La prueba real: Esperar a que la tabla contenga nuestro número de cuenta único.
        // Esto confirma que el AJAX de registro y el refresco de la tabla funcionaron.
        $tableElement = $this->driver->findElement(WebDriverBy::id('tabla_banco'));
        $this->driver->wait(10)->until(
            function () use ($tableElement, $test_num_cuenta) {
                // Comprueba si el texto del número de cuenta está en la tabla
                return strpos($tableElement->getText(), $test_num_cuenta) !== false;
            },
            "FALLO: El nuevo número de cuenta ($test_num_cuenta) no se encontró en la tabla después de registrar."
        );

        // Si la espera anterior no falló, la prueba es un éxito.
        $this->assertTrue(true, "Registro exitoso y verificado en la tabla.");
    }
}