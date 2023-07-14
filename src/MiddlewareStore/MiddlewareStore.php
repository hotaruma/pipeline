<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\MiddlewareStore;

use Hotaruma\Pipeline\Exception\MiddlewareStoreOutOfRangeException;
use Hotaruma\Pipeline\Interfaces\MiddlewareStore\MiddlewareStoreInterface;
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * @template TValue of MiddlewareInterface|RequestHandlerInterface
 *
 * @implements MiddlewareStoreInterface<int, TValue>
 */
final class MiddlewareStore implements MiddlewareStoreInterface
{
    /**
     * @var int
     */
    protected int $position = 0;

    /**
     * @var array<int, TValue>
     */
    protected array $store = [];

    /**
     * @inheritDoc
     */
    public function append(MiddlewareInterface|RequestHandlerInterface $middleware): void
    {
        $this->store[] = $middleware;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @inheritDoc
     */
    public function current(): MiddlewareInterface|RequestHandlerInterface
    {
        return $this->store[$this->position] ??
            throw new MiddlewareStoreOutOfRangeException('The current middleware is out of range.');
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->store[$this->position]);
    }
}
