<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes\Exception;

use InvalidArgumentException;

final class InvalidPayloadException extends InvalidArgumentException implements AlfaTransportesException
{
}
