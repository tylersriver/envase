<?php

namespace Envase;

use Exception;

class Container
{
    /**
     * @var array
     */
    private array $registry;

    /**
     * Container constructor.
     *
     * @param array $definitions
     */
    public function __construct(array $definitions = [])
    {
        $this->registry = $definitions;
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->registry[$key]);
    }

    /**
     * Set a value in the registry
     *
     * @param string $key
     * @param mixed  $val
     */
    public function set(string $key, $val): void
    {
        $this->registry[$key] = $val;
    }

    /**
     * @param  string $key
     * @return mixed
     * @throws Exception
     */
    public function get(string $key)
    {
        // Check static registry first
        if ($this->has($key)) {
            return $this->registry[$key];
        }

        // If class exists, Autowire
        if (class_exists($key)) {
            $this->registry[$key] = $this->resolve($key);
            return $this->registry[$key];
        }

        throw new Exception('Key not found');
    }

    /**
     * @param string $key
     * @return object
     */
    protected function resolve(string $key)
    {
        if (method_exists($key, '__construct')) {
            $reflection = new \ReflectionMethod($key, '__construct');
            $parameters = $reflection->getParameters();
    
            $dependences = [];
            foreach ($parameters as $parameter) {
                $dependenceClass = (string) $parameter->getType();
                $dependences[] = $this->get($dependenceClass);
            }

            return new $key(...$dependences);
        }

        return new $key();
    }
}
