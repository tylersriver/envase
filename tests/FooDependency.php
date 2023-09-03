<?php

namespace Envase\Test;

class FooDependency
{
    public function __construct(
        public Foo|string $foo, 
        public string $fooSet
    ){
    }
}