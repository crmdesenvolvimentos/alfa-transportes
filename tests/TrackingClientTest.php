<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes\Tests;

use CrmDesenvolvimentos\AlfaTransportes\TrackingClient;
use CrmDesenvolvimentos\AlfaTransportes\TrackingRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class TrackingClientTest extends TestCase
{
    public function testSendsPostJsonRequestAndParsesResponse(): void
    {
        $history = [];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'status' => 2,
                'nome' => 'RASTREAMENTO CONCLUIDO COM SUCESSO',
                'dadosCte' => [
                    'numeroCte' => '999999',
                    'dataPrivista' => '2023-04-10',
                ],
                'dadosEntrega' => [
                    'dataEntrega' => '2023-04-05 15:03:00',
                    'urlComprovante' => 'https://example.com/comprovante',
                ],
                'ocorrenciasExtras' => [
                    [
                        'codigoOcorrencia' => 0,
                        'descricaoOcorrencia' => 'PROCESSO DE TRANSPORTE INICIADO',
                    ],
                ],
            ])),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $httpClient = new Client([
            'base_uri' => TrackingClient::DEFAULT_BASE_URI,
            'handler' => $stack,
        ]);

        $client = new TrackingClient('api-key', $httpClient);
        $response = $client->trackResponse(new TrackingRequest('01', '00000000000000'));

        self::assertTrue($response->isSuccessful());
        self::assertSame('999999', $response->cteNumber());
        self::assertSame('2023-04-10', $response->expectedDate());
        self::assertSame('2023-04-05 15:03:00', $response->deliveredAt());
        self::assertSame('https://example.com/comprovante', $response->proofUrl());
        self::assertCount(1, $response->occurrences());
        self::assertCount(1, $history);
        self::assertSame('POST', $history[0]['request']->getMethod());
        self::assertSame('/rastreamento/v1.3/', (string) $history[0]['request']->getUri()->getPath());
        self::assertSame('application/json', $history[0]['request']->getHeaderLine('Content-Type'));
        self::assertSame('api-key', json_decode((string) $history[0]['request']->getBody(), true)['idr']);
    }

    public function testReturnsJsonBodyFromHttpErrorResponse(): void
    {
        $mock = new MockHandler([
            new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Erro critico na consulta principal',
            ])),
        ]);

        $httpClient = new Client([
            'base_uri' => TrackingClient::DEFAULT_BASE_URI,
            'handler' => HandlerStack::create($mock),
        ]);

        $client = new TrackingClient('api-key', $httpClient);
        $response = $client->track(new TrackingRequest('99999999'));

        self::assertSame(500, $response['_http_status']);
        self::assertSame('Erro critico na consulta principal', $response['error']);
    }

    public function testReadsUppercaseStatusAndNameFromApi(): void
    {
        $response = \CrmDesenvolvimentos\AlfaTransportes\TrackingResponse::fromArray([
            'Status' => 9,
            'Nome' => 'NOTA FISCAL NAO ENCONTRADA NESTE CNPJ',
            '_http_status' => 200,
        ]);

        self::assertSame(9, $response->statusCode());
        self::assertSame('NOTA FISCAL NAO ENCONTRADA NESTE CNPJ', $response->statusDescription());
        self::assertSame(200, $response->httpStatusCode());
    }
}
