<?php
$dni = '70617300';
$apiKey = '02dd8e7fce0843cda4d8398eb56f0cc3';
$url = "https://peruapi.com/api/dni/{$dni}";

echo "Probando cURL con URL: $url\n";
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => ["X-API-KEY: $apiKey"],
  CURLOPT_TIMEOUT        => 10,
  CURLOPT_SSL_VERIFYPEER => false // Por si acaso
]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($res === false) { 
    echo "Error cURL: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Respuesta: $res\n";
}
curl_close($ch);
