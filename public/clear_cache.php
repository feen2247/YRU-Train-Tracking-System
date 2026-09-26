<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset successfully.<br>";
} else {
    echo "OPcache not available.<br>";
}

// Clear Laravel caches manually if possible
$laravelPath = __DIR__ . '/../laravel';
if (file_exists($laravelPath . '/artisan')) {
    require $laravelPath . '/vendor/autoload.php';
    $app = require_once $laravelPath . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    $kernel->call('view:clear');
    echo "View cache cleared.<br>";
    $kernel->call('cache:clear');
    echo "App cache cleared.<br>";
    $kernel->call('route:clear');
    echo "Route cache cleared.<br>";
    $kernel->call('config:clear');
    echo "Config cache cleared.<br>";
} else {
    echo "Laravel artisan not found at $laravelPath.<br>";
}

echo "All done!";
