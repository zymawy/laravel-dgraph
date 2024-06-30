<?php

namespace Zymawy\Dgraph\Types;

class IntType extends TypeBase
{
    protected string $name;

    public function __construct(protected array $directives = [])
    {
        $this->name = EnumType::Int->value;
    }
}
