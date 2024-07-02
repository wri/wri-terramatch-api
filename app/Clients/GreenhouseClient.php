<?php

namespace App\Clients;

use App\Exceptions\ExternalAPIException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GreenhouseClient
{
    protected string $url;

    protected string $token;

    protected Client $client;

    public function __construct(Client $client)
    {
        $this->url = config('services.greenhouse_api.url');
        $this->token = config('services.greenhouse_api.token');
        $this->client = $client;
    }

    public function getEnabled(): bool
    {
        return ! empty($this->url) && ! empty($this->token);
    }

    /**
     * @throws ExternalAPIException
     */
    public function notifyPolygonUpdated(string $polygonUuid): ?array
    {
        return $this->runQuery('tmNotifyFeatureUpdated', $polygonUuid);
    }

    /**
     * @throws ExternalAPIException
     */
    public function notifyMediaDeleted(string $mediaUuid): ?array
    {
        return $this->runQuery('tmNotifyMediaDeleted', $mediaUuid);
    }

    /**
     * @throws ExternalAPIException
     */
    protected function runQuery(string $functionName, string $uuid): ?array
    {
        if (! $this->getEnabled()) {
            return null;
        }

        try {
            $response = $this->client->request('POST', $this->url, [
                'headers' => [
                    'api-key' => $this->token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'query' => 'mutation ($uuid: Id!) { ' . $functionName . '(uuid: $uuid) { ok } }',
                    'variables' => [ 'uuid' => $uuid ],
                ]),
            ]);
        } catch (GuzzleException $exception) {
            Log::error(
                "Exception sending query to Greenhouse [fn=$functionName, uuid=$uuid]: " .
                $exception->getMessage()
            );

            throw new ExternalAPIException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
