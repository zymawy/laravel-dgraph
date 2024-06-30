<?php

namespace Zymawy\Dgraph\Constants;

class ErrorConstants
{
    public const ERR_NO_CLIENTS = 'No clients provided in DgraphClient constructor';

    public const ERR_FINISHED = 'Transaction has already been committed or discarded';

    public const ERR_ABORTED = 'Transaction has been aborted. Please retry';

    public const ERR_BEST_EFFORT_REQUIRED_READ_ONLY = 'Best effort only works for read-only queries';
}
