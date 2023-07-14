<?php

declare(strict_types=1);

namespace Hotaruma\Tests\Unit;

use Hotaruma\Pipeline\MiddlewareStore\MiddlewareStore;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareStoreTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testAppendReceive(): void
    {
        $store = new MiddlewareStore();
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);

        $store->append($middleware1);
        $store->append($middleware2);

        $this->assertSame($middleware1, $store->current());
        $store->next();
        $this->assertSame($middleware2, $store->current());
    }

    /**
     * @throws Exception
     */
    public function testHasNext(): void
    {
        $store = new MiddlewareStore();
        $this->assertFalse($store->valid());

        $middleware = $this->createMock(MiddlewareInterface::class);

        $store->append($middleware);
        $this->assertTrue($store->valid());

        $store->current();
        $store->next();
        $this->assertFalse($store->valid());
    }
}
