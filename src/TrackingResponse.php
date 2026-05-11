<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes;

final class TrackingResponse
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
        if (isset($this->data['status'])) {
            return (int) $this->data['status'];
        }

        return isset($this->data['Status']) ? (int) $this->data['Status'] : null;
    }

    public function statusDescription(): ?string
    {
        if (isset($this->data['nome'])) {
            return (string) $this->data['nome'];
        }

        return isset($this->data['Nome']) ? (string) $this->data['Nome'] : null;
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
        return in_array($this->statusCode(), [1, 2]);
    }

    public function isDelivered(): bool
    {
        return $this->statusCode() === 2;
    }

    public function shipperName(): ?string
    {
        return isset($this->data['dadosRemetente']['nomeRemetente']) ? (string) $this->data['dadosRemetente']['nomeRemetente'] : null;
    }

    public function recipientName(): ?string
    {
        return isset($this->data['dadosCte']['nomeDestinatario']) ? (string) $this->data['dadosCte']['nomeDestinatario'] : null;
    }

    public function cteNumber(): ?string
    {
        return isset($this->data['dadosCte']['numeroCte']) ? (string) $this->data['dadosCte']['numeroCte'] : null;
    }

    public function expectedDate(): ?string
    {
        return isset($this->data['dadosCte']['dataPrivista']) ? (string) $this->data['dadosCte']['dataPrivista'] : null;
    }

    public function deliveredAt(): ?string
    {
        return isset($this->data['dadosEntrega']['dataEntrega']) ? (string) $this->data['dadosEntrega']['dataEntrega'] : null;
    }

    public function proofUrl(): ?string
    {
        return isset($this->data['dadosEntrega']['urlComprovante']) ? (string) $this->data['dadosEntrega']['urlComprovante'] : null;
    }

    /**
     * @return array<int, mixed>
     */
    public function occurrences(): array
    {
        return isset($this->data['ocorrenciasExtras']) && is_array($this->data['ocorrenciasExtras'])
            ? $this->data['ocorrenciasExtras']
            : [];
    }

    /**
     * @return array<int, mixed>
     */
    public function shipments(): array
    {
        return isset($this->data['dadosEmbarque']) && is_array($this->data['dadosEmbarque'])
            ? $this->data['dadosEmbarque']
            : [];
    }
}
