<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// Feature coverage for the v0.2.0 Alpine-enhanced dropdown.
//
// The base dropdown view emits TWO different DOM shapes depending on
// the props:
//
//   1. Default (searchable=false, clearable=false): a native <select>
//      with data-searchable / data-clearable hooks for third-party JS
//      libraries (Tom Select, Choices.js, etc.). Preserves v0.1.0
//      behaviour.
//   2. Enhanced (searchable=true OR clearable=true): an Alpine
//      combobox/listbox with hidden input for form value, a
//      filter input, keyboard navigation (arrows/Enter/Esc), and a
//      clear button. Requires <x-laranail-enumerator::alpine-loader />
//      in the page (documented in docs/tools/alpine-loader.md and
//      docs/tools/blade-components.md).
//
// The enhanced path is excluded when multiple=true or disabled=true —
// those fall through to the native select.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

// Default (native select) path

it('defaults to a native <select> when neither searchable nor clearable is set', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('<select')
        ->not->toContain('x-data')
        ->not->toContain('enumerator-dropdown-combobox');
});

// Enhanced (Alpine) path — searchable

it('renders the Alpine combobox when searchable=true', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('x-data')
        ->toContain('enumerator-dropdown-combobox')
        ->toContain('data-enhancement="alpine"')
        ->toContain('aria-haspopup="listbox"')
        ->toContain('role="listbox"')
        ->toContain('<input type="hidden" name="status"')
        ->toContain('<input')                         // the search filter input
        ->toContain('x-ref="filter"')
        ->toContain('autocomplete="off"');
});

it('encloses option data as escaped JSON inside the x-data attribute', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // Inner JSON double quotes must be HTML-escaped (&quot;) so the
    // x-data="..." attribute boundary stays intact.
    expect((string) $html)
        ->toContain('&quot;value&quot;:&quot;active&quot;')
        ->toContain('&quot;label&quot;:&quot;Active&quot;')
        ->not->toContain('x-data="{ "value"');     // would mean unescaped JSON broke the attribute
});

it('emits keyboard-nav handlers on the trigger and the filter input', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('@keydown.arrow-down.prevent="moveDown()"')
        ->toContain('@keydown.arrow-up.prevent="moveUp()"')
        ->toContain('@keydown.enter.prevent="commitActive()"')
        ->toContain('@keydown.escape.prevent="open = false; filter = \'\'"');
});

// Enhanced (Alpine) path — clearable

it('renders the clear button when clearable=true', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :clearable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('x-data')
        ->toContain('enumerator-dropdown-clear')
        ->toContain('aria-label="Clear selection"')
        ->toContain('@click.stop="clearSelection()"');
});

it('omits the clear button when clearable=false', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)->not->toContain('enumerator-dropdown-clear');
});

// Selected-value hydration

it('hydrates the selectedValue into the Alpine x-data when :selected is provided', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :selected="$selected" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class, 'selected' => SimpleStatusEnum::Active],
    );

    expect((string) $html)->toContain('selectedValue: &quot;active&quot;');
});

// Multi-mode — Alpine path now handles multiple=true (post-PR-δ)

it('multiple=true stays in the Alpine path (post-PR-δ)', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status[]" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('x-data')
        ->toContain('multiple: true')
        ->toContain('data-multiple="true"')
        ->toContain('aria-multiselectable="true"')
        ->toContain('enumerator-dropdown-pills')
        ->not->toContain('<select');
});

// Fallthrough — disabled

it('falls through to the native <select> when disabled=true', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :disabled="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('<select')
        ->not->toContain('x-data');
});

// Form submission contract — hidden input carries the value

it('emits a hidden input with the form field name in the Alpine path', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="user_status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('<input type="hidden" name="user_status"')
        ->toContain(':value="selectedValue"');
});

// Empty-state message in the Alpine listbox

it('includes the empty-state list item', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect((string) $html)
        ->toContain('No matches.')
        ->toContain('enumerator-dropdown-empty');
});
