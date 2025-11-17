<?php
// tests/Selenium/solicitudesRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class solicitudGastoRegistrarTest extends TestCase
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

    public function testRegistrarSolicitudExitosoUI()
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

        $this->driver->get('http://localhost/haydee-app/?pagina=inicio_controlador.php&accion=inicio');
        $this->driver->wait(10, 500)->until(fn() => $this->driver->findElement(WebDriverBy::id('contenido')));

        // -----------------------------------------------------------------
        // PASO 2: Navegar a Solicitudes
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=solicitud_gasto_controlador.php&accion=inicio');

        // Esperar título
        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR SOLICITUD DE GASTOS')]"))
        );

        // Esperar a que AJAX cargue la tabla inicial
        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_solicitud_gasto']//td[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Abrir Modal y Cargar Presupuesto (AJAX)
        // -----------------------------------------------------------------
        
        // 3.1 Clic en botón Nueva Solicitud
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_solicitud_gasto']"))->click();

        // 3.2 Esperar a que el modal sea visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_solicitud_gasto'))
        );

        // 3.3 Seleccionar MES y AÑO
        // Nota: Tu JS carga estos selects vía Ajax (cargarMesesYAniosConPresupuesto) al abrir el modal.
        
        // Esperamos a que el select de mes tenga opciones (más allá del placeholder)
        $this->driver->wait(5)->until(function($driver) {
            return count($driver->findElement(WebDriverBy::id('selector_mes'))->findElements(WebDriverBy::tagName('option'))) > 1;
        });

        // Seleccionamos el primer mes válido (Index 1)
        $selectMes = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('selector_mes')));
        $selectMes->selectByIndex(1); 

        // Seleccionamos el primer año válido (Index 1)
        $selectAnio = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('selector_anio')));
        $selectAnio->selectByIndex(1);

        // 3.4 FORZAR evento 'change'
        // A veces Selenium selecciona pero no dispara el evento JS que llama a 'buscarPresupuesto()'.
        // Esto asegura que tu función AJAX se ejecute.
        $this->driver->executeScript("
            var event = new Event('change');
            document.getElementById('selector_anio').dispatchEvent(event);
        ");

        // 3.5 Esperar a que aparezca el formulario completo
        // Esto valida que 'buscarPresupuesto' encontró datos y ejecutó 'camposFormulario.style.display = "block"'
        $camposOcultos = WebDriverBy::id('campos_formulario_completo');
        
        try {
            $this->driver->wait(10)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated($camposOcultos)
            );
        } catch (\Exception $e) {
            $this->fail("FALLO: El formulario no se desplegó. Causa probable: No hay presupuesto registrado para el Mes/Año seleccionado o el AJAX falló.");
        }

        // -----------------------------------------------------------------
        // PASO 4: Llenar Formulario
        // -----------------------------------------------------------------

        $unique_id = rand(1000, 9999);
        $test_nombre = 'Solicitante Selenium';
        $test_monto = '10.50'; // Monto bajo para asegurar que no exceda el disponible
        $test_descripcion = 'Solicitud Auto ' . $unique_id;

        $this->driver->findElement(WebDriverBy::id('fecha'))->sendKeys(date('d-m-Y'));
        $this->driver->findElement(WebDriverBy::id('nombre'))->sendKeys($test_nombre);
        $this->driver->findElement(WebDriverBy::id('monto_estimado'))->sendKeys($test_monto);

        $selectPrioridad = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('prioridad')));
        $selectPrioridad->selectByValue('1'); // Alta

        $this->driver->findElement(WebDriverBy::id('descripcion'))->sendKeys($test_descripcion);

        // -----------------------------------------------------------------
        // PASO 5: Guardar y Confirmar
        // -----------------------------------------------------------------

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // 1. Esperar alerta "¿Estás seguro?"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Esperar alerta "Éxito" (Generada por tu función consulta_completada())
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), 'Atencion')
        );
        // Validamos también el texto del cuerpo para estar seguros
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'correctamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 6: Verificación Final
        // -----------------------------------------------------------------

        // Esperar a que cierre el SweetAlert
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // Buscar en la tabla
        // Tu función 'consultar()' recarga la tabla al final, así que buscamos el texto.
        $tableSelector = WebDriverBy::id('tabla_solicitud_gasto');
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $test_descripcion
            ),
            "FALLO: La solicitud creada ($test_descripcion) no se encontró en la tabla."
        );

        $this->assertTrue(true, "Solicitud registrada y verificada correctamente.");
    }
}