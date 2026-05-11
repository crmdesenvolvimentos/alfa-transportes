<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes\Tests;

use CrmDesenvolvimentos\AlfaTransportes\QuoteRequest;
use PHPUnit\Framework\TestCase;

final class QuoteRequestTest extends TestCase
{
    public function testBuildsPayloadWithNormalizedDocumentsAndZipCodes(): void
    {
        $request = QuoteRequest::forNaturalPerson('000.000.000-00', '88888-888', 3790.0, 5.0, 0.01)
            ->sender('00.000.000/0000-00', '88888-888')
            ->volumeQuantity(1);

        self::assertSame([
            'idr' => 'api-key',
            'cliTip' => 2,
            'cliCep' => '88888888',
            'merVlr' => 3790.0,
            'merPeso' => 5.0,
            'merM3' => 0.01,
            'quim' => 0,
            'modoJson' => 1,
            'cliCnpj' => '00000000000000',
            'merVol' => 1,
            'cepRem' => '88888888',
            'cnpjRem' => '00000000000000',
        ], $request->toPayload('api-key'));
    }

    public function testConvertsBrazilianMoneyFormat(): void
    {
        self::assertSame(3790.0, QuoteRequest::moneyToFloat('3.790,00'));
    }
}
