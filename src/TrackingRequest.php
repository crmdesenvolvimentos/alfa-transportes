<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes;

use CrmDesenvolvimentos\AlfaTransportes\Exception\InvalidPayloadException;

final class TrackingRequest
{
    /** @var string */
    private $invoiceNumber;

    /** @var string|null */
    private $payerDocument;

    /**
     * @param int|string $invoiceNumber
     */
    public function __construct($invoiceNumber, ?string $payerDocument = null)
    {
        $normalizedInvoiceNumber = trim((string) $invoiceNumber);

        if ($normalizedInvoiceNumber === '' || !ctype_digit($normalizedInvoiceNumber)) {
            throw new InvalidPayloadException('Numero da nota fiscal deve ser maior que zero.');
        }

        $this->invoiceNumber = $normalizedInvoiceNumber;

        if ($payerDocument !== null) {
            $this->payerDocument = self::onlyDigits($payerDocument);

            if (strlen($this->payerDocument) !== 14) {
                throw new InvalidPayloadException('CNPJ do tomador deve conter 14 digitos.');
            }
        }
    }

    /**
     * @return array<string, int|string>
     */
    public function toPayload(string $apiKey): array
    {
        $payload = [
            'idr' => $apiKey,
            'merNF' => $this->invoiceNumber,
        ];

        if ($this->payerDocument !== null) {
            $payload['cnpjTomador'] = $this->payerDocument;
        }

        return $payload;
    }

    private static function onlyDigits(string $value): string
    {
        return (string) preg_replace('/\D+/', '', $value);
    }
}
