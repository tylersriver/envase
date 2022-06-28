<?php

namespace Envase;

use Closure;
use Exception;
use ReflectionMethod;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
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
     * Find an entry
     *
     * @param  string $key
     * @return mixed
     * @throws Exception
     */
    public function get(string $key)
    {
        // Check static registry first
        if ($this->has($key)) {
            $item = $this->registry[$key];

            // If static item return
            if (!$item instanceof \Closure) {
                return $item;
            }

            // IF closure we need to call and set
            // the returned item to the registry for next time
            $resolved = $item($this);
            $this->registry[$key] = $resolved;
            return $resolved;
        }

        // If class exists, Autowire
        if (class_exists($key)) {
            $this->registry[$key] = $this->make($key);
            return $this->registry[$key];
        }

        throw new NotFoundException('Key not found');
    }

    /**
     * Create an object from the given FQCN String
     *
     * @param string $key
     * @return object
     */
    public function make(string $key): object
    {
        $obj = $this->instantiate($key);

        // TODO : Handle property attributes
        
        return $obj;
    }

    /**
     * @param string $key - the FQCN of the class to instantiate
     * @return object - the instantiated class
     */
    private function instantiate(string $key): object
    {
        // With no constructor defined just return new
        if (!method_exists($key, '__construct')) {
            return new $key();
        }
        
        // Let's inject the cosntructor parameters
        $reflection = new ReflectionMethod($key, '__construct');
        $parameters = $reflection->getParameters();

        $dependences = [];
        foreach ($parameters as $parameter) {
            $dependencyType = (string) $parameter->getType();

            $dependencyStr =
                class_exists($dependencyType)
                    ? $dependencyType
                    : $parameter->getName();

            $dependences[] = $this->get($dependencyStr);
        }

        return new $key(...$dependences);
    }
}
