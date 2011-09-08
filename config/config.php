<?php
// Application settings
define('ROOT_DIR', realpath(dirname(__FILE__) . '/../'));
define('APP_DIR', ROOT_DIR . '/app');

// Error reporting level
define('DEBUG', true);
if (DEBUG) {
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Information required to connect to the database
define('DB_HOST', 'localhost');
define('DB_NAME', 'apify');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Default timezone used by all date/time functions
date_default_timezone_set('Europe/London');

// Set PHP config values
set_magic_quotes_runtime(0);
ini_set('register_globals', 'Off');
ini_set('short_open_tag', 'Off');
ini_set('session.cookie_lifetime', '3600'); // 1 hour

// Include required files
require_once ROOT_DIR . '/library/Loader.php';
require_once ROOT_DIR . '/library/Exceptions.php';
require_once ROOT_DIR . '/library/Router.php';
require_once APP_DIR . '/controllers/Controller.php';

// Include path configuration option
set_include_path(ROOT_DIR . '/models' . PATH_SEPARATOR . ROOT_DIR . '/library');
spl_autoload_register(array('Loader', 'autoload'));



