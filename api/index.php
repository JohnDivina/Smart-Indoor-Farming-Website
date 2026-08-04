<?php
// Vercel PHP Router for SmartFarm2
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    require_once __DIR__ . '/../index.php';
    exit;
}

$target = __DIR__ . '/..' . $uri;

if (file_exists($target) && !is_dir($target)) {
    $ext = pathinfo($target, PATHINFO_EXTENSION);
    if ($ext === 'php') {
        require_once $target;
        exit;
    }
}

// Fallback to index.php
require_once __DIR__ . '/../index.php';
