<?php

namespace Zymawy\Dgraph\Commands;

use Illuminate\Console\Command;

class DgraphCommand extends Command
{
    public $signature = 'dgraph';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
