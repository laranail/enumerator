<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// Feature coverage for the v0.3.0 PR-γ per-input :wireModel prop on
// <x-laranail-enumerator::radio> and <x-laranail-enumerator::checkboxes>.
//
// Background: PR-F (v0.2.0) routes generic attribute forwarding to the
// wrapping <fieldset> via $extraAttrs. That works for the <select>
// component (where wire:model on the <select> binds Livewire), but for
// radio + checkboxes Livewire 3 binds at the <input> level — placing
// wire:model on the <fieldset> works for radios (via Livewire's
// DOM-morph), fails for checkbox-array binding.
//
// PR-γ adds dedicated :wireModel + :wireModelModifier props that emit
// wire:model[.modifier]="..." on each <input> element. This is the
// correct shape for both single-radio binding and checkbox-array
// binding.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

// Radio

it('radio without wireModel does not emit per-input wire:model', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->not->toContain('wire:model');
});

it('radio with :wireModel emits wire:model on each <input>', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" wire-model="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // 3 cases in SimpleStatusEnum → 3 wire:model attrs
    expect(substr_count($html, 'wire:model="status"'))->toBe(3);
});

it('radio with :wireModel + :wireModelModifier emits wire:model.live', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" wire-model="status" wire-model-modifier="live" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect(substr_count($html, 'wire:model.live="status"'))->toBe(3);
    expect($html)->not->toContain('wire:model="status"');
});

it('radio supports complex modifiers like debounce.500ms', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" wire-model="status" wire-model-modifier="debounce.500ms" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect(substr_count($html, 'wire:model.debounce.500ms="status"'))->toBe(3);
});

// Checkboxes

it('checkboxes without wireModel does not emit per-input wire:model', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="permissions[]" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->not->toContain('wire:model');
});

it('checkboxes with :wireModel emits wire:model on each <input>', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="permissions[]" wire-model="permissions" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect(substr_count($html, 'wire:model="permissions"'))->toBe(3);
});

it('checkboxes with :wireModel + :wireModelModifier emits wire:model.live', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="permissions[]" wire-model="permissions" wire-model-modifier="live" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect(substr_count($html, 'wire:model.live="permissions"'))->toBe(3);
});

// Coexistence — the fieldset extraAttrs path from PR-F still works alongside per-input wireModel

it('per-input wireModel and fieldset data-* attributes coexist', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" wire-model="status" data-cy="status-radio" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // wire:model on each input (3 times)
    expect(substr_count($html, 'wire:model="status"'))->toBe(3);
    // data-cy on the fieldset (1 time)
    expect($html)->toContain('data-cy="status-radio"');
});
