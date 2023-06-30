<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Resolver;

use Hotaruma\Pipeline\Exception\{MiddlewareResolverInvalidArgumentException, NotFoundContainerException};
use Hotaruma\Pipeline\Interfaces\Resolver\MiddlewareResolverInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class MiddlewareResolver extends Resolver implements MiddlewareResolverInterface
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
    public function resolve(MiddlewareInterface|RequestHandlerInterface|string $middleware): MiddlewareInterface|RequestHandlerInterface
    {
        return match (true) {
            is_string($middleware) => $this->middlewareByString($middleware),
            default => $middleware
        };
    }

    /**
     * Create middleware by request class name.
     *
     * @param string $className
     * @return MiddlewareInterface
     *
     * @throws NotFoundContainerException|MiddlewareResolverInvalidArgumentException
     *
     * @phpstan-param TA_MIddlewareResolverStringType $className
     */
    protected function middlewareByString(string $className): MiddlewareInterface
    {
        if (
            !is_subclass_of($className, RequestHandlerInterface::class) &&
            !is_subclass_of($className, MiddlewareInterface::class)
        ) {
            throw new MiddlewareResolverInvalidArgumentException("Invalid middleware provided.");
        }

        return new class ($className, $this->getContainer()) implements MiddlewareInterface {
            /**
             * @param string $className
             * @param ContainerInterface $container
             *
             * @phpstan-param TA_MIddlewareResolverStringType $className
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
}
