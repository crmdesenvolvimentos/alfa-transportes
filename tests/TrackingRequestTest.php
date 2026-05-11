<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes\Tests;

use CrmDesenvolvimentos\AlfaTransportes\TrackingRequest;
use PHPUnit\Framework\TestCase;

final class TrackingRequestTest extends TestCase
{
    public function testBuildsPayloadWithNormalizedPayerDocument(): void
    {
        $request = new TrackingRequest('01', '00.000.000/0000-00');

        self::assertSame([
            'idr' => 'api-key',
            'merNF' => '01',
            'cnpjTomador' => '00000000000000',
        ], $request->toPayload('api-key'));
    }
}
