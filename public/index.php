<?php
require_once dirname(__FILE__) . '/../config/config.php';

try {
    $request = new Request();
    $request->enableUrlRewriting();
    $request->addRoutes(include ROOT_DIR.'/config/routes.php');
    $request->dispatch();
} catch (Exception $e) {
    $request->handleException($e);
}

