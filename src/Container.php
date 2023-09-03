<?php

namespace Envase;

use Closure;
use Exception;
use ReflectionMethod;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private array $registry = [];

    /**
     * Container constructor.
     *
     * @param array|string $definitions
     */
    public function __construct(array|string $definitions = [])
    {
        $this->add($definitions);
    }

    /**
     * @param array|string $definitions
     */
    public function add(array|string $definitions): self
    {
        if (is_string($definitions) && file_exists($definitions)) {
            $definitions = require $definitions;
        }

        $this->registry = array_merge($this->registry, $definitions);
        return $this;
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

        $obj = $this->injectProperties($obj);
        
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
            $dependencyStr = $this->getDependencyNameFromType($parameter);
            $dependences[] = $this->get($dependencyStr);
        }

        return new $key(...$dependences);
    }

    private function injectProperties(object $obj): object
    {
        $reflected = new ReflectionClass($obj);
        foreach ($reflected->getProperties() as $prop) {
            // Check if propert has Inject attr
            $parameterAttr = $prop->getAttributes(Inject::class);
            if (count($parameterAttr) === 0) {
                continue;
            }

            // Create the Inject attr and get the key to resolve
            $parameterAttr = $parameterAttr[0]->newInstance();
            $key = $parameterAttr->getKey();

            // Resolve prop, when null try by name
            $propValue = $this->get($key ?? $this->getDependencyNameFromType($prop));

            // Set the property on the object
            $prop->setValue($obj, $propValue);
        }

        return $obj;
    }

    private function getDependencyNameFromType(ReflectionParameter|ReflectionProperty $parameter): string
    {
        $dependencyType = (string) $parameter->getType();
        return
            class_exists($dependencyType) || interface_exists($dependencyType)
                ? $dependencyType
                : $parameter->getName();
    }
}
