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
    protected int $position;

    /**
     * @var array<TValue>
     */
    protected array $store;

    /**
     * @inheritDoc
     */
    public function append(MiddlewareInterface|RequestHandlerInterface $middleware): void
    {
        $store = $this->getStore();
        $store[] = $middleware;

        $this->store($store);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position(0);
    }

    /**
     * @inheritDoc
     */
    public function current(): MiddlewareInterface|RequestHandlerInterface
    {
        return $this->getStore()[$this->getPosition()] ??
            throw new MiddlewareStoreOutOfRangeException('The current middleware is out of range.');
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->getPosition();
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $position = $this->getPosition();
        $this->position(++$position);
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->getStore()[$this->getPosition()]);
    }

    /**
     * @param int $position
     * @return void
     */
    protected function position(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    protected function getPosition(): int
    {
        return $this->position ??= 0;
    }

    /**
     * @param array<TValue> $store
     * @return void
     */
    protected function store(array $store): void
    {
        $this->store = $store;
    }

    /**
     * @return array<TValue>
     */
    protected function getStore(): array
    {
        return $this->store ??= [];
    }
}
