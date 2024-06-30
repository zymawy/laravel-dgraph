<?php

namespace Zymawy\Dgraph\Api;

use Zymawy\Dgraph\Facades\Dgraph;
use Zymawy\Dgraph\Responses\DgraphResponse;

class Mutation extends ApiBase
{
    protected array $set = [];

    protected array $delete = [];

    protected array $extraParams = [];

    public function set(array $data): self
    {
        $this->set = $data;

        return $this;
    }

    public function delete(array $data): self
    {
        $this->delete = $data;

        return $this;
    }

    public function setDatum(string $key, $value): self
    {
        $this->extraParams[$key] = $value;

        return $this;
    }

    public function build(): array
    {
        $mutation = [];
        if (! empty($this->set)) {
            $mutation['set'] = $this->set;
        }
        if (! empty($this->delete)) {
            $mutation['delete'] = $this->delete;
        }

        return array_merge($mutation, $this->extraParams);
    }

    public function send(): Dgraph|DgraphResponse
    {
        return Dgraph::mutate($this);
    }
}
