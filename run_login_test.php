<?php
// 1. Cargar Composer
require 'vendor/autoload.php';

use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;
use PhpXmlRpc\Encoder;

// -----------------------------------------------------------------
// --- CONFIGURACIÓN (aqui configuramos cada testcase muchachos) ---
// -----------------------------------------------------------------
$TESTLINK_API_KEY  = '30779eda0efadc81d9088c35d61b0394'; // Cambian esto por su API Key de testlink
$TESTLINK_URL      = 'http://localhost:8080/testlink/lib/api/xmlrpc.php'; // No vayan a cambiar esto
$TEST_PROJECT_NAME = 'Proyecto Haydee'; // Aqui ponen el nombre del proyecto que usan en TestLink
$TEST_CASE_ID      = '1-1'; // En este caso, el ID externo del caso de prueba en TestLink
$BUILD_NAME        = 'Version 1.3'; // Nombre de la build que estan probando
$TEST_PLAN_ID      = 2; // Y el ID del plan de pruebas en TestLink
// -----------------------------------------------------------------

$command = 'vendor\bin\phpunit tests\selenium\loginTest.php';
$output = [];
$returnCode = 0; 

echo "--- Iniciando ejecucion automatizada para $TEST_CASE_ID ---\n";
exec($command, $output, $returnCode);

$fullOutput = implode("\n", $output);
$resultStatus = ($returnCode === 0) ? 'p' : 'f'; // 'p' = passed, 'f' = failed (NO CAMBIAR ESTO)
$mensaje = ($resultStatus === 'p') ? 'Aprobado' : 'Fallido'; // Aqui lo puse asi para que no saliera solamente una "P" o "F" en el resultado

echo "Prueba de Selenium finalizada. Resultado: $mensaje\n";
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

    // --- LÓGICA DE VERIFICACIÓN FINAL ---
    if ($response->faultCode()) {
        echo "Error: La API de TestLink devolvio un error (faultCode):\n";
        echo "Codigo: " . $response->faultCode() . "\n";
        echo "Mensaje: " . $response->faultString() . "\n";
        exit(1);
    } else {
        $data = $encoder->decode($response->value());
        $first_response = is_array($data) ? $data[0] : $data;

        if (is_array($first_response) && isset($first_response['message'])) {
            
            if ($first_response['message'] === 'Success!') {
                echo "Conexion Exitosa - Resultado reportado a TestLink.\n";
                exit(0);
            } else {
                echo "Conexion Fallida - TestLink rechazó el resultado:\n";
                echo "Mensaje de TestLink: " . $first_response['message'] . "\n";
                print_r($data);
                exit(1);
            }
        } else {
             echo "Exito - Resultado reportado a TestLink (Respuesta genérica).\n";
             print_r($data);
             exit(0);
        }
    }
} catch (Exception $e) {
    echo "Error de Conexión: No se pudo reportar a TestLink: " . $e->getMessage() . "\n";
    exit(1); 
}