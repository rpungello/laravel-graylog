<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Rpungello\Graylog\Graylog;

it('can parse cluster data', function () {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode([
            '11111' => [
                'facility' => 'facility',
                'codename' => 'codename',
                'node_id' => '00000',
                'cluster_id' => '11111',
                'version' => '1.2.3',
                'started_at' => '2025-01-01T00:00:00.000Z',
                'hostname' => 'hostname',
                'lifecycle' => 'lifecycle',
                'lb_status' => 'active',
                'timezone' => 'UTC',
                'operating_system' => 'Linux',
                'is_leader' => true,
                'is_processing' => true,
            ]
        ]))
    ]);
    $client = new Graylog(app(), HandlerStack::create($mock));
    $cluster = $client->cluster();
    expect($cluster)->toBeArray()
        ->and($cluster)->toHaveCount(1)
        ->and($cluster[0])->toHaveKey('facility', 'facility')
        ->and($cluster[0])->toHaveKey('codename', 'codename')
        ->and($cluster[0])->toHaveKey('node_id', '00000')
        ->and($cluster[0])->toHaveKey('cluster_id', '11111')
        ->and($cluster[0])->toHaveKey('version', '1.2.3')
        ->and($cluster[0])->toHaveKey('started_at', '2025-01-01T00:00:00.000Z')
        ->and($cluster[0])->toHaveKey('hostname', 'hostname')
        ->and($cluster[0])->toHaveKey('lifecycle', 'lifecycle'
        )->and($cluster[0])->toHaveKey('lb_status', 'active')
        ->and($cluster[0])->toHaveKey('timezone', 'UTC')
        ->and($cluster[0])->toHaveKey('operating_system', 'Linux')
        ->and($cluster[0])->toHaveKey('is_leader', true)
        ->and($cluster[0])->toHaveKey('is_processing', true);
});
