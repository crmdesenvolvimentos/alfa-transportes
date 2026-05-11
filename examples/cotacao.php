<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CrmDesenvolvimentos\AlfaTransportes\AlfaTransportesClient;
use CrmDesenvolvimentos\AlfaTransportes\QuoteRequest;

$client = new AlfaTransportesClient('sua_chave_da_api');

// A documentacao da Alfa exige merM3. Ajuste 0.01 para o cubico real da carga.
$request = QuoteRequest::forNaturalPerson(
    '000000000',
    '88888888',
    QuoteRequest::moneyToFloat('3.790,00'),
    5.0,
    0.01
)->sender('00000000000000', '88888888')
    ->volumeQuantity(1);

$response = $client->quoteResponse($request);

print_r($response->toArray());
