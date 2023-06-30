<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\MiddlewareStore;

use Hotaruma\Pipeline\Interfaces\MiddlewareStore\MiddlewareStoreInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

/**
 * @template TValue of MiddlewareInterface|RequestHandlerInterface
 *
 * @extends SplQueue<TValue>
 * @implements MiddlewareStoreInterface<TValue>
 */
final class MiddlewareStore extends SplQueue implements MiddlewareStoreInterface
{
    /**
     * @inheritDoc
     */
    public function append(MiddlewareInterface|RequestHandlerInterface $middleware): void
    {
        parent::enqueue($middleware);
    }

    /**
     * @inheritDoc
     */
    public function receive(): MiddlewareInterface|RequestHandlerInterface
    {
        return parent::dequeue();
    }

    /**
     * @inheritDoc
     */
    public function hasNext(): bool
    {
        return !$this->isEmpty();
    }
}
