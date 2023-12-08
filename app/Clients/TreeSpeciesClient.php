<?php

namespace App\Clients;

use App\Exceptions\ExternalAPIException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class TreeSpeciesClient
{
    protected $url;

    protected $client;

    public function __construct(Client $client)
    {
        $this->url = config('services.tree_species_api.url');
        $this->client = $client;
    }

    public function search(string $searchString)
    {
        try {
            $response = json_decode($this->client->request('POST', $this->url, ['json' => [
                'opts' => [
                    'sources' => 'wfo,wcvp',
                    'class' => 'wfo',
                    'mode' => 'resolve',
                    'matches' => 'all',
                    'acc' => '0',
                ],
                'data' => [
                    [1, $searchString],
                ],
            ]])->getBody());
        } catch (TransferException $exception) {
            throw new ExternalAPIException();
        }

        return $response;
    }
}
