<?php

namespace Zymawy\Dgraph\Builders;

use Zymawy\Dgraph\Contracts\TypeContract;

class SchemaBuilder
{
    protected array $fields = [];

    protected array $types = [];

    public function addField(string $name, TypeContract $type): void
    {
        $field = "$name: $type .";
        $this->fields[] = $field;
    }

    public function addType(string $typeName, array $fields): void
    {
        $typeFields = implode("\n  ", $fields);
        $this->types[] = "type $typeName {\n  $typeFields\n}";
    }

    public function build(): string
    {
        $schema = implode("\n", $this->fields);
        $types = implode("\n\n", $this->types);

        return "$schema\n\n$types";
    }
}
