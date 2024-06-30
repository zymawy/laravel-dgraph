<?php

namespace Zymawy\Dgraph\Types;

class UIDType extends TypeBase
{
    protected string $name;
    public function __construct(protected array $directives = [])
    {
        $this->name = EnumType::UID->value;
    }
}