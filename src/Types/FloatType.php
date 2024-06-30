<?php

namespace Zymawy\Dgraph\Types;

class FloatType extends TypeBase
{
    protected string $name;
    public function __construct(protected array $directives = [])
    {
        $this->name = EnumType::Float->value;
    }
}