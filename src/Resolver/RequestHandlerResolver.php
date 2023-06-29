<?php

namespace Hotaruma\Pipeline\Resolver;

use Hotaruma\Pipeline\Exception\RequestHandlerResolverInvalidArgumentException;
use Hotaruma\Pipeline\Interfaces\{Pipeline\PipelineInterface,
    Resolver\RequestHandlerResolverInterface
};
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{RequestHandlerInterface};

final class RequestHandlerResolver extends Resolver implements RequestHandlerResolverInterface
{
    /**
     * @inheritDoc
     */
    public function container(ContainerInterface $container): RequestHandlerResolverInterface
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resolve(RequestHandlerInterface|PipelineInterface|callable|string $handler): RequestHandlerInterface
    {
        return match (true) {
            is_string($handler) => $this->requestHandlerByString($handler),
            is_callable($handler) => $this->requestHandlerByCallable($handler),
            $handler instanceof PipelineInterface => $this->requestHandlerByPipeline($handler),
            default => $handler
        };
    }

    /**
     * Create request handler by callable.
     *
     * @param callable(ServerRequestInterface): ResponseInterface $handler
     * @return RequestHandlerInterface
     */
    protected function requestHandlerByCallable(callable $handler): RequestHandlerInterface
    {
        return new class ($handler) implements RequestHandlerInterface {
            /**
             * @param callable(ServerRequestInterface): ResponseInterface $handler
             */
            public function __construct(
                protected mixed $handler
            ) {
            }

            /**
             * @param ServerRequestInterface $request
             * @return ResponseInterface
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return ($this->handler)($request);
            }
        };
    }

    /**
     * Create request handler by class name.
     *
     * @param string $handler
     * @return RequestHandlerInterface
     *
     * @throws RequestHandlerResolverInvalidArgumentException
     *
     * @phpstan-param (callable&string)|class-string $handler
     */
    protected function requestHandlerByString(string $handler): RequestHandlerInterface
    {
        return new class ($handler, $this->getContainer()) implements RequestHandlerInterface {
            /**
             * @param (callable&string)|class-string $handler
             * @param ContainerInterface $container
             */
            public function __construct(
                protected mixed              $handler,
                protected ContainerInterface $container
            ) {
            }

            /**
             * @param ServerRequestInterface $request
             * @return ResponseInterface
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $handler = class_exists($this->handler) ? $this->container->get($this->handler) : $this->handler;
                return match (true) {
                    $handler instanceof RequestHandlerInterface => $handler->handle($request),
                    is_callable($handler) => $handler($request),
                    default =>
                    throw new RequestHandlerResolverInvalidArgumentException("Invalid request handler provided.")
                };
            }
        };
    }

    /**
     * Create request handler by pipeline.
     *
     * @param PipelineInterface $pipeline
     * @return RequestHandlerInterface
     *
     * @phpstan-param TA_Pipeline $pipeline
     */
    protected function requestHandlerByPipeline(PipelineInterface $pipeline): RequestHandlerInterface
    {
        return new class ($pipeline) implements RequestHandlerInterface {
            /**
             * @param PipelineInterface $pipeline
             *
             * @phpstan-param TA_Pipeline $pipeline
             */
            public function __construct(
                protected PipelineInterface $pipeline
            ) {
            }

            /**
             * @param ServerRequestInterface $request
             * @return ResponseInterface
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->pipeline->handle($request);
            }
        };
    }
}
