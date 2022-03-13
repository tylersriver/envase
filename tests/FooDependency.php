<?php

namespace Envase\Test;

class FooDependency
{
    public function __construct(
        public Foo $foo, 
        public string $fooSet
    ){
    }
}