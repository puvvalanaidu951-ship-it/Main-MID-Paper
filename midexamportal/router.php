<?php
$prefix = '';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($uri, $prefix) === 0) {
    $path = substr($uri, strlen($prefix));
    if ($path === '' || $path === '/') {
        $path = '/index.php';
    }
} else {
    $path = $uri;
}
$requested = __DIR__ . $path;
if ($path !== '/router.php' && file_exists($requested) && !is_dir($requested)) {
    require $requested;
    return true;
}
require __DIR__ . '/index.php';

