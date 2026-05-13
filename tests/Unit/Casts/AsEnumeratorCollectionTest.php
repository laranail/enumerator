<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Simtabi\Laranail\Enumerator\Casts\AsEnumeratorCollection;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;

// AsEnumeratorCollection — JSON-column cast to/from a Collection of enums.

function asCollectionModel(): Model
{
    return new class extends Model {};
}

it('of() returns the cast spec string', function (): void {
    expect(AsEnumeratorCollection::of(StatusEnum::class))
        ->toBe(AsEnumeratorCollection::class . ':' . StatusEnum::class);
});

it('get() returns an empty Collection for null', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    $result = $cast->get(asCollectionModel(), 'statuses', null, []);
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->isEmpty())->toBeTrue();
});

it('get() returns an empty Collection for an empty string', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    expect($cast->get(asCollectionModel(), 'statuses', '', [])->isEmpty())->toBeTrue();
});

it('get() decodes a JSON string into a Collection of cases', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    $json = '["active","pending"]';
    $result = $cast->get(asCollectionModel(), 'statuses', $json, []);
    expect($result->count())->toBe(2);
    expect($result->all())->toContain(StatusEnum::Active);
    expect($result->all())->toContain(StatusEnum::Pending);
});

it('get() returns an empty Collection when the decoded value is not an array', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    expect($cast->get(asCollectionModel(), 'statuses', '"not-an-array"', [])->isEmpty())->toBeTrue();
});

it('get() accepts an already-decoded array value', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    $result = $cast->get(asCollectionModel(), 'statuses', ['active', 'inactive'], []);
    expect($result->count())->toBe(2);
});

it('set() returns null for null', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    expect($cast->set(asCollectionModel(), 'statuses', null, []))->toBeNull();
});

it('set() returns null for non-iterable input', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    expect($cast->set(asCollectionModel(), 'statuses', 42, []))->toBeNull();
});

it('set() encodes a Collection of cases to a JSON array of backing values', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    $collection = new Collection([StatusEnum::Active, StatusEnum::Pending]);
    $json = $cast->set(asCollectionModel(), 'statuses', $collection, []);
    expect($json)->toBeString();

    $decoded = json_decode($json, true);
    expect($decoded)->toBe(['active', 'pending']);
});

it('set() encodes a plain array of mixed inputs', function (): void {
    $cast = new AsEnumeratorCollection(StatusEnum::class);
    $json = $cast->set(asCollectionModel(), 'statuses', [StatusEnum::Active, 'pending'], []);
    $decoded = json_decode($json, true);
    expect($decoded)->toBe(['active', 'pending']);
});
