<?php

namespace Zymawy\Dgraph\Responses;

class DgraphResponse
{
    public array $data;

    public function __construct(protected \GuzzleHttp\Psr7\Response|array $response)
    {
        $this->data = $this->getData();
    }

    public function getData(): array
    {
        if (is_array($this->response)) {
            return $this->response;
        }

        return json_decode($this->response->getBody(), true);
    }

    public function isSuccess()
    {
        if (is_array($this->response)) {
            return $this->response['code'] ?? 400 === 200;
        }

        return $this->response->getStatusCode() === 200 &&
            ! isset($this->data['errors']);
    }

    public function hasErrors(): bool
    {
        return isset($this->data['errors']);
    }

    public function getErrors(): ?array
    {
        return $this->data['errors'] ?? null;
    }
}
