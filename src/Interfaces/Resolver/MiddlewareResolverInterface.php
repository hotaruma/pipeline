<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Interfaces\Resolver;

use Hotaruma\Pipeline\Exception\{MiddlewareResolverInvalidArgumentException, NotFoundContainerException};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

interface MiddlewareResolverInterface extends ResolverConfigInterface
{
    /**
     * Generate middleware.
     *
     * @param MiddlewareInterface|RequestHandlerInterface|string $middleware
     * @return MiddlewareInterface
     *
     * @throws NotFoundContainerException|MiddlewareResolverInvalidArgumentException
     *
     * @phpstan-param TA_MiddlewareTypes $middleware
     */
    public function resolve(MiddlewareInterface|RequestHandlerInterface|string $middleware): MiddlewareInterface;
}
