<?php

namespace Zymawy\Dgraph;

use Zymawy\Dgraph\Constants\ErrorConstants;
use Zymawy\Dgraph\Exceptions\DgraphException;
use Zymawy\Dgraph\Responses\DgraphResponse;

class Txn
{
    protected DgraphClient $client;
    protected bool $finished = false;
    protected bool $mutated = false;
    protected array $context;

    public function __construct(DgraphClient $client, array $options = [])
    {
        $this->client = $client;

        if (isset($options['bestEffort']) && $options['bestEffort'] && !isset($options['readOnly'])) {
            throw new DgraphException(ErrorConstants::ERR_BEST_EFFORT_REQUIRED_READ_ONLY);
        }

        $this->context = [
            'start_ts' => 0,
            'keys' => [],
            'preds' => [],
            'readOnly' => $options['readOnly'] ?? false,
            'bestEffort' => $options['bestEffort'] ?? false,
            'hash' => '',
        ];

        $this->client->beginTransaction();
    }

    public function query(string $query, array $vars = [], array $options = []): DgraphResponse
    {
        if ($this->finished) {
            throw new DgraphException(ErrorConstants::ERR_FINISHED);
        }

        $response = $this->client->query($query, $vars, $options['debug'] ?? false);
        $this->mergeContext($response->getData()['extensions']['txn'] ?? []);

        return $response;
    }

    public function mutate(array $mutation, bool $commitNow = false): DgraphResponse
    {
        if ($this->finished) {
            throw new DgraphException(ErrorConstants::ERR_FINISHED);
        }

        $this->mutated = true;
        $mutation['start_ts'] = $this->context['start_ts'];
        $mutation['hash'] = $this->context['hash'];

        $response = $this->client->mutate($mutation, $commitNow);
        if ($commitNow) {
            $this->finished = true;
        }

        $this->mergeContext($response->getData()['extensions']['txn'] ?? []);
        return $response;
    }

    public function commit(): DgraphResponse
    {
        if ($this->finished) {
            throw new DgraphException(ErrorConstants::ERR_FINISHED);
        }

        $this->finished = true;

        if (!$this->mutated) {
            return new DgraphResponse(['code' => 400, 'status' => 'No mutations to commit']);
        }

        try {
            return $this->client->commit();
        } catch (\Exception $e) {
            throw new DgraphException("Transaction commit failed: " . $e->getMessage());
        }
    }

    public function discard(): DgraphResponse
    {
        if ($this->finished) {
            return new DgraphResponse(['code' => 400, 'status' => 'Transaction already finished']);
        }

        $this->finished = true;

        if (!$this->mutated) {
            return new DgraphResponse(['code' => 400, 'status' => 'No mutations to discard']);
        }

        $this->context['aborted'] = true;
        return $this->client->abort();
    }

    private function mergeContext(array $src): void
    {
        if (empty($src)) {
            return;
        }

        $this->context['hash'] = $src['hash'] ?? '';

        if ($this->context['start_ts'] === 0) {
            $this->context['start_ts'] = $src['start_ts'];
        } elseif ($this->context['start_ts'] !== $src['start_ts']) {
            throw new DgraphException("StartTs mismatch");
        }

        if (isset($src['keys'])) {
            $this->context['keys'] = array_unique(array_merge($this->context['keys'], $src['keys']));
        }
        if (isset($src['preds'])) {
            $this->context['preds'] = array_unique(array_merge($this->context['preds'], $src['preds']));
        }
    }
}
