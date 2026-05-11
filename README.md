# Alfa Transportes

Biblioteca PHP 7.4+ para consumir as APIs de cotacao e rastreamento de frete da Alfa Transportes.

Documentacoes usadas como base:

- Cotação: https://api.alfatransportes.com.br/cotacao/v1.2/docs/
- Rastreamento: https://api.alfatransportes.com.br/rastreamento/v1.3/docs

## Instalação

Em outro projeto, instale por caminho local enquanto este pacote nao estiver publicado:

```bash
composer config repositories.alfa-transportes path /home/celio/sites/outros/packages/api-alfa-transportes/cx
composer require crmdesenvolvimentos/alfa-transportes
```

Ou publique este repositorio em um VCS e instale via Composer normalmente.

## Cotação

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use CrmDesenvolvimentos\AlfaTransportes\AlfaTransportesClient;
use CrmDesenvolvimentos\AlfaTransportes\QuoteRequest;

$client = new AlfaTransportesClient('sua_chave_da_api');

// A API exige o campo merM3. Substitua 0.01 pelo metro cubico real da carga.
$request = QuoteRequest::forNaturalPerson(
    '00000000000000',
    '89000000',
    QuoteRequest::moneyToFloat('3.000,00'),
    5.0,
    0.01
)->sender('00000000000000', '87000000')
    ->volumeQuantity(1);

$response = $client->quoteResponse($request);

if ($response->isSuccessful()) {
    echo $response->totalValue();
    echo $response->deliveryEstimate();
}

print_r($response->toArray());
```

## Campos suportados

O cliente de cotação monta o payload aceito pela API:

- `idr`: chave da API.
- `cliTip`: 1 pessoa juridica, 2 pessoa fisica.
- `cliCnpj`: CPF ou CNPJ do destinatario, apenas numeros.
- `cliCep`: CEP do destinatario, apenas numeros.
- `merVlr`: valor da mercadoria.
- `merPeso`: peso bruto.
- `merM3`: metro cubico.
- `merVol`: quantidade de volumes.
- `quim`: 0 nao quimico, 1 quimico.
- `dtEmbarque`: data com 8 digitos.
- `cepRem`: CEP do remetente.
- `cnpjRem`: CNPJ do remetente.
- `zonaRural`: 0 nao, 1 sim.
- `tipoPagador`: 1 CIF, 2 FOB.
- `modoJson`: sempre enviado como 1.

## Rastreamento

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use CrmDesenvolvimentos\AlfaTransportes\TrackingClient;
use CrmDesenvolvimentos\AlfaTransportes\TrackingRequest;

$client = new TrackingClient('sua_chave_da_api');

$request = new TrackingRequest('99999', '00000000000000');

$response = $client->trackResponse($request);

if ($response->isSuccessful()) {
    echo $response->cteNumber();
    echo $response->expectedDate();
    echo $response->deliveredAt();
    echo $response->proofUrl();
} else {
    echo $response->httpStatusCode();
    echo $response->statusDescription() ?? $response->errorMessage();
}

print_r($response->occurrences());
print_r($response->toArray());
```

O exemplo local aceita numero da nota e CNPJ do remetente por argumento:

```bash
php examples/rastreamento.php 12345678 00000000000099
```

Campos enviados para rastreamento:

- `idr`: chave da API.
- `merNF`: numero da nota fiscal, obrigatorio.
- `cnpjTomador`: CNPJ do tomador, opcional.

A pagina de documentacao v1.3 lista `numeroNota`/`tomCnpj` na tabela, mas o formulario de teste da propria pagina envia `merNF`/`cnpjTomador`. A biblioteca usa `merNF`/`cnpjTomador`.

Status conhecidos do rastreamento:

- `1`: rastreamento nao concluido.
- `2`: rastreamento concluido com sucesso.
- `3`: falha de conexao com banco de dados.
- `4`: falta identificacao do remetente da nota.
- `5`: falha ao verificar identificacao.
- `6`: identificacao nao encontrada.
- `7`: falha ao recuperar os dados da nota fiscal.
- `8`: falta nota fiscal da mercadoria.
- `9`: nota fiscal nao encontrada neste CNPJ.

## Excecoes

Todas as excecoes do pacote implementam `CrmDesenvolvimentos\AlfaTransportes\Exception\AlfaTransportesException`.

## Desenvolvimento

```bash
composer install
composer run lint
composer test
```
