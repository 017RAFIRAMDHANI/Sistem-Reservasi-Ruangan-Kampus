<?php
require_once dirname(__DIR__) . '/config/auth.php';

$routes = require dirname(__DIR__) . '/routes/web.php';
$script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

if ($script === 'index.php' && isset($_GET['page'])) {
    $script = basename((string)$_GET['page']);
}

$route = $routes[$script] ?? null;

if (!$route) {
    http_response_code(404);
    echo '404 - Halaman tidak ditemukan.';
    exit;
}

[$controllerClass, $method] = $route;
$controller = new $controllerClass();
$controller->$method();
