<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// Feature coverage for the v0.4.0 PR-μ :wireModel prop on
// <x-laranail-enumerator::dropdown>. Mirrors the PR-γ pattern that
// already exists on radio + checkboxes — closes the parity gap.
//
// The dropdown emits in two shapes:
//   - Alpine listbox path (searchable / clearable / multiple, !disabled):
//     wire:model goes on the hidden <input>(s) inside the wrapper. For
//     multi-select this means each iteration of the <template x-for>
//     hidden-input loop carries it.
//   - Native <select> fallback path (no Alpine engagement): wire:model
//     goes on the <select> element itself.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

// Alpine path

it('dropdown (Alpine, single) emits wire:model on the hidden input', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" wire-model="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->toContain('wire:model="status"');
});

it('dropdown (Alpine, single) supports wireModelModifier', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" wire-model="status" wire-model-modifier="live" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->toContain('wire:model.live="status"');
});

it('dropdown (Alpine, multi) emits wire:model inside the hidden-input <template>', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="statuses" :multiple="true" :searchable="true" wire-model="statuses" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // Multi-mode renders hidden inputs inside <template x-for> — wire:model
    // rides each iteration.
    expect($html)->toContain('wire:model="statuses"');
    expect($html)->toContain('<template x-for="entry in selectedLabels"');
});

it('dropdown (Alpine) without wireModel does not emit wire:model on the hidden input', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->not->toContain('wire:model');
});

// Native fallback path (no Alpine — neither searchable nor clearable, OR disabled)

it('dropdown (native fallback) emits wire:model on the <select>', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" wire-model="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // No searchable / clearable → native <select> path.
    expect($html)->toContain('wire:model="status"');
    expect($html)->toContain('<select');
});

it('dropdown (native fallback) supports wireModelModifier on the <select>', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" wire-model="status" wire-model-modifier="blur" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->toContain('wire:model.blur="status"');
});

it('dropdown (native fallback) without wireModel emits no wire:model on the <select>', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->not->toContain('wire:model');
});

// Defensive-escape regression (D75) — values flow through htmlspecialchars

it('dropdown HTML-escapes a `"` in the wireModel prop (Alpine path)', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" wire-model=\'foo"bar\' />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->toContain('wire:model="foo&quot;bar"');
});

it('dropdown HTML-escapes a `"` in the wireModel prop (native fallback path)', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" wire-model=\'foo"bar\' />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->toContain('wire:model="foo&quot;bar"');
});
