<?php
declare(strict_types=1);

if (!defined('GRPG_INC')) {
    exit;
}
if (!defined('INSTALLER') && !file_exists(dirname(__DIR__) . '/.env')) {
    header('Location: install');
    exit;
}
$composer = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($composer)) {
    exit('Composer required.');
}
/** @noinspection PhpIncludeInspection */
require_once $composer;
$dotenv = \Dotenv\Dotenv::createUnsafeImmutable(dirname(__DIR__));
$dotenv->load();
$extraIncludes = getenv('EXTRA_INCLUDES');
if ($extraIncludes !== false) {
    $files = explode(';', $extraIncludes);
    if (count($files) > 0) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                /** @noinspection PhpIncludeInspection */
                require_once $file;
            }
        }
    }
}
// https://github.com/filp/whoops
if (class_exists('\Whoops\Run')) {
    /** @noinspection PhpUndefinedNamespaceInspection,PhpUndefinedClassInspection */
    $whoops = new \Whoops\Run();
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        /** @noinspection PhpUndefinedNamespaceInspection,PhpUndefinedMethodInspection,PhpUndefinedClassInspection */
        $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
    } else {
        /** @noinspection PhpUndefinedNamespaceInspection,PhpUndefinedMethodInspection,PhpUndefinedClassInspection */
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    }
    /** @noinspection PhpUndefinedMethodInspection */
    $whoops->register();
}
error_reporting(E_ALL);
setlocale(LC_ALL, 'en_US');
$_SERVER['REMOTE_ADDR'] = array_key_exists('REMOTE_ADDR', $_SERVER) && filter_var($_SERVER['REMOTE_ADDR'],
    FILTER_VALIDATE_IP) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
define('BASE_URL', getenv('SITE_URL')); // Edit to suit your needs
define('BASE_PATH', getenv('GAME_PATH')); // Absolute path to base (example: /home/username/public_html)
define('DEBUG', (bool)getenv('DEBUG'));
/*
 *    Change the definitions below to match your setup
*/
define('SESSION_NAME', 'GRPG'); // Set to null to use default session name (generated by PHP). Set to custom string - refer to https://php.net/session_name for formats allowed
define('GAME_NAME', 'gRPG'); // Name your game!
define('PRUNE_INACTIVE_ACCOUNTS', false); // Boolean. Set to true to delete all accounts older than 30 days whenever control.php is accessed. Set to false or remove entirely to leave all accounts alone
define('PAYPAL_ADDRESS', 'magictallguy@hotmail.com'); // Change to your own PayPal (or keep it as is :P ~ Magictallguy)
define('RMSTORE_CURRENCY', 'USD'); // Edit to suit your needs - ISO 4217 codes are accepted
define('RMSTORE_LOCALE', 'en_US'); // Alter to match RMSTORE_CURRENCY's locale
define('RMSTORE_DISCOUNT', 0); // Percentage. Set to 0 to disable. Alter to set a discount on the RMStore Upgrades. Example: Setting to 10 will enable a 10% discount. Don't set to anything above 99 - breaks stuff otherwise
define('RMSTORE_BOGOF', false); // Set to true to enable "Boy One Get One Free" offer on RMStore Upgrades. Set to false to disable.
define('MD5_COMPATIBILITY', true); // Set to true to enable md5 passwords being accepted (if verified). Set to false to disable
define('MD5_COMPAT_UPDATE', true); // Set to true to enable updating md5 password to much stronger internal hashing (password_hash()) upon successful login. Set to false to disable. Requires MD5_COMPATIBILITY to be defined as true - a user changing their password will automatically use password_hash()
define('CAPTCHA_REGISTRATION', true); // Set to true to enable CAPTCHA on registration. Set to false to disable
define('CAPTCHA_LOGIN', false); // Set to true to enable CAPTCHA on login. Set to false to disable
define('CAPTCHA_FORGOT_PASS', true); // Set to true to enable CAPTCHA on forgotten password. Set to false to disable
define('SQL_SESSIONS', false); // Set to true to let the database handle PHP sessions. Set to false to use default disk file sessions
define('SECURITY_TIMEOUT_MESSAGE', 'Your request has timed out for security purposes'); // In relation to CSRF protection. If a request is invalid, this message will be displayed
define('DEFAULT_DATE_FORMAT', 'F jS Y, g:i:sa'); // Match PHP's date() format
define('DEFAULT_EMAIL_ADDRESS', 'noreply@' . $_SERVER['HTTP_HOST']); // Ideally, you should alter this to a hard-coded email address, instead of relying on _SERVER['HTTP_HOST']
/*
 *     All definitions below MUST be called *before* including a core file
 *     Example:
 *     <?php
 *     define('NO_PDO', true);
 *     require_once _DIR__ . '/inc/example.php';
 *     // Rest of code...
*/
if (!defined('NO_SESSION')) { // If we haven't declared to not include the session handler
    require_once __DIR__ . '/Zebra_Session.php'; // Include the session handler
}

require_once __DIR__ . '/pdo.class.php'; // Include the PDO function wrapper
if (!defined('NO_CSRF')) { // If we haven't declared to not include the CSRF protection file
    require_once __DIR__ . '/nocsrf.php'; // Include the CSRF protection file
}
if (!defined('NO_FUNCTIONS')) { // If we haven't declared to not include the functions file
    require_once __DIR__ . '/functions.php'; // Include the functions file
}
