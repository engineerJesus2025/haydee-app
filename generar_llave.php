<?php
//  No lo he dejado para ejecutarlo en el servidor despues. Ojala no se me olvide borrarlo XD
require_once "vendor/autoload.php";
use phpseclib3\Crypt\RSA;

// Generamos el par de llaves maestras del servidor
$private = RSA::createKey(4096);
$public = $private->getPublicKey();

// Las guardamos en la carpeta config
file_put_contents(ROOT_PATH . '/config/llave_servidor_privada.pem', $private->toString('PKCS8'));
file_put_contents(ROOT_PATH . '/config/llave_servidor_publica.pem', $public->toString('PKCS8'));

echo "Llaves maestras generadas con éxito en la carpeta config";
?>
<?php // echo base64_encode(random_bytes(32)); 