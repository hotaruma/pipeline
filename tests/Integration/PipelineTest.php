<?php

declare(strict_types=1);

namespace Tests\Integration;

use Hotaruma\Pipeline\Exception\PipelineEmptyStoreException;
use Hotaruma\Pipeline\Interfaces\Pipeline\PipelineInterface;
use Hotaruma\Pipeline\Pipeline;
use Hotaruma\Pipeline\Resolver\MiddlewareResolver;
use Hotaruma\Pipeline\Resolver\RequestHandlerResolver;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class PipelineTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testPipeObject(): void
    {
        $pipeline = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();
        $middleware3 = $this->createMiddleware();
        $middleware4 = $this->createMiddleware();

        $handler = $this->createRequestHandler($response);

        $pipeline->pipe($middleware1);
        $pipeline->pipe($middleware2);
        $pipeline->pipe($middleware3, $middleware4);
        $pipeline->pipe($handler);

        $resultResponse = $pipeline->handle($request);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testPipeString(): void
    {
        $pipeline = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middlewareResolver = new MiddlewareResolver();

        $middleware = $this->createMiddleware();
        $handler = $this->createRequestHandler($response);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(4))
            ->method('get')
            ->willReturnCallback(function ($class) use ($middleware, $handler) {
                return match ($class) {
                    $middleware::class => $middleware,
                    $handler::class => $handler,
                    default => throw new InvalidArgumentException('Invalid argument')
                };
            });

        $middlewareResolver->container($container);
        $pipeline->middlewareResolver($middlewareResolver);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();

        $pipeline->pipe($middleware1);
        $pipeline->pipe($middleware::class);
        $pipeline->pipe($middleware2);
        $pipeline->pipe($middleware::class, $middleware::class);
        $pipeline->pipe($handler::class);

        $resultResponse = $pipeline->handle($request);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testProcessHandlerByRequestHandler(): void
    {
        $pipeline = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();

        $handler = $this->createRequestHandler($response);

        $pipeline->pipe($middleware1);
        $pipeline->pipe($middleware2);

        $resultResponse = $pipeline->process($request, $handler);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testProcessHandlerByPipeline(): void
    {
        $pipeline = new Pipeline();
        $pipeline2 = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();

        $handler = $this->createRequestHandler($response);

        $pipeline2->pipe($middleware1);
        $pipeline2->pipe($middleware2);
        $pipeline2->pipe($handler);

        $middleware3 = $this->createMiddleware();
        $middleware4 = $this->createMiddleware();

        $pipeline->pipe($middleware3);
        $pipeline->pipe($middleware4);

        $resultResponse = $pipeline->process($request, $pipeline2);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testProcessHandlerByStringPipeline(): void
    {
        $pipeline = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createRequestHandler($response);

        $pipeline2 = $this->createMock(PipelineInterface::class);
        $pipeline2->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (ServerRequestInterface $request) use ($handler): ResponseInterface {
                return $handler->handle($request);
            });

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->willReturnCallback(function ($class) use ($pipeline2) {
                return match ($class) {
                    $pipeline2::class => $pipeline2,
                    default => throw new InvalidArgumentException('Invalid argument')
                };
            });

        $requestHandlerResolver = new RequestHandlerResolver();
        $requestHandlerResolver->container($container);
        $pipeline->requestHandlerResolver($requestHandlerResolver);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();

        $pipeline->pipe($middleware1);
        $pipeline->pipe($middleware2);

        $resultResponse = $pipeline->process($request, $pipeline2::class);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testProcessHandlerByStringRequestHandler(): void
    {
        $pipeline = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createRequestHandler($response);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->willReturnCallback(function ($class) use ($handler) {
                return match ($class) {
                    $handler::class => $handler,
                    default => throw new InvalidArgumentException('Invalid argument')
                };
            });

        $requestHandlerResolver = new RequestHandlerResolver();
        $requestHandlerResolver->container($container);
        $pipeline->requestHandlerResolver($requestHandlerResolver);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();

        $pipeline->pipe($middleware1);
        $pipeline->pipe($middleware2);

        $resultResponse = $pipeline->process($request, $handler::class);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testProcessHandlerByCallable(): void
    {
        $pipeline = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();

        $pipeline->pipe($middleware1);
        $pipeline->pipe($middleware2);

        $resultResponse = $pipeline->process($request, function (ServerRequestInterface $request) use ($response): ResponseInterface {
            $request->getBody();
            return $response;
        });
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testPipelineTree(): void
    {
        $pipeline = new Pipeline();
        $pipeline2 = new Pipeline();
        $pipeline3 = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middleware0 = $this->createMiddleware();
        $pipeline3->pipe($middleware0);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();
        $pipeline2->pipe($middleware1);
        $pipeline2->pipe($pipeline3);
        $pipeline2->pipe($middleware2);

        $middleware3 = $this->createMiddleware();
        $middleware4 = $this->createMiddleware();
        $pipeline->pipe($middleware3);
        $pipeline->pipe($pipeline2);
        $pipeline->pipe($middleware4);

        $handler = $this->createRequestHandler($response);
        $pipeline->pipe($handler);

        $resultResponse = $pipeline->handle($request);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testPipelineTree2(): void
    {
        $pipeline = new Pipeline();
        $pipeline2 = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createRequestHandler($response);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();
        $pipeline2->pipe($middleware1);
        $pipeline2->pipe($middleware2);
        $pipeline2->pipe($handler);

        $middleware3 = $this->createMiddleware();
        $middleware4 = $this->createMiddleware();
        $pipeline->pipe($middleware3);
        $pipeline->pipe($middleware4);
        $pipeline->pipe($pipeline2);

        $resultResponse = $pipeline->handle($request);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testTwoRequestHandler(): void
    {
        $pipeline = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response2 = $this->createMock(ResponseInterface::class);

        $handler = $this->createRequestHandler($response);
        $handler2 = $this->createRequestHandler($response2);

        $middleware1 = $this->createMiddleware();
        $middleware2 = $this->createMiddleware();

        $pipeline->pipe($middleware1);
        $pipeline->pipe($handler);
        $pipeline->pipe($middleware2);
        $pipeline->pipe($handler2);

        $resultResponse = $pipeline->handle($request);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @throws Exception
     */
    public function testRewind(): void
    {
        $pipeline = new Pipeline();

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createRequestHandler($response);

        $pipeline->pipe($handler);

        $resultResponse = $pipeline->handle($request);
        $this->assertSame($response, $resultResponse);

        $this->expectException(PipelineEmptyStoreException::class);
        $pipeline->handle($request);

        $pipeline->rewind();
        $resultResponse = $pipeline->handle($request);
        $this->assertSame($response, $resultResponse);
    }

    /**
     * @return MiddlewareInterface
     */
    protected function createMiddleware(): MiddlewareInterface
    {
        return new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        };
    }

    /**
     * @param ResponseInterface $response
     * @return RequestHandlerInterface
     */
    protected function createRequestHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class ($response) implements RequestHandlerInterface {
            public function __construct(
                protected ResponseInterface $response
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
