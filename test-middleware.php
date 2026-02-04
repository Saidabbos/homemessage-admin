<?php
require 'vendor/autoload.php';
try {
    $app = require 'bootstrap/app.php';
    $middleware = new App\Http\Middleware\HandleInertiaRequests();
    echo "Middleware instantiated successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
