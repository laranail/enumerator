<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// PR-δ — multi-select Alpine listbox.
//
// Pre-PR-δ (PR-G in v0.2.0): `<x-...::dropdown :multiple="true">` fell
// through to the native `<select multiple>` because the Alpine path
// only handled single-selection.
//
// Post-PR-δ: the Alpine path handles multiple=true with a real
// listbox — pill UI in the trigger, hidden inputs (one per selected
// value via `<template x-for>`), `aria-multiselectable="true"` on
// the `<ul role="listbox">`, commitSelection toggles instead of
// replacing, and the panel stays open for consecutive selections.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

it('multi-mode emits the multi-aware x-data state', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)
        ->toContain('multiple: true')
        ->toContain('selectedValues: []')
        ->toContain('selectedLabels: []')
        ->toContain('data-multiple="true"');
});

it('multi-mode emits aria-multiselectable on the listbox', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->toContain('aria-multiselectable="true"');
});

it('multi-mode renders the pills container in the trigger button', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)
        ->toContain('enumerator-dropdown-pills')
        ->toContain('enumerator-dropdown-pill')
        ->toContain('enumerator-dropdown-pill-remove')
        ->toContain('removeValue(entry.value)');
});

it('multi-mode emits hidden inputs via <template x-for> over selectedLabels', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The hidden input lives inside a <template x-for>, with name
    // pre-resolved by Blade and value bound via Alpine.
    expect($html)
        ->toContain('<template x-for="entry in selectedLabels"')
        ->toContain('<input type="hidden" name="permissions[]" :value="entry.value">');
});

it('multi-mode hydrates selectedValues from :selected iterable', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :searchable="true" :selected="$selected" />',
        [
            'enum' => SimpleStatusEnum::class,
            'selected' => [SimpleStatusEnum::Active, SimpleStatusEnum::Pending],
        ],
    );

    expect($html)
        ->toContain('selectedValues: [&quot;active&quot;,&quot;pending&quot;]');
});

it('multi-mode commitSelection toggles values (does not close panel)', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The multi branch of commitSelection pushes/splices on the
    // array and does NOT mutate `this.open`. The branch is in the
    // emitted x-data JS.
    expect($html)
        ->toContain('this.selectedValues.splice(idx, 1)')
        ->toContain('this.selectedValues.push(v)');
});

it('single-mode (no multiple) still renders the single-select Alpine listbox', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The pill template + multi-mode branches are present in the
    // emitted HTML (Alpine gates them via x-if at runtime), so the
    // assertion is on the runtime-gating attributes / values, not on
    // the literal markup. data-multiple flips, aria-multiselectable
    // is omitted in single mode.
    expect($html)
        ->toContain('multiple: false')
        ->toContain('data-multiple="false"')
        ->not->toContain('aria-multiselectable="true"');
});

it('multi-mode supports the clear button to drop all selections', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :clearable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)
        ->toContain('enumerator-dropdown-clear')
        ->toContain('clearSelection()')
        // hasSelection() returns true when array length > 0 or scalar non-empty
        ->toContain('hasSelection()');
});

it('falls through to the native <select multiple> when disabled=true', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :searchable="true" :disabled="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)
        ->toContain('<select')
        ->toContain('multiple')
        ->toContain('disabled')
        ->not->toContain('x-data');
});

it('isSelected() resolves correctly in multi-mode (string vs int values)', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="permissions[]" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // isSelected() uses String() comparison both sides to handle
    // int-backed and string-backed enums uniformly.
    expect($html)
        ->toContain('this.selectedValues.some(v => String(v) === String(opt.value))');
});
