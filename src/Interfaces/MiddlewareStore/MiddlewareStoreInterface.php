<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Interfaces\MiddlewareStore;

use Hotaruma\Pipeline\Exception\MiddlewareStoreOutOfRangeException;
use Iterator;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @template TKey
 * @template TValue of MiddlewareInterface|RequestHandlerInterface
 *
 * @extends Iterator<TKey, TValue>
 */
interface MiddlewareStoreInterface extends Iterator
{
    /**
     * Add middleware to store.
     *
     * @param MiddlewareInterface|RequestHandlerInterface $middleware
     * @return void
     *
     * @phpstan-param TValue $middleware
     */
    public function append(MiddlewareInterface|RequestHandlerInterface $middleware): void;

    /**
     * Return current middleware.
     *
     * @return MiddlewareInterface|RequestHandlerInterface
     *
     * @throws MiddlewareStoreOutOfRangeException
     *
     * @phpstan-return TValue
     */
    public function current(): MiddlewareInterface|RequestHandlerInterface;

    /**
     * @inheritDoc
     */
    public function key(): mixed;

    /**
     * @inheritDoc
     */
    public function next(): void;

    /**
     * @inheritDoc
     */
    public function rewind(): void;

    /**
     * @inheritDoc
     */
    public function valid(): bool;
}
