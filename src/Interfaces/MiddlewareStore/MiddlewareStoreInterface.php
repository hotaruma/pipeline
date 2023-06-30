<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Interfaces\MiddlewareStore;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @template TValue of MiddlewareInterface|RequestHandlerInterface
 */
interface MiddlewareStoreInterface
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
     * Return next middleware.
     *
     * @return MiddlewareInterface|RequestHandlerInterface
     *
     * @phpstan-return TValue
     */
    public function receive(): MiddlewareInterface|RequestHandlerInterface;

    /**
     * Checks if there is a next middleware in the store.
     *
     * @return bool
     */
    public function hasNext(): bool;

    /**
     * Rewind middleware position to start.
     *
     * @return void
     */
    public function rewind(): void;
}
