<?php
define('ROOT_DIR', realpath(dirname(__FILE__) . '/../'));
define('APP_DIR', ROOT_DIR . '/app');

require_once ROOT_DIR . '/config/config.php';

try {
    $request = new Request();
    $request->enableUrlRewriting();
    $request->addRoutes(include ROOT_DIR.'/config/routes.php');
    $request->dispatch();
} catch (Exception $e) {
    $request->handleException($e);
}

