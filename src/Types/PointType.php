<?php

namespace Zymawy\Dgraph\Types;

class PointType extends TypeBase
{
    protected string $name;
    public function __construct(protected array $directives = [])
    {
        $this->name = EnumType::Point->value;
    }
}