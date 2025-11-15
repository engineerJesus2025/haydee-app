<?php
// tests/Selenium/apartamentosModificarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class apartamentosModificarTest extends TestCase
{
    private $driver;
    private $nuevo_porcentaje;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        // Generamos un porcentaje único (ej: 25.34)
        $this->nuevo_porcentaje = rand(10, 99) . '.' . rand(10, 99);
    }

    protected function tearDown(): void
    {
        $this->driver->quit(); 
    }

    public function testModificarApartamentoUI()
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
        
        // (Corregido el typo $this.driver)
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
        // PASO 3: Abrir Modal de Edición y Modificar
        // -----------------------------------------------------------------
        // 1. Clic en el primer botón de "Editar"
        $editButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//table[@id='tabla_apartamentos']/tbody/tr[1]//button[@title='Editar']")
            )
        );
        $editButton->click();

        // --- CORRECCIÓN PARA TIMEOUTEXCEPTION ---
        // 2. Esperar a que el CONTENEDOR del modal sea visible primero
        //    (Asumo que el ID del modal es 'modal_apartamentos' basado en tu otro test)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_apartamentos') 
            )
        );
        
        // 3. AHORA SÍ, esperar a que el título sea "Modificar Apartamento"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('titulo_modal'), 'Modificar Apartamento'
            )
        );
        
        // 4. Capturar el Nro de Apartamento (para buscarlo después)
        $nro_apto_modificado = $this->driver
            ->findElement(WebDriverBy::id('nro_apartamento'))
            ->getAttribute('value');

        // 5. Cambiar el porcentaje
        $porcentajeField = $this->driver->findElement(WebDriverBy::id('porcentaje_participacion'));
        $porcentajeField->clear();
        $porcentajeField->sendKeys($this->nuevo_porcentaje);

        // 6. Enviar
        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // -----------------------------------------------------------------
        // PASO 4: Manejar Alertas y Verificar
        // -----------------------------------------------------------------
        // 1. Alerta de Confirmación
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-title'), '¿Estás seguro?'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // "Sí, Editar"

        // 2. Alerta de Éxito
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::id('swal2-html-container'), 'El registro se ha modificado exitosamente'
            )
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click(); // "Aceptar"
        
        // 3. Esperar a que la alerta desaparezca
        // (Corregido el typo $this.driver)
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::className('swal2-popup')
            )
        );

        // 4. Verificación: Buscar el apto y el nuevo porcentaje
        // El AJAX recarga la tabla
        $searchInput = $this->driver->findElement(
            WebDriverBy::xpath("//div[@id='tabla_apartamentos_filter']//input[@type='search']")
        );
        $searchInput->sendKeys('Nro: ' . $nro_apto_modificado);

        // 5. Esperar a que el tbody contenga el *nuevo porcentaje*
        $tableBodySelector = WebDriverBy::xpath("//table[@id='tabla_apartamentos']/tbody");
        // El formato es "12.34%"
        $texto_porcentaje_esperado = $this->nuevo_porcentaje . '%'; 
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableBodySelector,
                $texto_porcentaje_esperado
            ),
            "FALLO: El apartamento (Nro: " . $nro_apto_modificado . ") no mostró el nuevo porcentaje (" . $texto_porcentaje_esperado . ") en la tabla."
        );

        $this->assertTrue(true, "Modificación exitosa y verificada en la tabla.");
    }
}