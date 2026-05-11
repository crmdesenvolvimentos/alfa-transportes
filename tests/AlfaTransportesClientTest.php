<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes\Tests;

use CrmDesenvolvimentos\AlfaTransportes\AlfaTransportesClient;
use CrmDesenvolvimentos\AlfaTransportes\QuoteRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class AlfaTransportesClientTest extends TestCase
{
    public function testSendsPostJsonRequestAndParsesResponse(): void
    {
        $history = [];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'status' => [
                    'numero' => 1,
                    'descricao' => 'COTACAO CONCLUIDA COM SUCESSO',
                ],
                'cotacao' => [
                    'codigoCotacao' => '123',
                    'emissao' => [
                        'valoresCotacao' => [
                            'valorTotal' => 99.9,
                        ],
                        'diasEntrega' => '3 DIAS UTEIS',
                    ],
                ],
            ])),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $httpClient = new Client([
            'base_uri' => AlfaTransportesClient::DEFAULT_BASE_URI,
            'handler' => $stack,
        ]);

        $client = new AlfaTransportesClient('api-key', $httpClient);
        $response = $client->quoteResponse(QuoteRequest::forNaturalPerson('00000000000', '88888888', 3790.0, 5.0, 0.01));

        self::assertTrue($response->isSuccessful());
        self::assertSame(99.9, $response->totalValue());
        self::assertSame('3 DIAS UTEIS', $response->deliveryEstimate());
        self::assertCount(1, $history);
        self::assertSame('POST', $history[0]['request']->getMethod());
        self::assertSame('/cotacao/v1.2/', (string) $history[0]['request']->getUri()->getPath());
        self::assertSame('application/json', $history[0]['request']->getHeaderLine('Content-Type'));
        self::assertSame('api-key', json_decode((string) $history[0]['request']->getBody(), true)['idr']);
    }
}
