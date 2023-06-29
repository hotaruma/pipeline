<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Exception;

use Hotaruma\Pipeline\Interfaces\Exception\PipelineExceptionInterface;
use OutOfRangeException;

class PipelineEmptyStoreException extends OutOfRangeException implements PipelineExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Pipeline store is empty');
    }
}
