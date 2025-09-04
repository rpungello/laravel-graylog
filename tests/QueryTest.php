<?php

use Rpungello\Graylog\Query\Builder;

it('can construct basic AND queries', function () {
    $query = Builder::begin()->and('field1', 'value1');
    expect((string)$query)->toBe('field1:"value1"');
});

it('can construct basic AND queries with two fields', function () {
    $query = Builder::begin()
        ->and('field1', 'value1')
        ->and('field2', 'value2');
    expect((string)$query)->toBe('field1:"value1" && field2:"value2"');
});

it('can construct basic OR queries', function () {
    $query = Builder::begin()->or('field1', 'value1');
    expect((string)$query)->toBe('field1:"value1"');
});

it('can construct basic OR queries with two fields', function () {
    $query = Builder::begin()
        ->or('field1', 'value1')
        ->or('field2', 'value2');
    expect((string)$query)->toBe('field1:"value1" || field2:"value2"');
});

it('can construct nested AND queries', function () {
    $query = Builder::begin()
        ->and('field1', 'value1')
        ->and(function (Builder $b) {
            $b->or('field2', 'value2');
            $b->or('field3', 'value3');
        });
    expect((string)$query)->toBe('field1:"value1" && (field2:"value2" || field3:"value3")');
});

it('can construct nested OR queries', function () {
    $query = Builder::begin()
        ->or('field1', 'value1')
        ->or(function (Builder $b) {
            $b->and('field2', 'value2');
            $b->and('field3', 'value3');
        });
    expect((string)$query)->toBe('field1:"value1" || (field2:"value2" && field3:"value3")');
});
