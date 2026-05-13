<?php

declare(strict_types=1);

use Illuminate\Support\HtmlString;
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
