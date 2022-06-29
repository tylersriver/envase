<?php

namespace Envase\Test;

use Envase\Inject;

class FooWithAttr
{
    #[Inject]
    private string $foo;

    public function getFoo(): string
    {
        return $this->foo;
    }
}