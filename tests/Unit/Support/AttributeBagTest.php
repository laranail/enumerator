<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Support\AttributeBag;

// AttributeBag — snapshot DTO for resolved per-case attributes.

it('starts with all-null/empty defaults', function (): void {
    $bag = new AttributeBag;

    expect($bag->label)->toBeNull();
    expect($bag->description)->toBeNull();
    expect($bag->color)->toBeNull();
    expect($bag->icon)->toBeNull();
    expect($bag->help)->toBeNull();
    expect($bag->order)->toBeNull();
    expect($bag->bit)->toBeNull();
    expect($bag->meta)->toBeNull();
    expect($bag->cssClasses)->toBe([]);
});

it('metaValue() returns the keyed value when meta is set', function (): void {
    $bag = new AttributeBag;
    $bag->meta = ['featured' => true, 'priority' => 3];

    expect($bag->metaValue('featured'))->toBeTrue();
    expect($bag->metaValue('priority'))->toBe(3);
});

it('metaValue() returns null for an unknown key', function (): void {
    $bag = new AttributeBag;
    $bag->meta = ['featured' => true];

    expect($bag->metaValue('missing'))->toBeNull();
});

it('metaValue() returns null when meta is unset', function (): void {
    expect((new AttributeBag)->metaValue('anything'))->toBeNull();
});

it('cssClassFor() returns the framework-keyed class string', function (): void {
    $bag = new AttributeBag;
    $bag->cssClasses = [
        'bootstrap' => 'badge bg-success',
        'tailwind' => 'bg-green-500',
    ];

    expect($bag->cssClassFor('bootstrap'))->toBe('badge bg-success');
    expect($bag->cssClassFor('tailwind'))->toBe('bg-green-500');
});

it('cssClassFor() returns null for unknown frameworks', function (): void {
    $bag = new AttributeBag;
    $bag->cssClasses = ['bootstrap' => 'badge bg-success'];

    expect($bag->cssClassFor('daisyui'))->toBeNull();
});

it('cssClassFor() returns null when cssClasses is empty', function (): void {
    expect((new AttributeBag)->cssClassFor('bootstrap'))->toBeNull();
});
