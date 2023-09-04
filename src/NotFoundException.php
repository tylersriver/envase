<?php

namespace Envase;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public function __construct(public readonly string $key)
    {
        parent::__construct("Key '$key' not found");
    }
}