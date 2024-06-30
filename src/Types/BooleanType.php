<?php

namespace Zymawy\Dgraph\Types;

class BooleanType extends TypeBase
{
    protected string $name;

    public function __construct(protected array $directives = [])
    {
        $this->name = EnumType::Boolean->value;
    }
}