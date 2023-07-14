<?php

declare(strict_types=1);

namespace Hotaruma\Tests\Unit;

use Hotaruma\Pipeline\Exception\{NotFoundContainerException, RequestHandlerResolverInvalidArgumentException};
use Hotaruma\Pipeline\Interfaces\Pipeline\PipelineInterface;
use Hotaruma\Pipeline\Resolver\RequestHandlerResolver;
use PHPUnit\Framework\{MockObject\Exception, TestCase};
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

class RequestHandlerResolverTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testResolveWithString(): void
    {
        $resolver = new RequestHandlerResolver();

        $container = $this->createMock(ContainerInterface::class);
        $resolver->container($container);

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $result = $resolver->resolve($requestHandler::class);
        $this->assertInstanceOf(RequestHandlerInterface::class, $result);

        $pipeline = $this->createMock(PipelineInterface::class);
        $result = $resolver->resolve($pipeline::class);
        $this->assertInstanceOf(RequestHandlerInterface::class, $result);

        /** @phpstan-ignore-next-line */
        $result = $resolver->resolve('strlen');
        $this->assertInstanceOf(RequestHandlerInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testResolveWithCallable(): void
    {
        $resolver = new RequestHandlerResolver();

        $response = $this->createMock(ResponseInterface::class);
        $result = $resolver->resolve(function (ServerRequestInterface $request) use ($response): ResponseInterface {
            $request->getBody();
            return $response;
        });
        $this->assertInstanceOf(RequestHandlerInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testResolveWithObject(): void
    {
        $resolver = new RequestHandlerResolver();

        $handler = $this->createMock(PipelineInterface::class);
        $result = $resolver->resolve($handler);
        $this->assertInstanceOf(RequestHandlerInterface::class, $result);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $result = $resolver->resolve($handler);
        $this->assertInstanceOf(RequestHandlerInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testResolveWithInvalidHandler(): void
    {
        $resolver = new RequestHandlerResolver();

        $container = $this->createMock(ContainerInterface::class);
        $resolver->container($container);

        $this->expectException(RequestHandlerResolverInvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $resolver->resolve(stdClass::class);
    }

    /**
     * @throws Exception
     */
    public function testInvalidContainer(): void
    {
        $resolver = new RequestHandlerResolver();

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->expectException(NotFoundContainerException::class);
        $resolver->resolve($requestHandler::class);
    }
}
