<?php

namespace Rpungello\Graylog;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Rpungello\Graylog\Query\Builder;
use Rpungello\Graylog\TimeRange\TimeRange;
use ValueError;

class Graylog
{
    protected string $baseUrl;

    protected string $authToken;

    protected array $defaultHeaders;

    public function __construct(Application $app, protected int $connectTimeout = 2, protected int $timeout = 16)
    {
        // Build the base URL from config
        $scheme = $app['config']->get('graylog.https') ? 'https' : 'http';
        $host = $app['config']->get('graylog.host');
        $port = $app['config']->get('graylog.port');

        $this->authToken = $app['config']->get('graylog.token');
        $this->baseUrl = "$scheme://$host:$port";

        // Default request headers
        $this->defaultHeaders = [
            'X-Requested-By' => 'rpungello/laravel-graylog',
        ];
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
     *     is_leader: bool,
     *     is_processing: bool
     * }>
     *
     * @throws ConnectionException
     */
    public function cluster(): array
    {
        $response = $this->pendingRequest()
            ->get('/api/cluster');

        return array_values(
            $response->json()
        );
    }

    /**
     * @return array<array>
     *
     * @throws ConnectionException
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
     * @throws ConnectionException
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
     * @throws ValueError if the datarows from Graylog contain a different number of fields as the schema returned
     * @throws ConnectionException
     */
    public function executeSearch(string|array $streams, TimeRange $timeRange, string $query, array $fields, int $perPage = 100, int $from = 0): array
    {
        $payload = [
            'streams' => is_array($streams) ? $streams : [$streams],
            'timerange' => $timeRange->toArray(),
            'query' => $query,
            'fields' => $fields,
            'size' => $perPage,
            'from' => $from,
        ];

        $response = $this->pendingRequest()
            ->post('/api/search/messages', $payload);

        $json = $response->json();
        $rows = Arr::get($json, 'datarows', []);
        $schema = array_map(fn (array $record) => $record['field'], Arr::get($json, 'schema', []));

        return array_map(fn (array $row) => array_combine($schema, $row), $rows);
    }

    /**
     * Create a pre‑configured HTTP client with the default headers and base URL.
     */
    private function pendingRequest(): PendingRequest
    {
        return Http::withHeaders($this->defaultHeaders)
            ->withBasicAuth($this->authToken, 'token')
            ->baseUrl($this->baseUrl)
            ->connectTimeout($this->connectTimeout)
            ->timeout($this->timeout);
    }
}
