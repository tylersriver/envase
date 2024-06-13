<?php

namespace Envase;

use Exception;
use ReflectionMethod;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
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
    public function get(string $key): mixed
    {
        // Check static registry first
        if ($this->has($key)) {
            $item = $this->registry[$key];

            // If static item return
            if (!is_callable($item)) {
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

        throw new NotFoundException($key);
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

        $dependencies = [];
        foreach ($parameters as $parameter) {
            $dependencies[] = $this->resolveDependency($parameter);
        }

        return new $key(...$dependencies);
    }

    /**
     * Given a class property attempt to resolve it from the container.
     * When it fails to resolve but the parameters type allows null
     * we fallback to null.
     *
     * @throws NotFoundException
     */
    private function resolveDependency(ReflectionParameter $parameter): mixed
    {
        $dependencyStr = $this->getDependencyNameFromType($parameter);

        try {
            return $this->get($dependencyStr);
        } catch (NotFoundException $e) {
            if ($parameter->getType()?->allowsNull()) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * for the given object check if the Inject attribute is
     * defined on each class property, attempt to set
     * it from the container if possible
     */
    private function injectProperties(object $obj): object
    {
        $reflected = new ReflectionClass($obj);
        foreach ($reflected->getProperties() as $prop) {
            // Check if property has Inject attr
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

    /**
     * Given a class property or constructor parameter resolve what string we want
     * to resolve from the conatiner. For non built in types attempt to locate a class/interface
     * to resolve. IF built in we return the name of the property/parameter
     */
    private function getDependencyNameFromType(ReflectionParameter|ReflectionProperty $parameter): string
    {
        $dependencyType = $parameter->getType();

        // Named types meaning only a single type and
        // aren't a built in type can be resolved as the class/interface
        // they are
        if (
            $dependencyType instanceof ReflectionNamedType
            && !$dependencyType->isBuiltin()
        ) {
            return
                $dependencyType->allowsNull()
                    ? ltrim((string)$dependencyType, '?')
                    : (string)$dependencyType;
        }

        // otherwise we resolve by the properties name after the $
        return $parameter->getName();
    }
}
