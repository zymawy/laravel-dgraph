<?php

namespace Zymawy\Dgraph\Api;

use Zymawy\Dgraph\Contracts\ApiContract;
use Zymawy\Dgraph\Facades\Dgraph;
use Zymawy\Dgraph\Responses\DgraphResponse;

class Query extends ApiBase
{
    protected string $root = 'all'; // Default root value
    protected string $fields = '';
    protected array $conditions = [];

    public function setRoot(string $root): self
    {
        $this->root = $root;
        return $this;
    }

    public function select(string $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function where(string $field, string $operator, $value): self
    {
        if ($operator === 'like') {
            $this->conditions[] = "anyofterms($field, \"$value\")";
        } else {
            $operator = $this->convertOperator($operator);
            $this->conditions[] = "$operator($field, $value)";
        }
        return $this;
    }

    protected function convertOperator(string $operator): string
    {
        return match ($operator) {
            '>' => 'gt',
            '>=' => 'ge',
            '<' => 'lt',
            '<=' => 'le',
            '=' => 'eq',
            '!=' => 'ne',
            default => throw new \InvalidArgumentException("Invalid operator $operator"),
        };
    }

    public function build(): string
    {
        $filter = implode(' AND ', $this->conditions);
        $fields = $this->fields ? $this->fields : 'uid';
        $query = "query { {$this->root}(func: {$filter}) { {$fields} } }";
//        dd($query);
//        $query = "query { {$this->root}(func: type(Person)) @filter({$filter}) { {$this->fields} } }";
        return $query;
    }

    public function send(): Dgraph|DgraphResponse
    {
        return Dgraph::query($this);
    }
}