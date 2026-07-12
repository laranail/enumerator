<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// Feature coverage for the v0.2.0 Livewire-aware Blade contract.
//
// The `<x-laranail-enumerator::select>`, `<x-laranail-enumerator::radio>`,
// and `<x-laranail-enumerator::checkboxes>` components forward any
// caller-supplied HTML attributes through to their outer element via
// `$attributes->except(<known props>)`. That makes wire:model and
// wire:model.live work out of the box (on the <select> for select,
// on the <fieldset> for radio / checkboxes — Livewire 3 morphs the
// binding down to each <input>).
//
// Laravel's ComponentAttributeBag::__toString() HTML-escapes attribute
// values, so the `{!! $extraAttrs !!}` emission in the base views is
// safe by construction — see tests/Unit/Blade/SelectComponentContractTest
// for the structural guard.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

// Select

it('forwards wire:model.live to the <select> element', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::select :enum="$enum" name="status" wire:model.live="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('wire:model.live="status"')
        ->toContain('<select');
});

it('forwards wire:model and arbitrary data-* attributes on the select', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::select :enum="$enum" name="status" wire:model="status" data-cy="status-input" aria-describedby="status-help" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('wire:model="status"')
        ->toContain('data-cy="status-input"')
        ->toContain('aria-describedby="status-help"');
});

// Radio

it('forwards wire:model to the radio <fieldset>', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" wire:model="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('wire:model="status"')
        ->toContain('<fieldset')
        ->toContain('role="radiogroup"');
});

it('forwards arbitrary data-* attributes on the radio fieldset', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" data-cy="status-radio" wire:model.live="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('data-cy="status-radio"')
        ->toContain('wire:model.live="status"');
});

// Checkboxes

it('forwards wire:model to the checkboxes <fieldset>', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="permissions" wire:model="permissions" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('wire:model="permissions"')
        ->toContain('<fieldset');
});

it('forwards data-* and aria-* on the checkboxes fieldset', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="permissions[]" data-cy="perms" aria-describedby="perms-help" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('data-cy="perms"')
        ->toContain('aria-describedby="perms-help"');
});

// Negative — known props are NOT leaked through extraAttrs

it('does not duplicate the name attribute via the attribute bag forwarding', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::select :enum="$enum" name="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The select tag should have exactly one name attribute.
    expect(substr_count($html, 'name="status"'))->toBe(1);
});
