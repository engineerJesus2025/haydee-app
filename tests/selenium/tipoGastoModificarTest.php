<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverExpectedCondition;

class tipoGastoModificarTest extends TestCase
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

    public function testModificarTipoGastoExitosoUI()
    {
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
                return $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR TIPOS DE GASTOS')]"));
            }
        );

        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_tipo_gasto']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Hacer clic en el primer botón de "Editar"
        // -----------------------------------------------------------------
        
        $editButtonSelector = WebDriverBy::xpath("//table[@id='tabla_tipo_gasto']/tbody/tr[1]//button[@title='Editar']");
        
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($editButtonSelector)
        );
        $editButton->click();

        // -----------------------------------------------------------------
// PASO 4: Esperar Modal, Modificar y Enviar
// -----------------------------------------------------------------

$inputSelector = WebDriverBy::id('nombre_tipo_gasto');
$nombreField = $this->driver->wait(10)->until(
    WebDriverExpectedCondition::visibilityOfElementLocated($inputSelector)
);


$this->driver->wait(5)->until(function ($driver) use ($inputSelector) {
    $element = $driver->findElement($inputSelector);
    return $element->getAttribute('value') != '';
});

$nuevo_nombre_tipo_gasto = 'Tipo Gasto Modificado';

// 3. Borrado Robust: A veces clear() falla en inputs con eventos complejos.
// Aseguramos el foco y borramos.
$nombreField->click();
$nombreField->clear();

// Verificación de seguridad: Si clear() falló, usamos teclas (Ctrl+A -> Backspace)
if ($nombreField->getAttribute('value') != '') {
    $nombreField->sendKeys(array(WebDriverKeys::CONTROL, 'a'));
    $nombreField->sendKeys(WebDriverKeys::BACKSPACE);
}

// 4. Escribir el nuevo nombre
$nombreField->sendKeys($nuevo_nombre_tipo_gasto);

// 5. Hacer click en guardar
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
            WebDriverBy::xpath("//div[@id='tabla_tipo_gasto_filter']//input[@type='search']")
        );

        // 6.3. Escribir el nuevo nombre del banco para buscarlo
        $searchInput->sendKeys($nuevo_nombre_tipo_gasto);
        // 6.4. Esperamos a que el *cuerpo de la tabla* (tbody) contenga el texto.
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_tipo_gasto']/tbody");

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $nuevo_nombre_tipo_gasto
            ),
            "FALLO: El nuevo nombre de tipo de gasto ($nuevo_nombre_tipo_gasto) no se encontró en el 'tbody' de la tabla después de modificar Y BUSCAR."
        );

        // 6.5. Si la espera anterior no falló, la prueba es un éxito.
        $this->assertTrue(true, "Modificación exitosa y verificada en la tabla mediante búsqueda.");
    }
}