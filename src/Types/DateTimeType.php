<?php

namespace Zymawy\Dgraph\Types;

class DateTimeType extends TypeBase
{
    protected string $name;

    public function __construct(protected array $directives = [])
    {
        $this->name = EnumType::DateTime->value;
    }
}
