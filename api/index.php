<?php

declare(strict_types=1);

$storagePath = getenv('LARAVEL_STORAGE_PATH') ?: '/tmp/storage';
$runtimePaths = [
    $storagePath,
    $storagePath.'/app',
    $storagePath.'/framework',
    $storagePath.'/framework/cache',
    $storagePath.'/framework/cache/data',
    $storagePath.'/framework/sessions',
    $storagePath.'/framework/testing',
    $storagePath.'/framework/views',
    $storagePath.'/logs',
];

foreach ($runtimePaths as $runtimePath) {
    if (! is_dir($runtimePath) && ! mkdir($runtimePath, 0775, true) && ! is_dir($runtimePath)) {
        throw new RuntimeException('Não foi possível preparar o armazenamento temporário da aplicação.');
    }
}

$temporaryEnvironment = [
    'LARAVEL_STORAGE_PATH' => $storagePath,
    'VIEW_COMPILED_PATH' => getenv('VIEW_COMPILED_PATH') ?: $storagePath.'/framework/views',
    'APP_CONFIG_CACHE' => getenv('APP_CONFIG_CACHE') ?: '/tmp/config.php',
    'APP_EVENTS_CACHE' => getenv('APP_EVENTS_CACHE') ?: '/tmp/events.php',
    'APP_PACKAGES_CACHE' => getenv('APP_PACKAGES_CACHE') ?: '/tmp/packages.php',
    'APP_ROUTES_CACHE' => getenv('APP_ROUTES_CACHE') ?: '/tmp/routes.php',
    'APP_SERVICES_CACHE' => getenv('APP_SERVICES_CACHE') ?: '/tmp/services.php',
];

foreach ($temporaryEnvironment as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

require dirname(__DIR__).'/public/index.php';
