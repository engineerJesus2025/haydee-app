<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect; // Necesario para manejar el <select>

class carteleraVirtualRegistrarTest extends TestCase
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

    public function testRegistrarPublicacionExitosoUI()
    {
        // -----------------------------------------------------------------
        // PASO 1: Login y Navegación (Estándar)
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/index.php');

        $this->driver->findElement(WebDriverBy::id('correo_login'))
            ->sendKeys('administrador@gmail.com');
        $this->driver->findElement(WebDriverBy::id('contra'))
            ->sendKeys('12345');

        $this->driver->executeScript("document.getElementById('form-login').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"operacion\" value=\"entrar\">');");
        $this->driver->executeScript("document.getElementById('form-login').insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"mantener_sesion\" value=\"false\">');");
        $this->driver->findElement(WebDriverBy::id('form-login'))->submit();
        sleep(1);

        $this->driver->get('http://localhost/haydee-app/?pagina=inicio_controlador.php&accion=inicio');
        $this->driver->wait(10, 500)->until(fn() => $this->driver->findElement(WebDriverBy::id('contenido')));

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Cartelera Virtual
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=cartelera_virtual_controlador.php&accion=inicio');

        // Esperar título y carga de tabla
        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR CARTELERA VIRTUAL')]"))
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_cartelera_virtual']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Abrir Modal y Generar Datos
        // -----------------------------------------------------------------
        
        // Datos de prueba
        $test_titulo = 'Prueba Registrar Selenium ' . rand(1000, 9999);
        $test_descripcion = 'Esta es una descripción de prueba generada automáticamente por Selenium para validar el registro.';
        $test_fecha = date('d-m-Y'); // Formato dd-mm-yyyy suele funcionar bien en inputs date
        
        // Clic en "Nueva Publicación"
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_cartelera']"))
            ->click();

        // Esperar visibilidad del modal
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('titulo_modal'))
        );

        // -----------------------------------------------------------------
        // PASO 4: Llenar Formulario (Sin Imagen)
        // -----------------------------------------------------------------

        // 1. Título
        $this->driver->findElement(WebDriverBy::id('titulo'))->sendKeys($test_titulo);

        // 2. Descripción
        $this->driver->findElement(WebDriverBy::id('descripcion'))->sendKeys($test_descripcion);

        // 3. Fecha
        // Nota: En inputs type="date", sendKeys suele funcionar enviando día-mes-año o año-mes-día
        // Si falla, prueba cambiando el orden a 'Y-m-d'
        $this->driver->findElement(WebDriverBy::id('fecha'))->sendKeys($test_fecha);

        // 4. Prioridad (Select)
        // Usamos la clase WebDriverSelect para manejar el dropdown fácilmente
        $selectElement = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('prioridad')));
        $selectElement->selectByValue('1'); // Seleccionamos 'Alta' (Value 1)

        // 5. Guardar
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // -----------------------------------------------------------------
        // PASO 5: Manejar Alertas (SweetAlert2)
        // -----------------------------------------------------------------

        // Confirmación "¿Estás seguro?"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // Éxito "Registro realizado"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'La operacion se ha realizado correctamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Verificación Final
        // -----------------------------------------------------------------

        // Esperar que cierre el modal de alerta
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // Buscar el título en la tabla para confirmar que se guardó
        $tableElement = $this->driver->findElement(WebDriverBy::id('tabla_cartelera_virtual'));
        
        // Esperamos a que el texto aparezca en la tabla (por si Ajax tarda un poco)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('tabla_cartelera_virtual'), 
                $test_titulo
            ),
            "FALLO: La publicación '$test_titulo' no apareció en la tabla."
        );

        $this->assertTrue(true, "Registro de cartelera virtual exitoso y verificado.");
    }
}