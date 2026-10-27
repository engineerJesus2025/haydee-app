<?php
date_default_timezone_set('America/Caracas');
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

if (!defined('URL_BASE')) define('URL_BASE', $_ENV['URL_BASE'] ?? '/condominios-haydee/haydee-web/');
if (!defined('ENTORNO')) define('ENTORNO', $_ENV['ENTORNO'] ?? 'local');

if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
