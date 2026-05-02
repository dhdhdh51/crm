<?php
declare(strict_types=1);

// ── Defines ──────────────────────────────────────────────────────────
define('ROOT', dirname(__DIR__));
define('APP_START', microtime(true));

// ── Autoloader ───────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $map = [
        'Core\\'            => ROOT . '/core/',
        'App\\Controllers\\' => ROOT . '/app/controllers/',
        'App\\Models\\'     => ROOT . '/app/models/',
        'App\\Middleware\\'  => ROOT . '/app/middleware/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file     = $dir . $relative . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// ── Config / Helpers ─────────────────────────────────────────────────
$appConfig = require ROOT . '/config/app.php';
date_default_timezone_set($appConfig['timezone']);
ini_set('display_errors', $appConfig['debug'] ? '1' : '0');
error_reporting($appConfig['debug'] ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once ROOT . '/core/Helpers.php';

// ── BASE_URL detection ───────────────────────────────────────────────
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script  = dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php');
$base    = rtrim(str_replace('/public', '', $script), '/');
define('BASE_URL', $scheme . '://' . $host . $base);

// ── Session ───────────────────────────────────────────────────────────
\Core\Session::start();

// ── Router ────────────────────────────────────────────────────────────
$router = new \Core\Router();
require ROOT . '/config/routes.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI']    ?? '/';

// Support POST method override (_method field)
if ($method === 'POST' && isset($_POST['_method'])) {
    $override = strtoupper($_POST['_method']);
    if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
        $method = $override;
    }
}

$router->dispatch($method, $uri);
