<?php

namespace Rpungello\Graylog;

use GuzzleHttp\Client;
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
            ]
        ]);
    }
}
