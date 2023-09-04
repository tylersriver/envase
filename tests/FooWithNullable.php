<?php

namespace Envase\Test;

class FooWithNullable
{
    public function __construct(
        public ?BarInterface $bar,
        public ?string $fooSet
    ){
        
    }
}