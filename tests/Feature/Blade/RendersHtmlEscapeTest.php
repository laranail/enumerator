<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\EscapeRegressionEnum;

// Regression coverage for RendersHtml::toHtml() escape behaviour.
//
// The path: IsTranslatable::label() returns a plain string (translation
// or #[Label] attribute), and RendersHtml::toHtml() wraps it in an
// HtmlString through `sprintf(..., e($label))`. Laravel's e() helper
// calls htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false) — the final
// `false` is the $double_encode flag.
//
// These tests pin the behaviour: special characters are HTML-escaped
// exactly once. A regression to `double_encode=true` (or any change that
// pre-escapes the label inside IsTranslatable) would break the
// `pre-encoded label stays single-encoded` test.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

it('escapes HTML tags in the label exactly once', function (): void {
    $html = (string) EscapeRegressionEnum::PlainHtml->toHtml();

    expect($html)
        ->toContain('&lt;b&gt;bold&lt;/b&gt;')
        ->not->toContain('<b>bold</b>')
        ->not->toContain('&amp;lt;'); // would indicate double-encoding
});

it('escapes ampersand in the label exactly once', function (): void {
    $html = (string) EscapeRegressionEnum::Ampersand->toHtml();

    expect($html)
        ->toContain('A &amp; B')
        ->not->toContain('A &amp;amp; B'); // double-encode trap
});

it('escapes double quotes in the label exactly once', function (): void {
    $html = (string) EscapeRegressionEnum::Quoted->toHtml();

    expect($html)
        ->toContain('She said &quot;hi&quot;')
        ->not->toContain('She said &amp;quot;hi&amp;quot;');
});

it('does NOT double-encode an already-encoded label', function (): void {
    $html = (string) EscapeRegressionEnum::PreEncoded->toHtml();

    // The label literal is `&lt;already-encoded&gt;`. With
    // double_encode=false, htmlspecialchars leaves existing entities
    // alone. With double_encode=true it would produce `&amp;lt;...`.
    expect($html)
        ->toContain('&lt;already-encoded&gt;')
        ->not->toContain('&amp;lt;already-encoded');
});
