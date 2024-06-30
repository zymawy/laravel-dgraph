<?php

namespace Zymawy\Dgraph\Traits;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

trait RequestEventTrait
{
    use Dispatchable, SerializesModels;

    public string $type;
    public array $data;

    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }
}