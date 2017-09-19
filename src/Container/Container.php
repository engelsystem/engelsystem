<?php

namespace Engelsystem\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    /**
     * The globally available container
     *
     * @var static
     */
    protected static $instance;

    /**
     * Contains the shared instances
     *
     * @var mixed[]
     */
    protected $instances = [];

    /**
     * Finds an entry of the container by its identifier and returns it
     *
     * @param string $id Identifier of the entry to look for
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier
     * @throws ContainerExceptionInterface Error while retrieving the entry
     *
     * @return mixed Entry
     */
    public function get($id)
    {
        if ($this->has($id)) {
            return $this->resolve($id);
        }

        throw new NotFoundException(sprintf('The entry with the id "%s" could not be found'));
    }

    /**
     * Register a shared entry in the container
     *
     * @param string $abstract Identifier of the entry to set
     * @param mixed  $instance Entry
     */
    public function instance($abstract, $instance)
    {
        $this->singleton($abstract, $instance);
    }

    /**
     * Register a shared entry as singleton in the container
     *
     * @param string $abstract
     * @param mixed  $instance
     */
    public function singleton($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Returns true if the container can return an entry for the given identifier
     * Returns false otherwise
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`
     *
     * @param string $id Identifier of the entry to look for
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this->instances[$id]);
    }

    /**
     * Resolve the requested object
     *
     * @param string $abstract
     * @return mixed
     */
    protected function resolve($abstract)
    {
        return $this->instances[$abstract];
    }

    /**
     * Get the globally available instance of the container
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the globally available instance of the container
     *
     * @param Container $container
     */
    public static function setInstance(Container $container)
    {
        static::$instance = $container;
    }
}
