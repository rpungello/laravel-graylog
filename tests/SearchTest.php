<?php

use Illuminate\Support\Facades\Http;
use Rpungello\Graylog\Graylog;
use Rpungello\Graylog\TimeRange\Relative;

it('can parse search results', function () {
    // Fake the HTTP request that Graylog::executeSearch() will perform.
    Http::fake([
        // The URL is built from the base URL + path, but we can fake “*” to match any request.
        '*' => Http::response(json_encode([
            'datarows' => [
                ['value1', 'value2'],
                ['value3', 'value4'],
            ],
            'schema' => [
                [
                    'name' => 'field: field1',
                    'column_type' => 'field',
                    'type' => 'string',
                    'field' => 'field1',
                ],
                [
                    'name' => 'field: field2',
                    'column_type' => 'field',
                    'type' => 'string',
                    'field' => 'field2',
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json']),
    ]);

    $client = new Graylog(app());

    $results = $client->executeSearch(
        '11111',
        new Relative(60),
        '',
        ['field1', 'field2']
    );

    expect($results)->toHaveCount(2)
        ->and(array_keys($results[0]))->toBe(['field1', 'field2'])
        ->and(array_keys($results[1]))->toBe(['field1', 'field2'])
        ->and($results[0]['field1'])->toBe('value1')
        ->and($results[0]['field2'])->toBe('value2')
        ->and($results[1]['field1'])->toBe('value3')
        ->and($results[1]['field2'])->toBe('value4');
});

it('can limit max results', function () {
    Http::fake([
        '*' => Http::response(json_encode([
            'datarows' => [
                ['value1', 'value2'],
                ['value3', 'value4'],
            ],
            'schema' => [
                [
                    'name' => 'field: field1',
                    'column_type' => 'field',
                    'type' => 'string',
                    'field' => 'field1',
                ],
                [
                    'name' => 'field: field2',
                    'column_type' => 'field',
                    'type' => 'string',
                    'field' => 'field2',
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json']),
    ]);

    $client = new Graylog(app());

    $results = $client->search(
        '11111',
        new Relative(60),
        '',
        ['field1', 'field2'],
        maxResults: 1
    );

    expect($results)->toHaveCount(1);
});

it('can count results', function () {
    // First request returns two rows, second request returns none (end of pagination).
    Http::fakeSequence()
        ->push(json_encode([
            'datarows' => [
                ['value1', 'value2'],
                ['value3', 'value4'],
            ],
            'schema' => [
                [
                    'name' => 'field: field1',
                    'column_type' => 'field',
                    'type' => 'string',
                    'field' => 'field1',
                ],
                [
                    'name' => 'field: field2',
                    'column_type' => 'field',
                    'type' => 'string',
                    'field' => 'field2',
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json'])
        ->push(json_encode([
            'datarows' => [],
            'schema' => [
                [
                    'name' => 'field: field1',
                    'column_type' => 'field',
                    'type' => 'string',
                    'field' => 'field1',
                ],
                [
                    'name' => 'field: field2',
                    'column_type' => 'field',
                    'type' => 'string',
                    'field' => 'field2',
                ],
            ],
        ]), 200, ['Content-Type' => 'application/json']);

    $client = new Graylog(app());

    $result = $client->countResults(
        '11111',
        new Relative(60),
        '',
    );

    expect($result)->toBe(2);
});
