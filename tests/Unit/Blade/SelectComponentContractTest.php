<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Blade\Components\Select;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// Architecture / contract tests for the Select Blade component.
//
// `_base/select.blade.php` renders `{!! $extraAttrs !!}` raw (so that
// `data-*` / `aria-*` callers' attributes flow through to the underlying
// <select>). The trust contract is that `$extraAttrs` is built INSIDE
// the framework Blade views via `$attributes->except([...])` — Laravel's
// ComponentAttributeBag escapes attribute values at render time, so the
// resulting string is HTML-safe.
//
// These tests guard the contract by asserting that the Select PHP
// component never declares `extraAttrs` as a constructor parameter or
// public property. If a future contributor adds `public string $extraAttrs`
// to Select.php, a Blade caller would be able to inject arbitrary content
// straight into the {!! !!} output.

it('Select component does not accept extraAttrs as a constructor parameter', function (): void {
    $reflection = new ReflectionClass(Select::class);
    $constructor = $reflection->getConstructor();
    expect($constructor)->not->toBeNull();

    $parameterNames = array_map(
        static fn (ReflectionParameter $p): string => $p->getName(),
        $constructor->getParameters(),
    );

    expect($parameterNames)->not->toContain('extraAttrs');
});

it('Select component does not expose extraAttrs as a public property', function (): void {
    $reflection = new ReflectionClass(Select::class);
    $publicPropertyNames = array_map(
        static fn (ReflectionProperty $p): string => $p->getName(),
        $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
    );

    expect($publicPropertyNames)->not->toContain('extraAttrs');
});

it('Select component does not expose extraAttrs from data()', function (): void {
    // Laravel's Component::data() merges public properties + with() output.
    // If `extraAttrs` ever leaks through here, the {!! !!} output in the
    // base view could receive caller-controlled content directly.
    $component = new Select(
        enum: SimpleStatusEnum::class,
        name: 'status',
    );

    $reflection = new ReflectionClass(Select::class);
    if ($reflection->hasMethod('data')) {
        $data = $component->data();
        expect($data)->not->toHaveKey('extraAttrs');
    } else {
        // No data() override — the contract is upheld by the public-
        // property check above.
        expect(true)->toBeTrue();
    }
});
