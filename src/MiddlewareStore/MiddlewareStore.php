<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\MiddlewareStore;

use Hotaruma\Pipeline\Interfaces\MiddlewareStore\MiddlewareStoreInterface;
use Psr\Http\Server\MiddlewareInterface;
use SplQueue;

/**
 * @template TValue of MiddlewareInterface
 *
 * @extends SplQueue<TValue>
 * @implements MiddlewareStoreInterface<int, TValue>
 */
final class MiddlewareStore extends SplQueue implements MiddlewareStoreInterface
{
    /**
     * @inheritDoc
     */
    public function append(MiddlewareInterface $middleware): void
    {
        parent::enqueue($middleware);
    }

    /**
     * @inheritDoc
     */
    public function receive(): MiddlewareInterface
    {
        return parent::dequeue();
    }

    /**
     * @inheritDoc
     */
    public function hasNext(): bool
    {
        return $this->isEmpty();
    }
}
