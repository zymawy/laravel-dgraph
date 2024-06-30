<?php

namespace Zymawy\Dgraph\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Zymawy\Dgraph\Traits\RequestEventTrait;

class RequestInProgress
{
    use RequestEventTrait;
}