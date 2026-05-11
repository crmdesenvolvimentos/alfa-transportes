<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes;

final class QuoteResponse
{
    /** @var array<string, mixed> */
    private $data;

    /**
     * @param array<string, mixed> $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function statusCode(): ?int
    {
        return isset($this->data['status']['numero']) ? (int) $this->data['status']['numero'] : null;
    }

    public function statusDescription(): ?string
    {
        return isset($this->data['status']['descricao']) ? (string) $this->data['status']['descricao'] : null;
    }

    public function httpStatusCode(): ?int
    {
        return isset($this->data['_http_status']) ? (int) $this->data['_http_status'] : null;
    }

    public function errorMessage(): ?string
    {
        return isset($this->data['error']) ? (string) $this->data['error'] : null;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode() === 1;
    }

    public function quoteId(): ?string
    {
        if (isset($this->data['cotacao']['codigoCotacao'])) {
            return (string) $this->data['cotacao']['codigoCotacao'];
        }

        return isset($this->data['id']) ? (string) $this->data['id'] : null;
    }

    public function totalValue(): ?float
    {
        $path = $this->data['cotacao']['emissao']['valoresCotacao']['valorTotal'] ?? null;

        return $path === null ? null : (float) $path;
    }

    public function deliveryEstimate(): ?string
    {
        $path = $this->data['cotacao']['emissao']['diasEntrega'] ?? null;

        return $path === null ? null : (string) $path;
    }
}
