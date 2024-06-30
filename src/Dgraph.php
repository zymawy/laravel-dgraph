<?php

namespace Zymawy\Dgraph;


use Zymawy\Dgraph\Api\Mutation;
use Zymawy\Dgraph\Api\Operation;
use Zymawy\Dgraph\Api\Query;
use Zymawy\Dgraph\Contracts\ApiContract;

final class Dgraph
{
    use \Illuminate\Support\Traits\ForwardsCalls;
    protected ?ApiContract $delegateTo = null;

    public function __construct(protected DgraphClient $client)
    {}


    /**
     * @return $this|Operation|ApiContract
     */
    public function operation(): self
    {
        $this->delegateTo = new Operation();
        return $this;
    }


//    public function query(Query $query): DgraphResponse
//    {
//        return $this->client->query($query->build());
//    }

    public function executeQuery(): self
    {
        $this->delegateTo = new Query();
        return $this;
    }

//    public function mutate(Mutation $mutation): DgraphResponse
//    {
//        return $this->client->mutate($mutation);
//    }

    public function executeMutation(): self
    {
        $this->delegateTo = new Mutation();
        return $this;
    }

    /**
     * Forward calls to the appropriate object.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->client, $method)) {

            return $this->forwardCallTo($this->client, $method, [...$this->delegateTo ? [$this->delegateTo] : [], ...$parameters]);
        }

        if ($this->delegateTo) {
            return $this->forwardCallTo($this->delegateTo, $method, $parameters);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");

    }
}
