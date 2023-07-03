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

class Pipeline implements PipelineInterface
{
    /**
     * @var RequestHandlerInterface
     */
    protected RequestHandlerInterface $selfRequestHandlerInstance;

    /**
     * @var bool
     */
    protected bool $rewindStatus;

    /**
     * @var MiddlewareStoreInterface
     *
     * @phpstan-var TA_MiddlewareStore
     */
    protected MiddlewareStoreInterface $middlewareStore;

    /**
     * @var MiddlewareResolverInterface
     */
    protected MiddlewareResolverInterface $middlewareResolver;

    /**
     * @var RequestHandlerResolverInterface
     */
    protected RequestHandlerResolverInterface $requestHandlerResolver;

    /**
     * @inheritDoc
     */
    public function pipe(MiddlewareInterface|RequestHandlerInterface|string ...$middlewares): PipelineInterface
    {
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
        $handler = $this->getRequestHandlerResolver()->resolve($handler);
        $this->pipe($handler);

        return $this->handle($request);
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->getRewindStatus()) {
            $this->rewind();
        }
        if (!$this->getMiddlewareStore()->valid()) {
            throw new PipelineEmptyStoreException();
        }

        $next = $this->getMiddlewareStore()->current();
        $this->getMiddlewareStore()->next();
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
        $this->rewindStatus(true);
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
    public function requestHandlerResolver(RequestHandlerResolverInterface $handlerResolver): PipelineInterface
    {
        $this->requestHandlerResolver = $handlerResolver;
        return $this;
    }

    /**
     * @return MiddlewareResolverInterface
     */
    public function getMiddlewareResolver(): MiddlewareResolverInterface
    {
        return $this->middlewareResolver ??= new MiddlewareResolver();
    }

    /**
     * @return RequestHandlerResolverInterface
     */
    public function getRequestHandlerResolver(): RequestHandlerResolverInterface
    {
        return $this->requestHandlerResolver ??= new RequestHandlerResolver();
    }

    /**
     * @return MiddlewareStoreInterface
     *
     * @phpstan-return TA_MiddlewareStore
     */
    protected function getMiddlewareStore(): MiddlewareStoreInterface
    {
        return $this->middlewareStore ??= new MiddlewareStore();
    }

    /**
     * @return RequestHandlerInterface
     *
     * @throws RequestHandlerResolverInvalidArgumentException
     */
    protected function getSelfRequestHandlerInstance(): RequestHandlerInterface
    {
        return $this->selfRequestHandlerInstance ??= $this->getRequestHandlerResolver()->resolve($this);
    }

    /**
     * @param bool $status
     * @return void
     */
    protected function rewindStatus(bool $status): void
    {
        $this->rewindStatus = $status;
    }

    /**
     * @return bool
     */
    protected function getRewindStatus(): bool
    {
        return $this->rewindStatus ??= false;
    }
}
