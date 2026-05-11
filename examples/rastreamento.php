<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CrmDesenvolvimentos\AlfaTransportes\TrackingClient;
use CrmDesenvolvimentos\AlfaTransportes\TrackingRequest;

$apiKey = getenv('ALFA_API_KEY') ?: 'sua_chave_da_api';
$invoiceNumber = isset($argv[1]) ? (int) $argv[1] : 104061;
$senderDocument = $argv[2] ?? '00000000000000';

$client = new TrackingClient($apiKey);

$request = new TrackingRequest($invoiceNumber, $senderDocument);

$response = $client->trackResponse($request);

if (!$response->isSuccessful()) {
    echo 'HTTP: ' . ($response->httpStatusCode() ?? 'N/A') . PHP_EOL;
    echo 'Status API: ' . ($response->statusCode() ?? 'N/A') . PHP_EOL;
    echo 'Mensagem: ' . ($response->statusDescription() ?? $response->errorMessage() ?? 'Sem mensagem') . PHP_EOL;
}

print_r($response->toArray());
