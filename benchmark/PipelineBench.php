<?php

declare(strict_types=1);

namespace Hotaruma\Benchmark;

use PhpBench\Attributes\{Assert, BeforeMethods, Iterations, OutputTimeUnit, Revs, Timeout, Warmup};
use Hotaruma\Pipeline\Interfaces\Pipeline\PipelineInterface;
use Hotaruma\Pipeline\Pipeline;
use Mockery;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

#[BeforeMethods(['setUp'])]
#[Warmup(2)]
#[Iterations(5)]
#[Revs(500)]
#[Timeout(10.0)]
#[OutputTimeUnit('microseconds')]
class PipelineBench
{
    protected PipelineInterface $pipeline;
    protected PipelineInterface $pipeline2;
    protected PipelineInterface $pipeline3;

    protected MiddlewareInterface $middleware;
    protected RequestHandlerInterface $requestHandler;

    protected ServerRequestInterface $request;
    protected ResponseInterface $response;

    public function setUp(): void
    {
        $this->pipeline = new Pipeline();

        $this->pipeline2 = new Pipeline();
        $this->pipeline3 = new Pipeline();

        /** @phpstan-ignore-next-line */
        $this->request = Mockery::mock(ServerRequestInterface::class);
        /** @phpstan-ignore-next-line */
        $this->response = Mockery::mock(ResponseInterface::class);

        $this->middleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        };
        /** @phpstan-ignore-next-line */
        $this->requestHandler = new class ($this->response) implements RequestHandlerInterface {
            public function __construct(
                protected ResponseInterface $response
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        $this->pipeline3->pipe($this->middleware, $this->middleware, $this->middleware);
        for ($i = 0; $i <= 500; $i++) {
            $this->pipeline2->pipe($this->middleware, $this->pipeline3);
        }
    }

    #[Assert("mode(variant.time.avg) < 4 microseconds +/- 10%")]
    #[Assert("mode(variant.mem.peak) < 3mb")]
    public function benchPipeMiddleware(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $this->pipeline->pipe($this->middleware);
        }
    }

    #[Assert("mode(variant.time.avg) < 4 microseconds +/- 10%")]
    #[Assert("mode(variant.mem.peak) < 3mb")]
    public function benchPipeRequestHandler(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $this->pipeline->pipe($this->requestHandler);
        }
    }

    #[Assert("mode(variant.time.avg) < 4 microseconds +/- 10%")]
    #[Assert("mode(variant.mem.peak) < 3mb")]
    public function benchPipePipeline(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $this->pipeline->pipe($this->pipeline2);
        }
    }

    #[Assert("mode(variant.time.avg) < 2 microseconds +/- 10%")]
    #[Assert("mode(variant.mem.peak) < 4mb")]
    public function benchProcess(): void
    {
        $this->pipeline2->process($this->request, $this->requestHandler);
    }
}
