<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Interfaces\Resolver;

use Hotaruma\Pipeline\Exception\RequestHandlerResolverInvalidArgumentException;
use Hotaruma\Pipeline\Interfaces\Pipeline\PipelineInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerResolverInterface extends ResolverConfigInterface
{
    /**
     * Generate request handler.
     *
     * @param RequestHandlerInterface|PipelineInterface|callable|string $handler
     * @return RequestHandlerInterface
     *
     * @throws RequestHandlerResolverInvalidArgumentException
     *
     * @phpstan-param TA_RequestHandlerTypes $handler
     */
    public function resolve(
        RequestHandlerInterface|PipelineInterface|callable|string $handler
    ): RequestHandlerInterface;
}
