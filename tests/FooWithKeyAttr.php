<?php

namespace Envase\Test;

use Envase\Inject;

class FooWithKeyAttr
{
    #[Inject("fooish")]
    private string $foo;

    public function getFoo(): string
    {
        return $this->foo;
    }
}