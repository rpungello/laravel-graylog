<?php

use Rpungello\Graylog\Query\Builder;

it('can construct basic AND queries', function () {
    $query = Builder::begin()->and('field1', 'value1');
    expect((string) $query)->toBe('field1:"value1"');
});

it('can construct basic AND queries with two fields', function () {
    $query = Builder::begin()
        ->and('field1', 'value1')
        ->and('field2', 'value2');
    expect((string) $query)->toBe('field1:"value1" && field2:"value2"');
});

it('can construct basic OR queries', function () {
    $query = Builder::begin()->or('field1', 'value1');
    expect((string) $query)->toBe('field1:"value1"');
});

it('can construct basic OR queries with two fields', function () {
    $query = Builder::begin()
        ->or('field1', 'value1')
        ->or('field2', 'value2');
    expect((string) $query)->toBe('field1:"value1" || field2:"value2"');
});

it('can construct nested AND queries', function () {
    $query = Builder::begin()
        ->and('field1', 'value1')
        ->and(function (Builder $b) {
            $b->or('field2', 'value2');
            $b->or('field3', 'value3');
        });
    expect((string) $query)->toBe('field1:"value1" && (field2:"value2" || field3:"value3")');
});

it('can construct nested OR queries', function () {
    $query = Builder::begin()
        ->or('field1', 'value1')
        ->or(function (Builder $b) {
            $b->and('field2', 'value2');
            $b->and('field3', 'value3');
        });
    expect((string) $query)->toBe('field1:"value1" || (field2:"value2" && field3:"value3")');
});

it('can construct three-tier queries', function () {
    $query = Builder::begin()
        ->and('field1', 'value1')
        ->and(function (Builder $b) {
            $b->or('field2', 'value2');
            $b->or(function (Builder $b) {
                $b->and('field3', 'value3');
                $b->and('field4', 'value4');
            });
        });
    expect((string) $query)->toBe('field1:"value1" && (field2:"value2" || (field3:"value3" && field4:"value4"))');
});

it('can construct basic regex queries', function () {
    $query = Builder::begin()->and('field1', '\d+', 'regex');
    expect((string) $query)->toBe('field1:/\d+/');
});

it('can construct basic greater than queries', function () {
    $query = Builder::begin()->and('field1', 5, '>');
    expect((string) $query)->toBe('field1:>5');
});

it('can construct basic greater than or equal queries', function () {
    $query = Builder::begin()->and('field1', 5, '>=');
    expect((string) $query)->toBe('field1:>=5');
});

it('can construct basic less than queries', function () {
    $query = Builder::begin()->and('field1', 5, '<');
    expect((string) $query)->toBe('field1:<5');
});

it('can construct basic less than or equal queries', function () {
    $query = Builder::begin()->and('field1', 5, '<=');
    expect((string) $query)->toBe('field1:<=5');
});

it('can construct complex queries', function () {
    $query = Builder::begin()->and('level', 4, '<')
        ->and(function (Builder $b) {
            $b->or('field1', '\/foo\/bar\/\d+', 'regex');
            $b->or('field2', 5, '>=');
        });
    expect((string) $query)->toBe('level:<4 && (field1:/\/foo\/bar\/\d+/ || field2:>=5)');
});
it('can construct basic not equal queries', function () {
    $query = Builder::begin()->and('field1', 5, '!=');
    expect((string)$query)->toBe('NOT field1:"5"');
});
