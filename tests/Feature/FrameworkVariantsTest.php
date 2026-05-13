<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;

it('renders each framework badge variant with that framework class set', function (string $framework, string $expectedClass): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.badge :case=\"\$case\" />",
        ['case' => StatusEnum::Active],
    );

    $html->assertSee($expectedClass, false);
})->with([
    'plain' => ['plain', 'enumerator-badge'],
    'tailwind' => ['tailwind', 'bg-success-50'],
    'daisyui' => ['daisyui', 'badge-success'],
    'bootstrap' => ['bootstrap', 'bg-success'],
    'bulma' => ['bulma', 'tag is-success'],
]);

it('renders each framework select variant', function (string $framework): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.select :enum=\"\$enum\" name=\"status\" />",
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('<select', false);
    $html->assertSee('name="status"', false);
    $html->assertSee('value="active"', false);
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);

it('renders each framework dropdown variant with label + groupings', function (string $framework): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.dropdown :enum=\"\$enum\" name=\"status\" label-text=\"Status\" groups-by=\"attribute:color\" />",
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('<label', false);
    $html->assertSee('Status', false);
    $html->assertSee('<optgroup', false);
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);

it('renders each framework radio variant', function (string $framework): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.radio :enum=\"\$enum\" name=\"status\" />",
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('type="radio"', false);
    $html->assertSee('<fieldset', false);
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);

it('renders each framework checkboxes variant', function (string $framework): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.checkboxes :enum=\"\$enum\" name=\"picks\" />",
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('type="checkbox"', false);
    $html->assertSee('name="picks[]"', false);
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);

it('renders each framework grid variant', function (string $framework): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.grid :enum=\"\$enum\" :columns=\"2\" />",
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('data-columns="2"', false);
    $html->assertSee('Active', false);
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);

it('renders each framework listing variant', function (string $framework): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.listing :enum=\"\$enum\" />",
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('role="list"', false);
    $html->assertSee('Active', false);
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);

it('renders each framework element variant as a heading', function (string $framework): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.element :case=\"\$case\" as=\"h2\" />",
        ['case' => StatusEnum::Active],
    );

    $html->assertSee('<h2', false);
    $html->assertSee('Active', false);
    $html->assertSee('data-value="active"', false);
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);
