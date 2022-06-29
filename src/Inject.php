<?php

namespace Envase;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Inject
{
    public function __construct(
        private ?string $key = null
    ) {
    }

    public function getKey(): ?string
    {
        return $this->key;
    }
}