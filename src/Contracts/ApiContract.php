<?php
namespace Zymawy\Dgraph\Contracts;

interface ApiContract
{
    public function send(): \Zymawy\Dgraph\Facades\Dgraph|\Zymawy\Dgraph\Responses\DgraphResponse;
}