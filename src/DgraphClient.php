<?php

namespace Zymawy\Dgraph;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Zymawy\Dgraph\Api\Mutation;
use Zymawy\Dgraph\Api\Operation;
use Zymawy\Dgraph\Api\Query;
use Zymawy\Dgraph\Events\AfterRequest;
use Zymawy\Dgraph\Events\BeforeRequest;
use Zymawy\Dgraph\Events\RequestFailed;
use Zymawy\Dgraph\Events\RequestInProgress;
use Zymawy\Dgraph\Responses\DgraphResponse;

class DgraphClient
{
    protected Client $client;

    protected string $url;

    protected int $startTs = 0;

    protected array $keys = [];

    protected array $preds = [];

    protected ?string $accessToken = null;

    protected ?string $refreshToken = null;

    protected bool $autoRefresh = false;

    protected ?int $autoRefreshTimer = null;

    protected bool $debugMode = false; // Added debug mode flag

    public function __construct(string $url, array $options = [])
    {
        $this->url = rtrim($url, '/');
        $this->client = new Client(array_merge(['base_uri' => $this->url], $options));
        $this->beginTransaction(); // Automatically start a transaction when the client is instantiated
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function getPreds(): array
    {
        return $this->preds;
    }

    public function beginTransaction(): void
    {
        $this->startTs = 0; // Start a new transaction with start_ts set to 0
        $this->keys = [];
        $this->preds = [];
    }

    public function alter(Operation $operation): DgraphResponse
    {
        return $this->sendRequest('post', '/alter', [
            'body' => $operation->build(),
            'headers' => ['Content-Type' => 'application/dql'],
        ], 'alter');
    }

    public function query(Query $query, array $vars = [], bool $debug = false): DgraphResponse
    {
        $headers = ['Content-Type' => 'application/json'];
        $body = ['query' => $query->build()];

        if (! empty($vars)) {
            $body['variables'] = $vars;
        }

        return $this->sendRequest('post', "/query?start_ts={$this->startTs}&debug={$debug}", [
            'body' => json_encode($body),
            'headers' => $headers,
        ], 'query');
    }

    public function mutate(Mutation $mutation, bool $commitNow = false): DgraphResponse
    {
        $mutation->setDatum('start_ts', $this->startTs);
        $response = $this->sendRequest('post', '/mutate?commitNow='.($commitNow ? 'true' : 'false'), [
            'body' => json_encode($mutation->build()),
            'headers' => ['Content-Type' => 'application/json'],
        ], 'mutate');

        $this->updateTransactionState($response->getData());

        return $response;
    }

    public function getHealth(bool $all = false): DgraphResponse
    {
        return $this->sendRequest('get', '/health'.($all ? '?all=true' : ''), [], 'health');
    }

    public function getState(): DgraphResponse
    {
        return $this->sendRequest('get', '/state', [], 'state');
    }

    public function abort(): DgraphResponse
    {
        return $this->sendRequest('post', '/abort', [
            'body' => json_encode(['start_ts' => $this->startTs]),
            'headers' => ['Content-Type' => 'application/json'],
        ], 'abort');
    }

    public function login(string $userid, string $password): DgraphResponse
    {
        $body = ['userid' => $userid, 'password' => $password];
        $response = $this->sendRequest('post', '/login', [
            'body' => json_encode($body),
            'headers' => ['Content-Type' => 'application/json'],
        ], 'login');

        $data = $response->getData();
        $this->accessToken = $data['accessJWT'];
        $this->refreshToken = $data['refreshJWT'];
        $this->maybeStartRefreshTimer($this->accessToken);

        return $response;
    }

    public function commit(): DgraphResponse
    {
        $mutation = [
            'commit' => true,
            'start_ts' => $this->startTs,
            'keys' => $this->keys,
            'preds' => $this->preds,
        ];

        return $this->sendRequest('post', '/commit', [
            'body' => json_encode($mutation),
            'headers' => ['Content-Type' => 'application/json'],
        ], 'commit');
    }

    private function sendRequest(string $method, string $uri, array $options, string $type): DgraphResponse
    {
        $options['headers'] = $options['headers'] ?? [];
        if ($this->accessToken && $type !== 'login') {
            $options['headers']['X-Dgraph-AccessToken'] = $this->accessToken;
        }

        event(new BeforeRequest($type, $options));

        try {
            event(new RequestInProgress($type, $options));
            $response = new DgraphResponse($this->client->request($method, $uri, $options));
        } catch (RequestException $e) {
            $data = ['error' => $e->getMessage()];
            event(new RequestFailed($type, $data));

            return new DgraphResponse($data);
        }
        if ($this->debugMode) {
            Log::debug('Response data: ', [
                json_encode($response->getData()),
            ]);
        }

        event(new AfterRequest($type, $response->data));

        return $response;
    }

    public function fetchSchema(): DgraphResponse
    {
        return $this->sendRequest('post', '/query', [
            'body' => json_encode(['query' => 'schema { }']),
            'headers' => ['Content-Type' => 'application/json'],
        ], 'query');
    }

    private function updateTransactionState(array $data): void
    {
        if (isset($data['extensions']['txn'])) {
            $txn = $data['extensions']['txn'];
            $this->startTs = $txn['start_ts'];
            if (isset($txn['keys'])) {
                $this->keys = array_merge($this->keys, $txn['keys']);
            }
            if (isset($txn['preds'])) {
                $this->preds = array_merge($this->preds, $txn['preds']);
            }
            $this->keys = array_unique($this->keys);
            $this->preds = array_unique($this->preds);
        }
    }

    private function maybeStartRefreshTimer(?string $accessToken): void
    {
        if (! $accessToken || ! $this->autoRefresh) {
            return;
        }
        $this->cancelRefreshTimer();

        $decoded = (array) jwt_decode($accessToken);
        $exp = $decoded['exp'] * 1000 - time() - 5000;

        $this->autoRefreshTimer = (int) setTimeout(function () {
            if ($this->refreshToken) {
                $this->loginWithRefreshToken($this->refreshToken);
            }
        }, max(2000, $exp));
    }

    private function loginWithRefreshToken(string $refreshToken): void
    {
        $response = $this->sendRequest('post', '/login', [
            'body' => json_encode(['refresh_token' => $refreshToken]),
            'headers' => ['Content-Type' => 'application/json'],
        ], 'login');

        $data = $response->getData();
        $this->accessToken = $data['accessJWT'];
        $this->refreshToken = $data['refreshJWT'];
    }

    private function cancelRefreshTimer(): void
    {
        if ($this->autoRefreshTimer !== null) {
            clearTimeout($this->autoRefreshTimer);
            $this->autoRefreshTimer = null;
        }
    }

    public function setDebugMode(bool $mode = true): void
    {
        $this->debugMode = $mode;
    }
}
