<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Interfaces\Pipeline;

use Hotaruma\Pipeline\Exception\{MiddlewareResolverInvalidArgumentException,
    NotFoundContainerException,
    PipelineEmptyStoreException,
    RequestHandlerResolverInvalidArgumentException};
use Hotaruma\Pipeline\Interfaces\{Resolver\MiddlewareResolverInterface,
    Resolver\RequestHandlerResolverInterface,
    MiddlewareStore\MiddlewareStoreInterface
};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

/**
 * @template TMiddlewareStore of MiddlewareStoreInterface<mixed, MiddlewareInterface>
 */
interface PipelineInterface extends RequestHandlerInterface, MiddlewareInterface
{
    /**
     * Add middleware to queue.
     *
     * @param TA_MiddlewareTypes|array<TA_MiddlewareTypes> $middleware
     * @return PipelineInterface
     *
     * @throws NotFoundContainerException|MiddlewareResolverInvalidArgumentException|RequestHandlerResolverInvalidArgumentException
     *
     * @phpstan-return static
     */
    public function pipe(MiddlewareInterface|RequestHandlerInterface|string|array $middleware): PipelineInterface;

    /**
     * Start pipeline without final handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     *
     * @throws PipelineEmptyStoreException|NotFoundContainerException|MiddlewareResolverInvalidArgumentException|RequestHandlerResolverInvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

    /**
     * Start pipeline.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     *
     * @throws PipelineEmptyStoreException|NotFoundContainerException|MiddlewareResolverInvalidArgumentException|RequestHandlerResolverInvalidArgumentException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;

    /**
     * Set middlewares store type.
     *
     * @param MiddlewareStoreInterface $middlewareStore
     * @return PipelineInterface
     *
     * @phpstan-param TMiddlewareStore $middlewareStore
     * @phpstan-return static
     */
    public function middlewareStore(MiddlewareStoreInterface $middlewareStore): PipelineInterface;

    /**
     * Set middleware resolver.
     *
     * @param MiddlewareResolverInterface $middlewareResolver
     * @return PipelineInterface
     *
     * @phpstan-return static
     */
    public function middlewareResolver(MiddlewareResolverInterface $middlewareResolver): PipelineInterface;

    /**
     * Set request handler resolver.
     *
     * @param RequestHandlerResolverInterface $handlerResolver
     * @return PipelineInterface
     *
     * @phpstan-return static
     */
    public function handlerResolver(RequestHandlerResolverInterface $handlerResolver): PipelineInterface;
}
