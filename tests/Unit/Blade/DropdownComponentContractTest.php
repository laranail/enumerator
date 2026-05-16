<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Blade\Components\Dropdown;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// Architecture / contract tests for the Dropdown Blade component.
// Same trust contract as the Select / Radio / Checkboxes contract
// tests — Dropdown inherits the `{!! $extraAttrs !!}` raw render from
// Select via parent::__construct(), so the no-extraAttrs-as-constructor-
// param rule applies to Dropdown's own constructor too.
//
// Dropdown also gained `wireModel` / `wireModelModifier` props in PR-μ
// (v0.4.0). These flow into `$wireModelAttr` via htmlspecialchars()
// (D75) so they're safe as constructor props — same as on radio +
// checkboxes.

it('Dropdown component does not accept extraAttrs as a constructor parameter', function (): void {
    $reflection = new ReflectionClass(Dropdown::class);
    $constructor = $reflection->getConstructor();
    expect($constructor)->not->toBeNull();

    $parameterNames = array_map(
        static fn (ReflectionParameter $p): string => $p->getName(),
        $constructor->getParameters(),
    );

    expect($parameterNames)->not->toContain('extraAttrs');
});

it('Dropdown component does not expose extraAttrs as a public property', function (): void {
    $reflection = new ReflectionClass(Dropdown::class);
    $publicPropertyNames = array_map(
        static fn (ReflectionProperty $p): string => $p->getName(),
        $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
    );

    expect($publicPropertyNames)->not->toContain('extraAttrs');
});

it('Dropdown component does not expose extraAttrs from data()', function (): void {
    $component = new Dropdown(
        enum: SimpleStatusEnum::class,
        name: 'status',
    );

    $reflection = new ReflectionClass(Dropdown::class);
    if ($reflection->hasMethod('data')) {
        $data = $component->data();
        expect($data)->not->toHaveKey('extraAttrs');
    } else {
        expect(true)->toBeTrue();
    }
});

it('Dropdown component exposes the v0.4.0 wireModel / wireModelModifier props', function (): void {
    // PR-μ adds these as public constructor-promoted properties — they
    // ARE allowed in the public surface because their values flow
    // through htmlspecialchars() in the view (D75).
    $reflection = new ReflectionClass(Dropdown::class);
    $constructor = $reflection->getConstructor();
    expect($constructor)->not->toBeNull();

    $parameterNames = array_map(
        static fn (ReflectionParameter $p): string => $p->getName(),
        $constructor->getParameters(),
    );

    expect($parameterNames)->toContain('wireModel');
    expect($parameterNames)->toContain('wireModelModifier');
});
