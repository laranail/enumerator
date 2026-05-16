<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Blade\Components\Checkboxes;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// Architecture / contract tests for the Checkboxes Blade component.
// Mirror of SelectComponentContractTest and RadioComponentContractTest
// — same trust contract: `_base/checkboxes.blade.php` renders
// `{!! $extraAttrs !!}` raw so that data-* / aria-* / wire:model.* /
// x-* attributes from the caller flow through to the underlying
// <fieldset>. `$extraAttrs` is built INSIDE the framework Blade
// views via `$attributes->except([...])`; Laravel's ComponentAttributeBag
// escapes attribute values at render time, so the resulting string is
// HTML-safe.
//
// These tests pin the contract by asserting that Checkboxes never
// declares `extraAttrs` as a constructor parameter or public property
// — if a future contributor adds `public string $extraAttrs` to
// Checkboxes.php, a Blade caller could inject raw content straight
// into the {!! !!} output. Same goes for the v0.3.0 `wireModel` /
// `wireModelModifier` props — those flow through htmlspecialchars(),
// so they ARE allowed as constructor props.

it('Checkboxes component does not accept extraAttrs as a constructor parameter', function (): void {
    $reflection = new ReflectionClass(Checkboxes::class);
    $constructor = $reflection->getConstructor();
    expect($constructor)->not->toBeNull();

    $parameterNames = array_map(
        static fn (ReflectionParameter $p): string => $p->getName(),
        $constructor->getParameters(),
    );

    expect($parameterNames)->not->toContain('extraAttrs');
});

it('Checkboxes component does not expose extraAttrs as a public property', function (): void {
    $reflection = new ReflectionClass(Checkboxes::class);
    $publicPropertyNames = array_map(
        static fn (ReflectionProperty $p): string => $p->getName(),
        $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
    );

    expect($publicPropertyNames)->not->toContain('extraAttrs');
});

it('Checkboxes component does not expose extraAttrs from data()', function (): void {
    $component = new Checkboxes(
        enum: SimpleStatusEnum::class,
        name: 'permissions[]',
    );

    $reflection = new ReflectionClass(Checkboxes::class);
    if ($reflection->hasMethod('data')) {
        $data = $component->data();
        expect($data)->not->toHaveKey('extraAttrs');
    } else {
        expect(true)->toBeTrue();
    }
});
