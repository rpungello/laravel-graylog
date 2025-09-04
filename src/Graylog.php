<?php

namespace Rpungello\Graylog;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Foundation\Application;

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
}
