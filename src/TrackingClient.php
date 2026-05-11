<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes;

use CrmDesenvolvimentos\AlfaTransportes\Exception\AlfaTransportesException;
use CrmDesenvolvimentos\AlfaTransportes\Exception\ApiException;
use CrmDesenvolvimentos\AlfaTransportes\Exception\InvalidPayloadException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

final class TrackingClient
{
    public const DEFAULT_BASE_URI = 'https://api.alfatransportes.com.br/rastreamento/v1.3/';

    /** @var ClientInterface */
    private $httpClient;

    /** @var string */
    private $apiKey;

    /** @var array<string, mixed> */
    private $defaultOptions;

    /**
     * @param array<string, mixed> $defaultOptions Guzzle request options.
     */
    public function __construct(string $apiKey, ?ClientInterface $httpClient = null, array $defaultOptions = [])
    {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?: new Client([
            'base_uri' => self::DEFAULT_BASE_URI,
            'timeout' => 30,
        ]);
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws AlfaTransportesException
     */
    public function track(TrackingRequest $request): array
    {
        try {
            $response = $this->httpClient->request('POST', '', array_replace_recursive($this->defaultOptions, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false,
                'json' => $request->toPayload($this->apiKey),
            ]));
        } catch (GuzzleException $exception) {
            throw new ApiException('Falha ao comunicar com a API de rastreamento da Alfa Transportes.', 0, $exception);
        }

        return $this->decodeJsonResponse($response);
    }

    /**
     * @throws AlfaTransportesException
     */
    public function trackResponse(TrackingRequest $request): TrackingResponse
    {
        return TrackingResponse::fromArray($this->track($request));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws InvalidPayloadException
     */
    private function decodeJsonResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            throw new InvalidPayloadException('A API retornou uma resposta JSON invalida.');
        }

        $decoded['_http_status'] = $response->getStatusCode();

        return $decoded;
    }
}
