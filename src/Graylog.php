<?php

namespace Rpungello\Graylog;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Foundation\Application;
use Rpungello\Graylog\TimeRange\TimeRange;

class Graylog
{
    protected Client $client;

    public function __construct(Application $app)
    {
        $uri = tap(new Uri)
            ->withScheme($app['config']->get('graylog.https') ? 'https' : 'http')
            ->withHost($app['config']->get('graylog.host'))
            ->withPort($app['config']->get('graylog.port'));

        $this->client = new Client([
            'base_uri' => $uri,
            RequestOptions::AUTH => [
                $app['config']->get('graylog.token'),
                'token',
            ],
            RequestOptions::HEADERS => [
                'X-Requested-By' => 'rpungello/laravel-graylog',
            ],
        ]);
    }

    /**
     * Gets the cluster info for the configured Graylog endpoint
     *
     * @return array<array{
     *     facility: string,
     *     codename: string,
     *     node_id: string,
     *     cluster_id: string,
     *     version: string,
     *     started_at: string,
     *     hostname: string,
     *     lifecycle: string,
     *     lb_status: string,
     *     timezone: string,
     *     operating_system: string,
     *     is_leader: boolean,
     *     is_processing: boolean
     * }>
     *
     * @throws GuzzleException
     */
    public function cluster(): array
    {
        return array_values(
            json_decode(
                $this->client->get('/api/cluster')->getBody(),
                true
            )
        );
    }

    /**
     * @return array<array>
     *
     * @throws GuzzleException
     */
    public function search(string|array $streams, TimeRange $timeRange, string $query, array $fields, int $perPage = 100): array
    {
        $offset = 0;
        $response = [];
        while (! empty($results = $this->executeSearch($streams, $timeRange, $query, $fields, $perPage, $offset))) {
            $response = array_merge($response, $results);
            $offset += $perPage;
        }

        return $response;
    }

    /**
     * @throws GuzzleException
     */
    public function countResults(string|array $streams, TimeRange $timeRange, string $query, int $perPage = 100): int
    {
        $offset = 0;
        $count = 0;
        while (! empty($results = $this->executeSearch($streams, $timeRange, $query, [], $perPage, $offset))) {
            $count += count($results);
            $offset += $perPage;
        }

        return $count;
    }

    /**
     * @return array<array>
     *
     * @throws GuzzleException
     */
    public function executeSearch(string|array $streams, TimeRange $timeRange, string $query, array $fields, int $perPage = 100, int $from = 0): array
    {
        $response = $this->client->post('/api/search/messages', [
            RequestOptions::JSON => [
                'streams' => is_array($streams) ? $streams : [$streams],
                'timerange' => $timeRange->toArray(),
                'query' => $query,
                'fields' => $fields,
                'size' => $perPage,
                'from' => $from,
            ],
        ]);

        $json = json_decode($response->getBody(), true);

        return array_key_exists('datarows', $json) ? $json['datarows'] : [];
    }
}
