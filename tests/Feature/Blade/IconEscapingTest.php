<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;

// Regression tests for the F1 audit finding (2026-05-16): the `#[Icon]`
// attribute value used to be rendered raw via `{!! !!}` inside the
// badge and element base views. A tenant-supplied override containing
// HTML could become a stored XSS. These tests pin the escaped form.

it('badge component HTML-escapes the icon attribute against XSS payloads', function (): void {
    config()->set('enumerator.css_framework', 'plain');
    config()->set('enumerator.overrides', [
        StatusEnum::class => [
            'Active' => ['icon' => '<script>alert(1)</script>'],
        ],
    ]);
    AttributesCache::flush();

    $html = Blade::render(
        '<x-laranail-enumerator::badge :case="$case" />',
        ['case' => StatusEnum::Active],
    );

    expect($html)->not->toContain('<script>alert(1)</script>');
    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
});

it('badge component HTML-escapes `&` and `"` in the icon attribute', function (): void {
    config()->set('enumerator.css_framework', 'plain');
    config()->set('enumerator.overrides', [
        StatusEnum::class => [
            'Active' => ['icon' => 'a&b"c'],
        ],
    ]);
    AttributesCache::flush();

    $html = Blade::render(
        '<x-laranail-enumerator::badge :case="$case" />',
        ['case' => StatusEnum::Active],
    );

    expect($html)->toContain('a&amp;b&quot;c');
    expect($html)->not->toContain('a&b"c');
});

it('badge component passes plain text icon names through unchanged', function (): void {
    // Plain class names like "check-circle" must continue to work — they
    // should appear in the output verbatim (no entities to encode).
    AttributesCache::flush();

    $html = Blade::render(
        '<x-laranail-enumerator::badge :case="$case" />',
        ['case' => StatusEnum::Active],
    );

    expect($html)->toContain('check-circle');
});
