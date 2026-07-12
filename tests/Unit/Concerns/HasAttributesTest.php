<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\RenderableStatusEnum;

// HasAttributes — attribute resolution + config-override layer.
// Locks in current behavior before (BehaviorCore extraction).

it('label() returns the #[Label] attribute value', function (): void {
    expect(StatusEnum::Active->label())->toBe('Active');
});

it('color() returns the #[Color] attribute value', function (): void {
    expect(StatusEnum::Active->color())->toBe('success');
});

it('icon() returns the #[Icon] attribute value', function (): void {
    expect(StatusEnum::Active->icon())->toBe('check-circle');
});

it('order() returns the #[Order] attribute value as int', function (): void {
    expect(StatusEnum::Active->order())->toBe(10);
});

it('cssClass() resolves per-framework via #[CssClass]', function (): void {
    expect(RenderableStatusEnum::Active->cssClass('bootstrap'))->toBe('badge bg-success');
    expect(RenderableStatusEnum::Active->cssClass('daisyui'))->toBe('badge badge-success');
});

it('cssClass() falls back to config framework when null', function (): void {
    config()->set('enumerator.css_framework', 'bootstrap');
    expect(RenderableStatusEnum::Active->cssClass())->toBe('badge bg-success');
});

it('meta() returns null for an undeclared key', function (): void {
    expect(StatusEnum::Active->meta('nothing'))->toBeNull();
});

it('attributes() returns only non-null/non-empty entries', function (): void {
    $attrs = StatusEnum::Active->attributes();
    expect($attrs)->toHaveKey('label')
        ->toHaveKey('color')
        ->toHaveKey('icon')
        ->toHaveKey('order');
});

it('config overrides take precedence over compile-time attributes', function (): void {
    config()->set('enumerator.overrides', [
        StatusEnum::class => ['Active' => ['color' => 'override-magenta']],
    ]);
    expect(StatusEnum::Active->color())->toBe('override-magenta');
});

it('config overrides for meta merge with declared meta', function (): void {
    config()->set('enumerator.overrides', [
        StatusEnum::class => ['Active' => ['meta' => ['paging' => true]]],
    ]);
    expect(StatusEnum::Active->meta('paging'))->toBeTrue();
});

it('metaAll() returns the merged meta array', function (): void {
    config()->set('enumerator.overrides', [
        StatusEnum::class => ['Active' => ['meta' => ['a' => 1, 'b' => 2]]],
    ]);
    $meta = StatusEnum::Active->metaAll();
    expect($meta)->toBeArray()
        ->toHaveKey('a')
        ->toHaveKey('b');
});

it('classDescription() reads the class-level #[Description] attribute', function (): void {
    // StatusEnum has no class-level #[Description], so returns null.
    expect(StatusEnum::classDescription())->toBeNull();
});
