<?php

namespace Hotaruma\Pipeline\Resolver;

use Hotaruma\Pipeline\Exception\NotFoundContainerException;
use Hotaruma\Pipeline\Interfaces\Resolver\ResolverConfigInterface;
use Psr\Container\ContainerInterface;

abstract class Resolver implements ResolverConfigInterface
{
    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * @return ContainerInterface
     *
     * @throws NotFoundContainerException
     */
    protected function getContainer(): ContainerInterface
    {
        if (!isset($this->container)) {
            throw new NotFoundContainerException();
        }
        return $this->container;
    }
}
