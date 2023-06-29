<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Exception;

use Hotaruma\Pipeline\Interfaces\Exception\PipelineExceptionInterface;
use RuntimeException;

class RequestHandlerResolverInvalidArgumentException extends RuntimeException implements PipelineExceptionInterface
{
}
