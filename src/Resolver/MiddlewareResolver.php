<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Resolver;

use Hotaruma\Pipeline\Exception\{MiddlewareResolverInvalidArgumentException, NotFoundContainerException};
use Hotaruma\Pipeline\Interfaces\Resolver\MiddlewareResolverInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

final class MiddlewareResolver extends Resolver implements MiddlewareResolverInterface
{
    /**
     * @inheritDoc
     */
    public function container(ContainerInterface $container): MiddlewareResolverInterface
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resolve(MiddlewareInterface|RequestHandlerInterface|string $middleware): MiddlewareInterface
    {
        return match (true) {
            is_string($middleware) => $this->middlewareByString($middleware),
            $middleware instanceof RequestHandlerInterface => $this->middlewareByRequestHandler($middleware),
            default => $middleware
        };
    }

    /**
     * Create middleware by request class name.
     *
     * @param class-string<RequestHandlerInterface|MiddlewareInterface> $className
     * @return MiddlewareInterface
     *
     * @throws NotFoundContainerException|MiddlewareResolverInvalidArgumentException
     */
    protected function middlewareByString(string $className): MiddlewareInterface
    {
        return new class ($className, $this->getContainer()) implements MiddlewareInterface {
            /**
             * @param class-string<RequestHandlerInterface|MiddlewareInterface> $className
             * @param ContainerInterface $container
             */
            public function __construct(
                protected string             $className,
                protected ContainerInterface $container
            ) {
            }

            /**
             * @param ServerRequestInterface $request
             * @param RequestHandlerInterface $handler
             *
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface  $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $middleware = $this->container->get($this->className);
                return match (true) {
                    $middleware instanceof RequestHandlerInterface => $middleware->handle($request),
                    $middleware instanceof MiddlewareInterface => $middleware->process($request, $handler),
                    default =>
                    throw new MiddlewareResolverInvalidArgumentException("Invalid middleware provided.")
                };
            }
        };
    }

    /**
     * Create middleware by request handler.
     *
     * @param RequestHandlerInterface $handler
     * @return MiddlewareInterface
     */
    protected function middlewareByRequestHandler(RequestHandlerInterface $handler): MiddlewareInterface
    {
        return new class ($handler) implements MiddlewareInterface {
            /**
             * @param RequestHandlerInterface $handler
             */
            public function __construct(
                protected RequestHandlerInterface $handler
            ) {
            }

            /**
             * @param ServerRequestInterface $request
             * @param RequestHandlerInterface $handler
             *
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface  $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $this->handler->handle($request);
            }
        };
    }
}
