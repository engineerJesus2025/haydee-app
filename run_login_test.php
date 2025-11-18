<?php
// .\msedgedriver.exe --port=4444
require 'vendor/autoload.php';

use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;
use PhpXmlRpc\Encoder;

// -----------------------------------------------------------------
// --- CONFIGURACIÓN (aqui configuramos cada testcase muchachos) ---
// -----------------------------------------------------------------
$TESTLINK_API_KEY  = '8314e3ce9557da4c3b74a9f175ac7fac'; // Cambian esto por su API Key de testlink
$TESTLINK_URL      = 'http://localhost:8080/testlink/lib/api/xmlrpc.php'; // No vayan a cambiar esto
$TEST_PROJECT_NAME = 'haydee'; // Aqui ponen el nombre del proyecto que usan en TestLink
$TEST_CASE_ID      = 'TC--5'; // En este caso, el ID externo del caso de prueba en TestLink
$BUILD_NAME        = '1.0'; // Nombre de la build que estan probando
$TEST_PLAN_ID      = 2; // Y el ID del plan de pruebas en TestLink
// -----------------------------------------------------------------

// --- Esto es puro para mostrar colores en la consola y sea mas entendible el resultado ---
define('COLOR_VERDE', "\033[0;32m");
define('COLOR_ROJO', "\033[0;31m");
define('COLOR_RESET', "\033[0m");
// ------------------------------------

// --- IMPORTANTE: ---
// Aqui abajo van los comandos para ejecutar la prueba de selenium con phpunit
$command = 'vendor\bin\phpunit tests/Selenium/mensualidadEliminarTest.php';
$output = [];
$returnCode = 0; 

echo "--- Iniciando ejecucion automatizada para $TEST_CASE_ID ---\n";
exec($command, $output, $returnCode);

$fullOutput = implode("\n", $output);
$resultStatus = ($returnCode === 0) ? 'p' : 'f'; // 'p' = passed, 'f' = failed (NO CAMBIAR ESTO)

// --- Aqui le hice el cambio con los colores y se vea bonito :v ---
$mensaje = ($resultStatus === 'p') ? (COLOR_VERDE . 'Aprobado' . COLOR_RESET) : (COLOR_ROJO . 'Fallido' . COLOR_RESET); 

//echo "Prueba de Selenium finalizada. Resultado: $mensaje\n";
echo "====================================================\n\n";
echo "Salida de PHPUnit:\n$fullOutput\n";
echo "--- Reportando resultado a TestLink... ---\n";

try {
    $encoder = new Encoder();
    $method = 'tl.reportTCResult';

    $params = [
        'devKey'             => new Value($TESTLINK_API_KEY, 'string'),
        'testprojectname'    => new Value($TEST_PROJECT_NAME, 'string'),
        'testplanid'         => new Value($TEST_PLAN_ID, 'int'), 
        'testcaseexternalid' => new Value($TEST_CASE_ID, 'string'),
        'buildname'          => new Value($BUILD_NAME, 'string'),
        'status'             => new Value($resultStatus, 'string'),
        'notes'              => new Value($fullOutput, 'string')
    ];
    
    $request_params = [ new Value($params, 'struct') ];
    $client = new Client($TESTLINK_URL);
    $request = new Request($method, $request_params);

    $response = $client->send($request);

    if ($response->faultCode()) {
        echo COLOR_ROJO . "Error: La API de TestLink devolvio un error (faultCode):\n" . COLOR_RESET;
        echo "Codigo: " . $response->faultCode() . "\n";
        echo "Mensaje: " . $response->faultString() . "\n";
        exit(1);
    } else {
        $data = $encoder->decode($response->value());
        $first_response = is_array($data) ? $data[0] : $data;

        if (is_array($first_response) && isset($first_response['message'])) {
            
            if ($first_response['message'] === 'Success!') {
                echo "Prueba de Selenium finalizada. Resultado: $mensaje\n";
                echo COLOR_VERDE . "Conexion Exitosa - Resultado reportado a TestLink.\n" . COLOR_RESET;
                exit(0);
            } else {
                echo "Prueba de Selenium finalizada. Resultado: $mensaje\n";
                echo COLOR_ROJO . "Conexion Fallida - TestLink rechazó el resultado:\n" . COLOR_RESET;
                echo "Mensaje de TestLink: " . $first_response['message'] . "\n";
                print_r($data);
                exit(1);
            }
        } else {
             echo COLOR_VERDE . "Exito - Resultado reportado a TestLink (Respuesta genérica).\n" . COLOR_RESET;
             print_r($data);
             exit(0);
        }
    }
} catch (Exception $e) {
    echo COLOR_ROJO . "Error de Conexión: No se pudo reportar a TestLink: " . $e->getMessage() . "\n" . COLOR_RESET;
    exit(1); 
}