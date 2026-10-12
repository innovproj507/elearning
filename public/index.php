<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

require_once BASE_PATH . '/vendor/autoload.php';

// Cargar configuración desde config/app.php y poblar $_ENV
$_appConfig = require BASE_PATH . '/config/app.php';
foreach ($_appConfig as $_k => $_v) {
    if (!array_key_exists($_k, $_ENV)) {
        $_ENV[$_k] = $_v;
        putenv("$_k=$_v");
    }
}
unset($_appConfig, $_k, $_v);

// Helpers globales (cms_asset, cms_url) — requieren $_ENV ya poblado
require_once BASE_PATH . '/src/Core/helpers.php';

// Errores según entorno
if ($_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);

    set_exception_handler(function (\Throwable $e): void {
        error_log('[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        include BASE_PATH . '/views/errors/500.php';
        exit;
    });

    set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
        error_log('[' . date('Y-m-d H:i:s') . "] [$errno] $errstr in $errfile:$errline");
        return true;
    });
}

date_default_timezone_set('America/Bogota');

// Cabeceras de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.ckeditor.com https://static.cloudflareinsights.com; script-src-elem 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.ckeditor.com https://static.cloudflareinsights.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdn.ckeditor.com; font-src 'self' https://fonts.gstatic.com https://cdn.ckeditor.com; img-src 'self' data: blob: http: https:; media-src 'self'; connect-src 'self' https://cdn.ckeditor.com https://cloudflareinsights.com; object-src 'none'; frame-ancestors 'self'");

use App\Core\Session;
use App\Core\Language;
use App\Core\Router;

Session::start();
Language::init();

$router = new Router();

require BASE_PATH . '/routes/web.php';
require BASE_PATH . '/routes/api.php';

$router->dispatch();
