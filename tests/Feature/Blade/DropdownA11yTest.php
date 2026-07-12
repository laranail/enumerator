<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// PR-ο a11y audit: assert the WAI-ARIA combobox/listbox attributes
// emitted by the Alpine dropdown path. Many of these are emitted via
// Alpine's :attribute binding (rendered as the bare attribute name in
// the HTML), so the assertions look for the binding syntax rather
// than a resolved value.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

it('Alpine path emits aria-controls linking trigger → listbox', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->toContain('aria-controls="status-listbox"');
    expect($html)->toContain('id="status-listbox"');
});

it('Alpine path emits aria-activedescendant binding on the trigger', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // Bound dynamically by Alpine; the rendered HTML carries the
    // `:aria-activedescendant` attribute name (Alpine strips the `:`
    // at runtime).
    expect($html)->toContain(':aria-activedescendant=');
    expect($html)->toContain('status-opt-');
});

it('Alpine path emits aria-activedescendant on the search input too', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The search <input> mirrors the same wiring — both surfaces are
    // navigable with arrow keys.
    $count = substr_count($html, ':aria-activedescendant=');
    expect($count)->toBeGreaterThanOrEqual(2);

    // And aria-controls on the search input.
    $controlsCount = substr_count($html, 'aria-controls="status-listbox"');
    expect($controlsCount)->toBeGreaterThanOrEqual(2);
});

it('Alpine listbox <ul> carries id + role=listbox + aria-multiselectable in multi-mode', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)
        ->toContain('id="status-listbox"')
        ->toContain('role="listbox"')
        ->toContain('aria-multiselectable="true"');
});

it('Alpine option <li> carries :id binding so aria-activedescendant resolves', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The <li> :id binding is what aria-activedescendant on the
    // trigger points at. Alpine renders it as :id="..." in the
    // pre-engagement HTML.
    expect($html)->toContain(':id=');
    expect($html)->toContain('status-opt-');
});

// announceChanges opt-in live region

it('Alpine path does NOT emit the live region by default', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)->not->toContain('id="status-announce"');
    expect($html)->not->toContain('aria-live="polite"');
});

it('Alpine path emits the polite live region when announceChanges=true', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" :announce-changes="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)
        ->toContain('id="status-announce"')
        ->toContain('aria-live="polite"')
        ->toContain('aria-atomic="true"')
        ->toContain('class="enumerator-dropdown-sr-only"');
});

it('Alpine x-data carries announcement state when announceChanges=true', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :multiple="true" :searchable="true" :announce-changes="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The x-data state always has `announcement: ''` (so the binding
    // resolves even when the live region is absent), and the live
    // region's x-text reads it.
    expect($html)
        ->toContain('announcement:')
        ->toContain('x-text="announcement"');
});

// Native fallback path (no Alpine — disabled OR neither searchable nor clearable):
// the WAI-ARIA combobox pattern doesn't apply; the native <select> is
// itself an accessible widget. Just confirm the live region is NOT
// emitted on the native path (it has no Alpine state to drive it).

it('native fallback path does not emit the live region even when announceChanges=true', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :announce-changes="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // No searchable / clearable → native <select> path. The
    // @if ($alpineEnhanced) gate skips the wrapper that holds the
    // live region; the native <select> handles its own a11y.
    expect($html)->not->toContain('aria-live="polite"');
    expect($html)->toContain('<select');
});
