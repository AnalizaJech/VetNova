<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PeruApiService;

$service = new PeruApiService();
$dni = '70617300';
echo "Consultando DNI: $dni\n";
$result = $service->consultarDni($dni);

if ($result) {
    echo "Resultado: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Error: No se obtuvieron datos.\n";
}
