<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes;

use CrmDesenvolvimentos\AlfaTransportes\Exception\InvalidPayloadException;

final class QuoteRequest
{
    public const CUSTOMER_LEGAL_PERSON = 1;
    public const CUSTOMER_NATURAL_PERSON = 2;
    public const PAYER_CIF = 1;
    public const PAYER_FOB = 2;

    /** @var int */
    private $customerType;

    /** @var string|null */
    private $customerDocument;

    /** @var string */
    private $customerZipCode;

    /** @var float */
    private $goodsValue;

    /** @var float */
    private $grossWeight;

    /** @var float */
    private $cubicMeters;

    /** @var int|null */
    private $volumeQuantity;

    /** @var bool */
    private $chemicalProduct = false;

    /** @var string|null */
    private $shippingDate;

    /** @var string|null */
    private $senderZipCode;

    /** @var string|null */
    private $senderDocument;

    /** @var bool|null */
    private $ruralArea;

    /** @var int|null */
    private $payerType;

    public function __construct(
        int $customerType,
        string $customerZipCode,
        float $goodsValue,
        float $grossWeight,
        float $cubicMeters
    ) {
        $this->customerType = $customerType;
        $this->customerZipCode = self::onlyDigits($customerZipCode);
        $this->goodsValue = $goodsValue;
        $this->grossWeight = $grossWeight;
        $this->cubicMeters = $cubicMeters;

        $this->validate();
    }

    public static function forNaturalPerson(string $cpf, string $zipCode, float $goodsValue, float $grossWeight, float $cubicMeters): self
    {
        return (new self(self::CUSTOMER_NATURAL_PERSON, $zipCode, $goodsValue, $grossWeight, $cubicMeters))
            ->customerDocument($cpf);
    }

    public static function forLegalPerson(string $cnpj, string $zipCode, float $goodsValue, float $grossWeight, float $cubicMeters): self
    {
        return (new self(self::CUSTOMER_LEGAL_PERSON, $zipCode, $goodsValue, $grossWeight, $cubicMeters))
            ->customerDocument($cnpj);
    }

    public function customerDocument(string $document): self
    {
        $this->customerDocument = self::onlyDigits($document);

        return $this;
    }

    public function sender(string $document, string $zipCode): self
    {
        $this->senderDocument = self::onlyDigits($document);
        $this->senderZipCode = self::onlyDigits($zipCode);

        return $this;
    }

    public function volumeQuantity(int $quantity): self
    {
        if ($quantity < 1) {
            throw new InvalidPayloadException('A quantidade de volumes deve ser maior que zero.');
        }

        $this->volumeQuantity = $quantity;

        return $this;
    }

    public function chemicalProduct(bool $chemicalProduct = true): self
    {
        $this->chemicalProduct = $chemicalProduct;

        return $this;
    }

    public function shippingDate(string $date): self
    {
        $normalized = preg_replace('/[^0-9]/', '', $date);

        if ($normalized === null || strlen($normalized) !== 8) {
            throw new InvalidPayloadException('A data de embarque deve estar no formato YYYYMMDD ou DDMMYYYY esperado pela API.');
        }

        $this->shippingDate = $normalized;

        return $this;
    }

    public function ruralArea(bool $ruralArea = true): self
    {
        $this->ruralArea = $ruralArea;

        return $this;
    }

    public function payerType(int $payerType): self
    {
        if (!in_array($payerType, [self::PAYER_CIF, self::PAYER_FOB], true)) {
            throw new InvalidPayloadException('Tipo de pagador invalido. Use 1 para CIF ou 2 para FOB.');
        }

        $this->payerType = $payerType;

        return $this;
    }

    /**
     * @return array<string, int|float|string>
     */
    public function toPayload(string $apiKey): array
    {
        $this->validate();

        $payload = [
            'idr' => $apiKey,
            'cliTip' => $this->customerType,
            'cliCep' => $this->customerZipCode,
            'merVlr' => $this->goodsValue,
            'merPeso' => $this->grossWeight,
            'merM3' => $this->cubicMeters,
            'quim' => $this->chemicalProduct ? 1 : 0,
            'modoJson' => 1,
        ];

        if ($this->customerDocument !== null) {
            $payload['cliCnpj'] = $this->customerDocument;
        }

        if ($this->volumeQuantity !== null) {
            $payload['merVol'] = $this->volumeQuantity;
        }

        if ($this->shippingDate !== null) {
            $payload['dtEmbarque'] = $this->shippingDate;
        }

        if ($this->senderZipCode !== null) {
            $payload['cepRem'] = $this->senderZipCode;
        }

        if ($this->senderDocument !== null) {
            $payload['cnpjRem'] = $this->senderDocument;
        }

        if ($this->ruralArea !== null) {
            $payload['zonaRural'] = $this->ruralArea ? 1 : 0;
        }

        if ($this->payerType !== null) {
            $payload['tipoPagador'] = $this->payerType;
        }

        return $payload;
    }

    public static function moneyToFloat(string $value): float
    {
        $normalized = str_replace(['.', ','], ['', '.'], trim($value));

        return (float) $normalized;
    }

    private function validate(): void
    {
        if (!in_array($this->customerType, [self::CUSTOMER_LEGAL_PERSON, self::CUSTOMER_NATURAL_PERSON], true)) {
            throw new InvalidPayloadException('Tipo de destinatario invalido. Use 1 para pessoa juridica ou 2 para pessoa fisica.');
        }

        if (strlen($this->customerZipCode) !== 8) {
            throw new InvalidPayloadException('CEP do destinatario deve conter 8 digitos.');
        }

        if ($this->goodsValue <= 0) {
            throw new InvalidPayloadException('Valor da mercadoria deve ser maior que zero.');
        }

        if ($this->grossWeight <= 0) {
            throw new InvalidPayloadException('Peso bruto deve ser maior que zero.');
        }

        if ($this->cubicMeters <= 0) {
            throw new InvalidPayloadException('Metro cubico deve ser maior que zero.');
        }
    }

    private static function onlyDigits(string $value): string
    {
        return (string) preg_replace('/\D+/', '', $value);
    }
}
