# PSR-15 Pipeline

[![Build and Test](https://github.com/hotaruma/pipeline/actions/workflows/ci.yml/badge.svg)](https://github.com/hotaruma/pipeline/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/github/release/hotaruma/pipeline.svg)](https://github.com/hotaruma/pipeline/releases)
[![License](https://img.shields.io/github/license/hotaruma/pipeline.svg)](https://github.com/hotaruma/pipeline/blob/master/LICENSE)
![PHP from Packagist](https://img.shields.io/packagist/php-v/hotaruma/pipeline)
[![Packagist Downloads](https://img.shields.io/packagist/dt/hotaruma/pipeline.svg)](https://packagist.org/packages/hotaruma/pipeline)
[![codecov](https://codecov.io/gh/hotaruma/pipeline/branch/main/graph/badge.svg)](https://codecov.io/gh/hotaruma/pipeline)

Pipeline library for handling HTTP requests and responses with middleware.

## Features

- Integration with PSR-7 HTTP messages and PSR-11 container.
- Support for middleware/pipeline chaining to process HTTP requests in a flexible way.

## Installation

You can install the library using Composer. Run the following command:

```bash
composer require hotaruma/pipeline
```

## Usage

Simple example of how you can use `Pipeline` to process an HTTP request:

```php
use Hotaruma\Pipeline\Pipeline;

$pipeline = new Pipeline();

$pipeline->pipe(
    ErrorMiddleware::class,
    LogMiddleware::class,
    RouteMiddleware::class,
);

$response = $pipeline->process($serverRequest, RouteNotFoundMiddleware::class);
```

By nesting pipelines within one another, it becomes possible to compose more complex and reusable middleware structures,
providing flexibility and modularity in application development.

```php
$authPipeline = new Pipeline();

$authPipeline->pipe(
    AccessLogMiddleware::class,
    AuthMiddleware::class,
);

$pipeline->pipe(
    ErrorMiddleware::class,
    LogMiddleware::class,
    $authPipeline,
);

$response = $pipeline->process($serverRequest, RequestHandler::class);
```

Using a pipeline as a request handler.

```php
$requestHandlerPipeline = new Pipeline();

$requestHandlerPipeline->pipe(
    LogMiddleware::class,
    RouteNotFoundMiddleware::class
);
$response = $pipeline->process($serverRequest, $requestHandlerPipeline);
```

Pipeline can be rewound.

```php
$pipeline->rewind();
```

We can pass request handler as a callable.

```php
use Zend\Diactoros\Response;
use GuzzleHttp\Psr7\Utils;

$response = $pipeline->process($serverRequest, function (ServerRequestInterface $request): ResponseInterface {
    return (new Response())
        ->withStatus(404)
        ->withHeader('Content-Type', 'text/plain')
        ->withBody(Utils::streamFor('Not Found'));
});
```

Resolvers are responsible for resolving middleware and request handler classes, allowing for dynamic retrieval and
instantiation of these components within the pipeline. By default, pipelines usually use a specific resolver
implementation.

```php
use Hotaruma\Pipeline\Resolver\{MiddlewareResolver, RequestHandlerResolver};

$pipeline->middlewareResolver(new MiddlewareResolver());
$pipeline->handlerResolver(new RequestHandlerResolver());

$pipeline->getMiddlewareResolver()->container($container);
$pipeline->getRequestHandlerResolver()->container($container);
```

The middleware store is responsible for managing and storing the middleware that is added to the pipeline. It provides
the necessary functionality to add, retrieve, and execute the middleware in the desired order.

```php
use Hotaruma\Pipeline\MiddlewareStore\MiddlewareStore;

$pipeline->middlewareStore(new MiddlewareStore());
```

## Contributing

Contributions are welcome! If you find a bug or have an idea for a new feature, please open an issue or submit a pull
request.
