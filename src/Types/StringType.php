<?php

namespace Zymawy\Dgraph\Types;

class StringType extends TypeBase
{
    protected string $name;

    public function __construct(protected array $directives = [])
    {
        $this->name = EnumType::String->value;
    }
}
