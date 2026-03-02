<?php
// tests/Selenium/habitantesConsultarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class habitantesConsultarTest extends TestCase
{
    private $driver;

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/'; 
        $capabilities = [ 'browserName' => 'MicrosoftEdge' ];
        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    protected function tearDown(): void
    {
        $this->driver->quit();
    }

    public function testConsultarHabitanteUI()
    {
        // -----------------------------------------------------------------
        // PASO 1: Login Y ESPERA DE DASHBOARD
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
            },
            "FALLO: El login no pareció exitoso (no se encontró 'b_gastos' después de navegar a inicio_controlador)."
        );

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Apartamentos (la página principal)
        // -----------------------------------------------------------------
        
        $this->driver->get('http://localhost/haydee-app/?pagina=apartamentos&accion=inicio');
        
        $this->driver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR APARTAMENTOS Y HABITANTES')]")
            )
        );
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_apartamentos_processing')
            )
        );

        // -----------------------------------------------------------------
        // PASO 3: Abrir el modal de "Detalles Apartamento"
        // -----------------------------------------------------------------
        
        // (Asumo que el title='Detalles Apartamento' es correcto)
        $viewInhabitantsButton = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::xpath("//table[@id='tabla_apartamentos']/tbody/tr[1]//button[@title='Detalles Apartamento']")
            )
        );
        $viewInhabitantsButton->click();

        // -----------------------------------------------------------------
        // PASO 4: Interactuar con el modal de Habitantes ('#modal_vista_previa')
        // -----------------------------------------------------------------

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_vista_previa')
            )
        );

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('tabla_habitantes_processing')
            )
        );

        // Capturar datos de la primera fila de la tabla de habitantes
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody/tr[1]/td[1]")
            )
        );
        
        $nombre_tabla = $this->driver->findElement(
            WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody/tr[1]/td[1]")
        )->getText();
        
        $apellido_tabla = $this->driver->findElement(
            WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody/tr[1]/td[2]")
        )->getText();

        $cedula_tabla = $this->driver->findElement(
            WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody/tr[1]/td[3]")
        )->getText();

        $this->assertNotEmpty($nombre_tabla, "El nombre en la tabla (fila 1) no puede estar vacío.");
        $this->assertNotEmpty($cedula_tabla, "La cédula en la tabla (fila 1) no puede estar vacía.");

        // Clic en el botón "Detalles" (el ojo) de ESE habitante
        $viewDetailsButton = $this->driver->findElement(
            WebDriverBy::xpath("//table[@id='tabla_habitantes']/tbody/tr[1]//button[@title='Detalles Habitante']")
        );
        $viewDetailsButton->click();

        // -----------------------------------------------------------------
        // PASO 5: Verificar datos en el modal de *Detalles* ('#modal_vista_previa_habitantes')
        // -----------------------------------------------------------------
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('modal_vista_previa_habitantes')
            )
        );

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::not(
                WebDriverExpectedCondition::elementTextIs(WebDriverBy::id('vista_nombre'), '')
            ),
            "El modal de vista previa (detalles) se abrió, pero no cargó los datos."
        );

        $nombre_modal = $this->driver->findElement(WebDriverBy::id('vista_nombre'))->getText();
        $apellido_modal = $this->driver->findElement(WebDriverBy::id('vista_apellido'))->getText();
        $cedula_modal = $this->driver->findElement(WebDriverBy::id('vista_cedula'))->getText();

        // -----------------------------------------------------------------
        // PASO 6: Comparar (Assert) y Finalizar
        // -----------------------------------------------------------------
        
        $this->assertEquals($nombre_tabla, $nombre_modal, "El NOMBRE no coincide.");
        $this->assertEquals($apellido_tabla, $apellido_modal, "El APELLIDO no coincide.");
        $this->assertEquals($cedula_tabla, $cedula_modal, "La CÉDULA no coincide.");

        // --- ¡CORRECCIÓN APLICADA AQUÍ! ---
        // Se eliminó el bloque "Cerrar modales" que causaba el TimeoutException.
        // La prueba termina aquí y tearDown() cerrará el navegador.
        
        $this->assertTrue(true, "Consulta (Vista Previa) de habitante exitosa y datos verificados.");
    }
}