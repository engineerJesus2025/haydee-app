<?php
if (!class_exists('Dotenv\Dotenv')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Dotenv\Dotenv;

// Cargar variables del .env (que está en la raíz del proyecto)
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Definir constantes SOLO si no han sido definidas previamente
if (!defined('DB_NAME')) define("DB_NAME", $_ENV['DB_NAME'] ?? '');
if (!defined('DB_HOST')) define("DB_HOST", $_ENV['DB_HOST'] ?? '');
if (!defined('DB_USER')) define("DB_USER", $_ENV['DB_USER'] ?? '');
if (!defined('DB_PASS')) define("DB_PASS", $_ENV['DB_PASS'] ?? '');
if (!defined('DB_SECURITY')) define("DB_SECURITY", $_ENV['DB_SECURITY'] ?? '');

if (!defined('DB_BACKUP_USER')) define("DB_BACKUP_USER", $_ENV['DB_BACKUP_USER'] ?? '');
if (!defined('DB_BACKUP_PASS')) define("DB_BACKUP_PASS", $_ENV['DB_BACKUP_PASS'] ?? '');

if (!defined('CLAVE_SITIO_RECAPTCHA')) define('CLAVE_SITIO_RECAPTCHA', $_ENV['CLAVE_SITIO_RECAPTCHA'] ?? '');
if (!defined('CLAVE_SECRETA_RECAPTCHA')) define('CLAVE_SECRETA_RECAPTCHA', $_ENV['CLAVE_SECRETA_RECAPTCHA'] ?? '');

if (!defined('CORREO_CONDOMINIO')) define('CORREO_CONDOMINIO', $_ENV['CORREO_CONDOMINIO'] ?? '');
if (!defined('PROVEEDOR_CORREO')) define('PROVEEDOR_CORREO', $_ENV['PROVEEDOR_CORREO'] ?? '');
if (!defined('API_CORREO')) define('API_CORREO', $_ENV['API_CORREO'] ?? '');
if (!defined('SMTP_HOST')) define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? '');
if (!defined('SMTP_USER')) define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
if (!defined('SMTP_PASS')) define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');

if (!defined('VAPID_PUBLIC_KEY')) define('VAPID_PUBLIC_KEY', $_ENV['VAPID_PUBLIC_KEY'] ?? '');
if (!defined('VAPID_PRIVATE_KEY')) define('VAPID_PRIVATE_KEY', $_ENV['VAPID_PRIVATE_KEY'] ?? '');

if (!defined('JWT_SECRET')) define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? '');

if (!defined('URL_BASE')) define('URL_BASE', $_ENV['URL_BASE'] ?? '/haydee-app/');
if (!defined('ENTORNO')) define('ENTORNO', $_ENV['ENTORNO'] ?? '');

if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));

    //Local
    // define("DB_NAME", "haydee_db");
    // define("DB_HOST", "localhost");
    // define("DB_USER", "app_condominio");
    // define("DB_PASS", "haydee.2025");
    // define("DB_SECURITY", "seguridad_haydee_db");
    // define('CLAVE_SITIO_RECAPTCHA', "6LdyxecrAAAAAGPib9DW3wss1jKLXMsvnTS8rFN_");
    // define('CLAVE_SECRETA_RECAPTCHA', "6LdyxecrAAAAAIpvcVgqtEESLleuRmb54dCb46b9");

    //Hosting
 //    define("DB_NAME", "condominioshaydee_haydee");
 //    define("DB_HOST", "mysql-condominioshaydee.alwaysdata.net");
 //    define("DB_USER", "421243");
 //    define("DB_PASS", "Haydee.2025");
 //    define("DB_SECURITY", "condominioshaydee_seguridad");
	// define('CLAVE_SITIO_RECAPTCHA', "6LcYwOcrAAAAANVy0L4JtP_NSpEpbQGD8h0ZQ2V4");
 // 	define('CLAVE_SECRETA_RECAPTCHA', "6LcYwOcrAAAAACqIboaH-irh7-jfBhF5EAwQ1SlX");    

    //Correo
    // define('API_CORREO', "re_Xh3Z2XV4_bZajUP5ZbSTS6UzKH61fskAL");
    // define('SMTP_HOST', 'smtp-condominioshaydee.alwaysdata.net');
    // define('SMTP_USER', 'condominioshaydee@alwaysdata.net');
    // define('SMTP_PASS', 'Haydee.2025'); 
    // define('URL_BASE', 'http://localhost/tu_proyecto/');

    //Otros
    // define('ENTORNO', 'local'); // local - host
    // define('ROOT_PATH', dirname(__DIR__));

// condominiohaydee2025   ---   Haydee.2025.
    //  haydee-app.pages.dev    
