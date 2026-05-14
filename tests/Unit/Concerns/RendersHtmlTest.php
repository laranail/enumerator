<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\RenderableStatusEnum;

// RendersHtml — toHtml() output contract.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'bootstrap');
});

it('toHtml() returns an HtmlString', function (): void {
    $html = RenderableStatusEnum::Active->toHtml();
    expect($html)->toBeInstanceOf(HtmlString::class);
});

it('toHtml() includes the label text', function (): void {
    $html = (string) RenderableStatusEnum::Active->toHtml();
    expect($html)->toContain('Active');
});

it('toHtml() includes the resolved CSS class', function (): void {
    $html = (string) RenderableStatusEnum::Active->toHtml('bootstrap');
    expect($html)->toContain('badge bg-success');
});

it('toHtml() respects framework override', function (): void {
    $bootstrap = (string) RenderableStatusEnum::Active->toHtml('bootstrap');
    $daisyui = (string) RenderableStatusEnum::Active->toHtml('daisyui');

    expect($bootstrap)->toContain('bg-success');
    expect($daisyui)->toContain('badge-success');
    expect($bootstrap)->not->toEqual($daisyui);
});

it('toHtml() falls back to per-framework class formulas when no #[CssClass] is set', function (): void {
    // StatusEnum has #[Color] but NO #[CssClass]; so RendersHtml uses
    // its fallbackClasses() formula instead.
    $tw = (string) StatusEnum::Active->toHtml('tailwind');
    $da = (string) StatusEnum::Active->toHtml('daisyui');
    $bs = (string) StatusEnum::Active->toHtml('bootstrap');
    $bu = (string) StatusEnum::Active->toHtml('bulma');
    $plain = (string) StatusEnum::Active->toHtml('plain');

    expect($tw)->toContain('bg-success-100');
    expect($da)->toContain('badge-success');
    expect($bs)->toContain('bg-success');
    expect($bu)->toContain('is-success');
    expect($plain)->toContain('enumerator-badge');
    expect($plain)->toContain('enumerator-success');
});

it('toHtml() defaults to plain when config returns null', function (): void {
    config()->set('enumerator.css_framework', null);
    $out = (string) StatusEnum::Active->toHtml();
    expect($out)->toContain('enumerator-badge');
});

it('toHtml() omits the icon span when the case has no #[Icon]', function (): void {
    $out = (string) PureColorEnum::Red->toHtml();
    expect($out)->not->toContain('enumerator-icon');
});
