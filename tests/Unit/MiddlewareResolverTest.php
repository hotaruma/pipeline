<?php

declare(strict_types=1);

namespace Tests\Unit;

use Hotaruma\Pipeline\Exception\MiddlewareResolverInvalidArgumentException;
use Hotaruma\Pipeline\Exception\NotFoundContainerException;
use Hotaruma\Pipeline\Resolver\MiddlewareResolver;
use PHPUnit\Framework\{MockObject\Exception, TestCase};
use Psr\Container\ContainerInterface;
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use stdClass;

class MiddlewareResolverTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testResolveWithString(): void
    {
        $resolver = new MiddlewareResolver();

        $container = $this->createMock(ContainerInterface::class);
        $resolver->container($container);

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $result = $resolver->resolve($requestHandler::class);
        $this->assertInstanceOf(MiddlewareInterface::class, $result);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $result = $resolver->resolve($middleware::class);
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testResolveWithObject(): void
    {
        $resolver = new MiddlewareResolver();

        $container = $this->createMock(ContainerInterface::class);
        $resolver->container($container);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $result = $resolver->resolve($handler);
        $this->assertInstanceOf(RequestHandlerInterface::class, $result);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $result = $resolver->resolve($middleware);
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testResolveWithInvalidMiddleware(): void
    {
        $resolver = new MiddlewareResolver();

        $container = $this->createMock(ContainerInterface::class);
        $resolver->container($container);

        $this->expectException(MiddlewareResolverInvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $resolver->resolve(stdClass::class);
    }

    /**
     * @throws Exception
     */
    public function testInvalidContainer(): void
    {
        $resolver = new MiddlewareResolver();

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->expectException(NotFoundContainerException::class);
        $resolver->resolve($requestHandler::class);
    }
}
