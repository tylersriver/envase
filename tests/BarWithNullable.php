<?php

namespace Envase\Test;

class BarWithNullable
{
    public function __construct(
        public ?BarInterface $bar,
        public FooInterface $foo
    ) {
        
    }
}