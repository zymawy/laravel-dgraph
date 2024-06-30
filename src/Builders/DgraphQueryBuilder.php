<?php

namespace Zymawy\Dgraph\Builders;

class DgraphQueryBuilder
{
    protected $query;
    protected $fields;
    protected $filter;

    public function __construct()
    {
        $this->query = '';
        $this->fields = '';
        $this->filter = '';
    }

    public function select($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function filter($conditions)
    {
        $this->filter = "@filter($conditions)";
        return $this;
    }

    public function build()
    {
        $this->query = "query { all(func: type(NodeType)) { $this->fields $this->filter } }";
        return $this->query;
    }
}