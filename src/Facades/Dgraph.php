<?php

namespace Zymawy\Dgraph\Facades;

use Illuminate\Support\Facades\Facade;
use Zymawy\Dgraph\Contracts\ApiContract;

/**
 * @see \Zymawy\Dgraph\Dgraph
 * @mixin \Zymawy\Dgraph\Dgraph
 * @mixin ApiContract
 */
class Dgraph extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'dgraph';
    }
}
