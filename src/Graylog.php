<?php

namespace Rpungello\Graylog;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Rpungello\Graylog\Query\Builder;
use Rpungello\Graylog\TimeRange\TimeRange;
use ValueError;

class Graylog
{
    protected Client $client;

    public function __construct(Application $app, ?HandlerStack $stack = null)
    {
        $uri = tap(new Uri)
            ->withScheme($app['config']->get('graylog.https') ? 'https' : 'http')
            ->withHost($app['config']->get('graylog.host'))
            ->withPort($app['config']->get('graylog.port'));

        $options = [
            'base_uri' => $uri,
            RequestOptions::AUTH => [
                $app['config']->get('graylog.token'),
                'token',
            ],
            RequestOptions::HEADERS => [
                'X-Requested-By' => 'rpungello/laravel-graylog',
            ],
        ];

        if (! empty($stack) && $stack->hasHandler()) {
            $options['handler'] = $stack;
        }

        $this->client = new Client($options);
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
    public function search(string|array $streams, TimeRange $timeRange, string|Builder $query, array $fields, int $perPage = 100, ?int $maxResults = null): array
    {
        $offset = 0;
        $response = [];
        while (! empty($results = $this->executeSearch($streams, $timeRange, $query, $fields, $perPage, $offset))) {
            $response = array_merge($response, $results);
            $offset += $perPage;
            if (is_int($maxResults) && $offset > $maxResults) {
                return array_slice($response, 0, $maxResults);
            }
        }

        return $response;
    }

    /**
     * @throws GuzzleException
     */
    public function countResults(string|array $streams, TimeRange $timeRange, string|Builder $query, int $perPage = 100): int
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
     * @throws ValueError if the datarows from Graylog contain a different number of fields as the schema returned
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
        $rows = Arr::get($json, 'datarows', []);
        $fields = array_map(fn (array $record) => $record['field'], Arr::get($json, 'schema', []));

        return array_map(fn (array $row) => array_combine($fields, $row), $rows);
    }
}
