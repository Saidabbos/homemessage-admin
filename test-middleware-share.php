<?php
require 'vendor/autoload.php';
try {
    $app = require 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();

    $middleware = new App\Http\Middleware\HandleInertiaRequests();
    echo "Middleware instantiated\n";

    $data = $middleware->share($request);
    echo "Share method called successfully\n";
    echo "Keys: " . implode(', ', array_keys($data)) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
