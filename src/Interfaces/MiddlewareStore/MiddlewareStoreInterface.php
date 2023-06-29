<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Interfaces\MiddlewareStore;

use Iterator;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @template TKey
 * @template TValue of MiddlewareInterface
 *
 * @extends Iterator<TKey, TValue>
 */
interface MiddlewareStoreInterface extends Iterator
{
    /**
     * Add middleware to store.
     *
     * @param MiddlewareInterface $middleware
     * @return void
     *
     * @phpstan-param TValue $middleware
     */
    public function append(MiddlewareInterface $middleware): void;

    /**
     * Return next middleware.
     *
     * @return MiddlewareInterface
     * @phpstan-return TValue
     */
    public function receive(): MiddlewareInterface;

    /**
     * Checks if there is a next middleware in the store.
     *
     * @return bool
     */
    public function hasNext(): bool;
}
