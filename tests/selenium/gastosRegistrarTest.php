<?php
// tests/Selenium/gastosRegistrarTest.php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class gastosRegistrarTest extends TestCase
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

    public function testRegistrarGastoEfectivoExitosoUI()
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
        // PASO 2: Navegar a Gestión de Gastos
        // -----------------------------------------------------------------
        $this->driver->get('http://localhost/haydee-app/?pagina=gastos_controlador.php&accion=inicio');

        // Esperar título "GESTIONAR GASTOS"
        $this->driver->wait(10, 500)->until(
            fn() => $this->driver->findElement(WebDriverBy::xpath("//h2[contains(text(), 'GESTIONAR GASTOS')]"))
        );

        // Esperar carga de la tabla
        $loadingSelector = WebDriverBy::xpath("//table[@id='tabla_gastos']//h4[contains(text(), 'Cargando...')]");
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($loadingSelector)
        );

        // -----------------------------------------------------------------
        // PASO 3: Abrir Modal y Generar Datos Generales
        // -----------------------------------------------------------------
        
        $unique_id = rand(1000, 9999);
        $test_descripcion_gasto = 'Gasto Varios Selenium ' . $unique_id;
        $test_descripcion_detalle = 'Pago en efectivo pruebas unitarias';
        $test_monto = '50.00';
        $test_fecha = date('d-m-Y');

        // Clic en "Nuevo Gasto" (Asumiendo que el botón tiene este target según tus otros modales)
        $this->driver->findElement(WebDriverBy::xpath("//button[@data-bs-target='#modal_gastos']"))->click();

        // Esperar visibilidad del modal
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('form_gastos'))
        );

        // -----------------------------------------------------------------
        // PASO 4: Llenar Cabecera del Gasto
        // -----------------------------------------------------------------

        // 1. Tipo (Fijo/Variable)
        $selectTipo = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('tipo')));
        $selectTipo->selectByValue('variable');

        // 2. Tipo de Gasto (Selecciona el primero disponible en la lista)
        $selectTipoGasto = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('tipo_gasto')));
        // Usamos selectByIndex(1) para evitar adivinar IDs de la base de datos. El 0 suele ser "Seleccione..."
        $selectTipoGasto->selectByIndex(1); 

        // 3. Descripción General
        $this->driver->findElement(WebDriverBy::id('descripcion_gasto'))->sendKeys($test_descripcion_gasto);

        // 4. Proveedor (Selecciona el primero disponible)
        $selectProveedor = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('proveedor')));
        $selectProveedor->selectByIndex(1);

        // 5. Solicitud (Selecciona la primera disponible)
        $selectSolicitud = new WebDriverSelect($this->driver->findElement(WebDriverBy::id('solicitud')));
        $selectSolicitud->selectByIndex(1);

        // -----------------------------------------------------------------
        // PASO 5: Llenar DETALLE del Gasto (Solo el primero, Efectivo)
        // -----------------------------------------------------------------
        
        // Nota: Al usar findElement (singular) con selectores de clase, Selenium tomará 
        // automáticamente el primer elemento visible, que corresponde al primer detalle.

        // 1. Fecha del detalle
        // Usamos CSS Selector porque es una clase dentro del array de detalles
        $this->driver->findElement(WebDriverBy::cssSelector('.fecha_detalle'))->sendKeys($test_fecha);

        // 2. Método de Pago: SIEMPRE EFECTIVO
        $selectMetodo = new WebDriverSelect($this->driver->findElement(WebDriverBy::cssSelector('.metodo_pago')));
        $selectMetodo->selectByValue('Efectivo');

        // 3. Monto
        $this->driver->findElement(WebDriverBy::cssSelector('.monto'))->sendKeys($test_monto);

        // 4. Descripción del Detalle
        $this->driver->findElement(WebDriverBy::cssSelector('.descripcion_detalle'))->sendKeys($test_descripcion_detalle);

        // NOTA: Al ser Efectivo, no llenamos Referencia ni Banco.

        // -----------------------------------------------------------------
        // PASO 6: Guardar y Confirmar
        // -----------------------------------------------------------------

        $this->driver->findElement(WebDriverBy::id('boton_formulario'))->click();

        // 1. Alerta "¿Estás seguro?"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-title'), '¿Estás seguro?')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // 2. Alerta "Registro exitoso"
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('swal2-html-container'), 'El registro se ha realizado exitosamente')
        );
        $this->driver->findElement(WebDriverBy::className('swal2-confirm'))->click();

        // -----------------------------------------------------------------
        // PASO 7: Verificación Final
        // -----------------------------------------------------------------

        // Esperar cierre de alerta
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('swal2-popup'))
        );

        // Verificar que la descripción del gasto aparezca en la tabla
        $tableSelector = WebDriverBy::id('tabla_gastos'); // Asegúrate que tu tabla tenga este ID
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementTextContains(
                $tableSelector,
                $test_descripcion_gasto
            ),
            "FALLO: El gasto registrado ($test_descripcion_gasto) no apareció en la tabla."
        );

        $this->assertTrue(true, "Registro de Gasto (Efectivo) completado exitosamente.");
    }
}