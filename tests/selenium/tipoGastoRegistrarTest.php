<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class tipoGastoRegistrarTest extends TestCase
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

    public function testRegistrarTipoGastoExitosoUI()
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
        
        // Esperar por el título
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR TIPOS DE GASTOS')]"));
            }
        );

        // Esperar a que "Cargando..." desaparezca (la tabla está lista)
        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_tipo_gasto']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        

        $test_nombre = 'Tipo Gasto Prueba Selenium'; // 


        // Hacer clic en el botón "Nuevo Tipo Gasto"
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_tipo_gasto']"))
            ->click();

        // Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('titulo_modal'))
        );


        // PASO 4: Rellenar y Enviar el Formulario

        $this->driver->findElement(WebDriverBy::id('nombre_tipo_gasto'))->sendKeys($test_nombre);

        
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

        $tableElement = $this->driver->findElement(WebDriverBy::id('tabla_tipo_gasto'));
        $this->driver->wait(10)->until(
            function () use ($tableElement, $test_nombre) {
                // Comprueba si el texto del número de cuenta está en la tabla
                return strpos($tableElement->getText(), $test_nombre) !== false;
            },
            "FALLO: El nuevo tipo de gasto ($test_nombre) no se encontró en la tabla después de registrar."
        );

        // Si la espera anterior no falló, la prueba es un éxito.
        $this->assertTrue(true, "Registro exitoso y verificado en la tabla.");
    }
}