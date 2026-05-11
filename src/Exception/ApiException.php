<?php

declare(strict_types=1);

namespace CrmDesenvolvimentos\AlfaTransportes\Exception;

use RuntimeException;

final class ApiException extends RuntimeException implements AlfaTransportesException
{
}
