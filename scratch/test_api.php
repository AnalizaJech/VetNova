<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\PeruApiService::class);
$dni = '21311331'; // DNI from the screenshot
$res = $service->consultarDni($dni);
echo "Response for DNI $dni:\n";
print_r($res);
