<?php

namespace Zymawy\Dgraph\Api;

use Zymawy\Dgraph\Builders\SchemaBuilder;
use Zymawy\Dgraph\Facades\Dgraph;
use Zymawy\Dgraph\Responses\DgraphResponse;

class Operation extends ApiBase
{
    protected SchemaBuilder $schemaBuilder;

    public function __construct()
    {
        $this->schemaBuilder = new SchemaBuilder();
    }

    public function addField(string $name, \Zymawy\Dgraph\Contracts\TypeContract $type): self
    {
        $this->schemaBuilder->addField($name, $type);
        return $this;
    }

    public function addType(string $typeName, array $fields): self
    {
        $this->schemaBuilder->addType($typeName, $fields);
        return $this;
    }

    public function setSchema(string $schema): self
    {
        $this->schemaBuilder->setSchema($schema);
        return $this;
    }

    public function build(): string
    {
        return $this->schemaBuilder->build();
    }

    public function send(): Dgraph|DgraphResponse
    {
        return Dgraph::alter($this);
    }
}