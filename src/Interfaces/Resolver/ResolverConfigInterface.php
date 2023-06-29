<?php

declare(strict_types=1);

namespace Hotaruma\Pipeline\Interfaces\Resolver;

use Psr\Container\ContainerInterface;

interface ResolverConfigInterface
{
    /**
     * Set container.
     *
     * @param ContainerInterface $container
     * @return object
     */
    public function container(ContainerInterface $container): object;
}
