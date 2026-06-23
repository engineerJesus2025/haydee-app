<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class proveedoresRegistrarTest extends TestCase
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

    public function testRegistrarProveedorExitosoUI()
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
                return $this->driver->findElement(WebDriverBy::id('contenido'));
            }
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Proveedores y esperar a que la tabla cargue
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=proveedores&accion=inicio');

        // Esperar por el título
        $this->driver->wait(10, 500)->until(
            function () {
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR PROVEEDORES')]"));
            }
        );

        // Esperar a que "Cargando..." desaparezca (la tabla está lista)
        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_proveedores']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Generar Datos y Abrir el Modal
        // -----------------------------------------------------------------

        $unique_part = rand(100000, 999999); // Genera exactamente 6 dígitos
        $test_rif = 'v' . $unique_part;    // Resultado ejemplo: "v482911"
        $test_nombre = 'Proveedor Prueba Selenium';
        $test_servicio = 'Polar';
        $test_direccion = 'direccion selenium';

        // Hacer clic en el botón "Nuevo Proveedor"
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_proveedores']"))
            ->click();

        // Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('titulo_modal'))
        );

        // -----------------------------------------------------------------
        // PASO 4: Rellenar y Enviar el Formulario
        // -----------------------------------------------------------------
        $this->driver->findElement(WebDriverBy::id('nombre_proveedor'))->sendKeys($test_nombre);
        $this->driver->findElement(WebDriverBy::id('rif'))->sendKeys($test_rif);
        $this->driver->findElement(WebDriverBy::id('servicio'))->sendKeys($test_servicio);
        $this->driver->findElement(WebDriverBy::id('direccion'))->sendKeys($test_direccion);

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
        $tableElement = $this->driver->findElement(WebDriverBy::id('tabla_proveedores'));
        $this->driver->wait(10)->until(
            function () use ($tableElement, $test_rif) {
                // Comprueba si el texto del número de cuenta está en la tabla
                return strpos($tableElement->getText(), $test_rif) !== false;
            },
            "FALLO: El nuevo número de cuenta ($test_rif) no se encontró en la tabla después de registrar."
        );

        // Si la espera anterior no falló, la prueba es un éxito.
        $this->assertTrue(true, "Registro exitoso y verificado en la tabla.");
    }
}