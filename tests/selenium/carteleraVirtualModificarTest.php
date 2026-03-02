<?php
// tests/Selenium/carteleraModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;   // Necesario para el borrado seguro
use Facebook\WebDriver\WebDriverSelect; // Necesario para manejar el <select>

class carteleraVirtualModificarTest extends TestCase
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

    public function testModificarPublicacionExitosoUI()
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

        $this->driver->get('http://localhost/haydee-app/?pagina=inicio&accion=inicio');
        $this->driver->wait(10, 500)->until(fn() => $this->driver->findElement(WebDriverBy::id('contenido')));

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Cartelera Virtual
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=cartelera_virtual&accion=inicio');
        
        // Esperar título
        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR CARTELERA VIRTUAL')]"))
        );

        // Esperar a que desaparezca el loading de la tabla
        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_cartelera_virtual']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Seleccionar la PRIMERA publicación para editar
        // -----------------------------------------------------------------
        // Nota: Si prefieres buscar una específica, avísame y ajustamos este paso como en Proveedores.
        
        $editButtonSelector = WebDriverBy::xpath("//table[@id='tabla_cartelera_virtual']/tbody/tr[1]//button[@title='Editar']");
        
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable($editButtonSelector)
        );
        $editButton->click();

        // -----------------------------------------------------------------
        // PASO 4: Esperar Modal y Modificar Datos
        // -----------------------------------------------------------------

        // 4.1 Esperar a que el input 'titulo' sea visible
        $tituloInputSelector = WebDriverBy::id('titulo');
        $tituloField = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($tituloInputSelector)
        );

        // 4.2 (CRUCIAL) Esperar a que AJAX llene el campo con el valor actual
        // Si no hacemos esto, clear() borrará el campo vacío y luego AJAX pondrá el valor viejo.
        $this->driver->wait(5)->until(function ($driver) use ($tituloInputSelector) {
            return $driver->findElement($tituloInputSelector)->getAttribute('value') != '';
        });

        // 4.3 Preparar nuevos datos
        $nuevo_titulo = 'Titulo Editado Selenium ' . rand(1000, 9999);
        $nueva_descripcion = 'Descripción actualizada automáticamente por Selenium.';

        // 4.4 Borrado Robusto del Título
        $tituloField->click();
        $tituloField->clear();
        if ($tituloField->getAttribute('value') != '') {
            $tituloField->sendKeys(array(WebDriverKeys::CONTROL, 'a'));
            $tituloField->sendKeys(WebDriverKeys::BACKSPACE);
        }
        $tituloField->sendKeys($nuevo_titulo);

        // 4.5 Modificar Descripción (Opcional, pero recomendado)
        $descField = $this->driver->findElement(WebDriverBy::id('descripcion'));
        $descField->clear(); 
        $descField->sendKeys($nueva_descripcion);

        // 4.6 Modificar Prioridad (Select)
        // Cambiamos a prioridad '2' (Media) o '3' (Baja) para probar el dropdown
        $selectPrioridad = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('prioridad')));
        $selectPrioridad->selectByValue('2'); // Selecciona "Media"

        // 4.7 Guardar cambios
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // -----------------------------------------------------------------
        // PASO 5: Manejar Alertas
        // -----------------------------------------------------------------

        // Confirmación
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // Éxito
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'La operacion se ha realizado correctamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Verificación
        // -----------------------------------------------------------------
        
        // Esperar que cierre el popup
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // Verificar que el nuevo título aparezca en la tabla (DataTables suele tener un input de búsqueda general)
        // Nota: Cartelera Virtual a veces no tiene el buscador activado por defecto en todas las implementaciones.
        // Si tu tabla tiene id="tabla_cartelera_virtual", asumimos que es un DataTable estándar.
        
        $tableSelector = WebDriverBy::id('tabla_cartelera_virtual');
        
        // Opción A: Verificar simplemente que el texto existe en la tabla (más rápido)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains($tableSelector, $nuevo_titulo),
            "FALLO: El título modificado ($nuevo_titulo) no se encontró en la tabla."
        );

        $this->assertTrue(true, "Modificación exitosa y verificada en la tabla.");
    }
}