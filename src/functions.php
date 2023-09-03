<?php

namespace Envase;

use Closure;
use Psr\Container\ContainerInterface;

if (!function_exists('Envase\get')) {
    function get(string $entry): Closure
    {
        return fn(ContainerInterface $container) => $container->get($entry);
    }
}