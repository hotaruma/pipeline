<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Exception;

use Hotaruma\Pipeline\Interfaces\Exception\PipelineExceptionInterface;
use OutOfRangeException;

class MiddlewareStoreOutOfRangeException extends OutOfRangeException implements PipelineExceptionInterface
{
}
