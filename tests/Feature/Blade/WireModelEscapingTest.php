<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\FlaggedPermissionEnum;

// Regression tests for the F2 audit finding (2026-05-16): the
// `$wireModelAttr` string in `_base/radio.blade.php` and
// `_base/checkboxes.blade.php` used to be built via concatenation
// without escaping. A `"` in the wireModel prop would break out of
// the attribute. These tests pin the defensive-escape behaviour.

it('radio HTML-escapes a `"` in the wireModel prop', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" wire-model=\'foo"bar\' />',
        ['enum' => StatusEnum::class],
    );

    // The injected `"` is encoded as `&quot;`, the attribute stays one
    // attribute, no breakout.
    expect($html)->toContain('wire:model="foo&quot;bar"');
});

it('radio HTML-escapes `&` in the wireModel modifier', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" wire-model="status" wire-model-modifier="live&amp" />',
        ['enum' => StatusEnum::class],
    );

    expect($html)->toContain('wire:model.live&amp;amp');
});

it('radio passes a plain wireModel prop through verbatim', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" wire-model="status" wire-model-modifier="live" />',
        ['enum' => StatusEnum::class],
    );

    expect($html)->toContain('wire:model.live="status"');
});

it('checkboxes HTML-escapes a `"` in the wireModel prop', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="flags[]" wire-model=\'foo"bar\' />',
        ['enum' => FlaggedPermissionEnum::class],
    );

    expect($html)->toContain('wire:model="foo&quot;bar"');
});

it('checkboxes passes a plain wireModel prop through verbatim', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="flags[]" wire-model="flags" />',
        ['enum' => FlaggedPermissionEnum::class],
    );

    expect($html)->toContain('wire:model="flags"');
});
