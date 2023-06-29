<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Exception;

use Hotaruma\Pipeline\Interfaces\Exception\PipelineExceptionInterface;
use RuntimeException;

class NotFoundContainerException extends RuntimeException implements PipelineExceptionInterface
{
    public function __construct()
    {
        parent::__construct('The container was not found.');
    }
}
