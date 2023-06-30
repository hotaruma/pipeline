<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline;

use Hotaruma\Pipeline\Exception\{PipelineEmptyStoreException, RequestHandlerResolverInvalidArgumentException};
use Hotaruma\Pipeline\Interfaces\MiddlewareStore\MiddlewareStoreInterface;
use Hotaruma\Pipeline\Resolver\{MiddlewareResolver, RequestHandlerResolver};
use Hotaruma\Pipeline\Interfaces\{Pipeline\PipelineInterface,
    Resolver\MiddlewareResolverInterface,
    Resolver\RequestHandlerResolverInterface
};
use Hotaruma\Pipeline\MiddlewareStore\MiddlewareStore;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * @template TMiddlewareStore of TA_MiddlewareStore
 *
 * @implements PipelineInterface<TMiddlewareStore>
 */
class Pipeline implements PipelineInterface
{
    /**
     * @var RequestHandlerInterface
     */
    protected RequestHandlerInterface $selfRequestHandlerInstance;

    /**
     * @param MiddlewareStoreInterface $middlewareStore
     * @param MiddlewareResolverInterface $middlewareResolver
     * @param RequestHandlerResolverInterface $handlerResolver
     *
     * @phpstan-param TMiddlewareStore $middlewareStore
     */
    public function __construct(
        protected MiddlewareStoreInterface        $middlewareStore = new MiddlewareStore(),
        protected MiddlewareResolverInterface     $middlewareResolver = new MiddlewareResolver(),
        protected RequestHandlerResolverInterface $handlerResolver = new RequestHandlerResolver()
    ) {
    }

    /**
     * @inheritDoc
     */
    public function pipe(MiddlewareInterface|RequestHandlerInterface|string|array $middleware): PipelineInterface
    {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];

        foreach ($middlewares as $middleware) {
            $middleware = $this->getMiddlewareResolver()->resolve($middleware);
            $this->getMiddlewareStore()->append($middleware);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface|PipelineInterface|callable|string $handler): ResponseInterface
    {
        $handler = $this->getHandlerResolver()->resolve($handler);
        $this->pipe($handler);

        return $this->handle($request);
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->getMiddlewareStore()->hasNext()) {
            throw new PipelineEmptyStoreException();
        }

        $next = $this->getMiddlewareStore()->receive();
        return match (true) {
            $next instanceof PipelineInterface => $next->process($request, $this->getSelfRequestHandlerInstance()),
            $next instanceof MiddlewareInterface => $next->process($request, $this),
            $next instanceof RequestHandlerInterface => $next->handle($request),
        };
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->getMiddlewareStore()->rewind();
    }

    /**
     * @inheritDoc
     */
    public function middlewareStore(MiddlewareStoreInterface $middlewareStore): PipelineInterface
    {
        $this->middlewareStore = $middlewareStore;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function middlewareResolver(MiddlewareResolverInterface $middlewareResolver): PipelineInterface
    {
        $this->middlewareResolver = $middlewareResolver;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function handlerResolver(RequestHandlerResolverInterface $handlerResolver): PipelineInterface
    {
        $this->handlerResolver = $handlerResolver;
        return $this;
    }

    /**
     * @return MiddlewareStoreInterface
     *
     * @phpstan-return TMiddlewareStore
     */
    protected function getMiddlewareStore(): MiddlewareStoreInterface
    {
        return $this->middlewareStore;
    }

    /**
     * @return MiddlewareResolverInterface
     */
    protected function getMiddlewareResolver(): MiddlewareResolverInterface
    {
        return $this->middlewareResolver;
    }

    /**
     * @return RequestHandlerResolverInterface
     */
    protected function getHandlerResolver(): RequestHandlerResolverInterface
    {
        return $this->handlerResolver;
    }

    /**
     * @return RequestHandlerInterface
     *
     * @throws RequestHandlerResolverInvalidArgumentException
     */
    protected function getSelfRequestHandlerInstance(): RequestHandlerInterface
    {
        return $this->selfRequestHandlerInstance ??= $this->getHandlerResolver()->resolve($this);
    }
}
