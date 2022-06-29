<?php

namespace Envase\Test;

use Envase\Inject;

class FooWithObjAttr
{
    #[Inject]
    private FooDependency $foo;

    public function getFoo(): FooDependency
    {
        return $this->foo;
    }
}