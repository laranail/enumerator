<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

/*
|--------------------------------------------------------------------------
| Per-framework CSS class verification
|--------------------------------------------------------------------------
|
| For each component × framework, asserts that the CSS class strings from
| `config('enumerator.css_classes.{framework}.{component}.{element}')` land
| in the rendered HTML. Catches regressions where:
|
|   - the config map drifts from the views,
|   - the framework variant forgets to forward `framework=`,
|   - the `resolveClasses()` lookup falls through to plain unintentionally,
|   - the `{color}` token interpolation breaks for bg-{color} / badge-{color}.
|
| Plus a few cross-cutting tests for overrides, unknown frameworks, and
| attribute passthrough.
|
*/

// ---- BADGE -----------------------------------------------------------------

it('renders the badge with framework-specific classes', function (string $framework, array $expected): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.badge :case=\"\$case\" />",
        ['case' => StatusEnum::Active],
    );

    foreach ($expected as $needle) {
        $html->assertSee($needle, false);
    }
})->with([
    'plain' => ['plain',     ['enumerator-badge']],
    'tailwind' => ['tailwind',  ['inline-flex', 'rounded-full', 'bg-success-50', 'text-success-700', 'ring-1', 'ring-inset']],
    'daisyui' => ['daisyui',   ['badge', 'badge-success']],
    'bootstrap' => ['bootstrap', ['badge', 'text-bg-success', 'rounded-pill']],
    'bulma' => ['bulma',     ['tag', 'is-success', 'is-rounded']],
]);

it('interpolates the {color} token with the case color', function (): void {
    // PriorityEnum::Critical has color "danger".
    $bootstrap = $this->blade(
        '<x-laranail-enumerator::bootstrap.badge :case="$case" />',
        ['case' => PriorityEnum::Critical],
    );
    $bootstrap->assertSee('text-bg-danger', false);

    $daisyui = $this->blade(
        '<x-laranail-enumerator::daisyui.badge :case="$case" />',
        ['case' => PriorityEnum::Critical],
    );
    $daisyui->assertSee('badge-danger', false);

    $bulma = $this->blade(
        '<x-laranail-enumerator::bulma.badge :case="$case" />',
        ['case' => PriorityEnum::Critical],
    );
    $bulma->assertSee('is-danger', false);
});

it('falls back to the framework default color when the case has no color attribute', function (): void {
    // SimpleStatusEnum::Pending has no #[Color]. Each framework uses its own default fallback:
    //   plain → 'default', tailwind → 'gray', daisyui → 'neutral',
    //   bootstrap → 'secondary', bulma → 'info'.
    $bootstrap = $this->blade(
        '<x-laranail-enumerator::bootstrap.badge :case="$case" />',
        ['case' => SimpleStatusEnum::Pending],
    );
    $bootstrap->assertSee('text-bg-secondary', false);

    $tailwind = $this->blade(
        '<x-laranail-enumerator::tailwind.badge :case="$case" />',
        ['case' => SimpleStatusEnum::Pending],
    );
    $tailwind->assertSee('bg-gray-50', false);
});

// ---- SELECT ----------------------------------------------------------------

it('renders the select with framework-specific classes', function (string $framework, array $expected): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.select :enum=\"\$enum\" name=\"status\" />",
        ['enum' => StatusEnum::class],
    );

    foreach ($expected as $needle) {
        $html->assertSee($needle, false);
    }
})->with([
    'plain' => ['plain',     ['enumerator-select']],
    'tailwind' => ['tailwind',  ['block', 'w-full', 'rounded-md', 'ring-gray-300', 'focus:ring-indigo-600']],
    'daisyui' => ['daisyui',   ['select', 'select-bordered', 'w-full']],
    'bootstrap' => ['bootstrap', ['form-select']],
    'bulma' => ['bulma',     ['select', 'is-fullwidth']],
]);

// ---- DROPDOWN --------------------------------------------------------------

it('renders the dropdown wrapper, label, and description with framework classes', function (string $framework, array $expected): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.dropdown :enum=\"\$enum\" name=\"status\" label-text=\"Status\" description=\"Required\" />",
        ['enum' => StatusEnum::class],
    );

    foreach ($expected as $needle) {
        $html->assertSee($needle, false);
    }
})->with([
    'plain' => ['plain',     ['enumerator-dropdown', 'enumerator-dropdown-label', 'enumerator-dropdown-description']],
    'tailwind' => ['tailwind',  ['space-y-1', 'text-sm', 'font-medium', 'leading-6', 'text-gray-500']],
    'daisyui' => ['daisyui',   ['form-control', 'label-text', 'label-text-alt']],
    'bootstrap' => ['bootstrap', ['mb-3', 'form-label', 'fw-medium', 'form-text']],
    'bulma' => ['bulma',     ['field', 'label', 'help']],
]);

// ---- RADIO -----------------------------------------------------------------

it('renders the radio group with framework-specific root/item/input/label classes', function (string $framework, array $expected): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.radio :enum=\"\$enum\" name=\"status\" legend=\"Choose\" />",
        ['enum' => StatusEnum::class],
    );

    foreach ($expected as $needle) {
        $html->assertSee($needle, false);
    }
})->with([
    'plain' => ['plain',     ['enumerator-radio-group', 'enumerator-radio-item', 'enumerator-radio-label', 'enumerator-radio-legend']],
    'tailwind' => ['tailwind',  ['space-y-3', 'flex items-center', 'text-indigo-600', 'size-4']],
    'daisyui' => ['daisyui',   ['form-control', 'radio radio-primary', 'label cursor-pointer']],
    'bootstrap' => ['bootstrap', ['d-flex flex-column', 'form-check', 'form-check-input', 'form-check-label']],
    'bulma' => ['bulma',     ['is-grouped', 'control', 'radio']],
]);

// ---- CHECKBOXES ------------------------------------------------------------

it('renders the checkbox group with framework-specific classes', function (string $framework, array $expected): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.checkboxes :enum=\"\$enum\" name=\"picks\" />",
        ['enum' => StatusEnum::class],
    );

    foreach ($expected as $needle) {
        $html->assertSee($needle, false);
    }
})->with([
    'plain' => ['plain',     ['enumerator-checkbox-group', 'enumerator-checkbox-item', 'enumerator-checkbox-label']],
    'tailwind' => ['tailwind',  ['space-y-3', 'rounded', 'text-indigo-600', 'size-4']],
    'daisyui' => ['daisyui',   ['form-control', 'checkbox checkbox-primary']],
    'bootstrap' => ['bootstrap', ['d-flex flex-column', 'form-check', 'form-check-input']],
    'bulma' => ['bulma',     ['is-grouped', 'control', 'checkbox']],
]);

// ---- GRID ------------------------------------------------------------------

it('renders the grid with framework-specific root/item/label classes', function (string $framework, array $expected): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.grid :enum=\"\$enum\" :columns=\"3\" />",
        ['enum' => StatusEnum::class],
    );

    foreach ($expected as $needle) {
        $html->assertSee($needle, false);
    }
})->with([
    'plain' => ['plain',     ['enumerator-grid', 'enumerator-grid-item', 'enumerator-grid-label']],
    'tailwind' => ['tailwind',  ['grid gap-4', 'ring-1', 'ring-gray-200', 'rounded-lg', 'font-semibold']],
    'daisyui' => ['daisyui',   ['grid gap-4', 'card card-bordered', 'card-title']],
    'bootstrap' => ['bootstrap', ['row', 'g-3', 'card', 'card-title h6']],
    'bulma' => ['bulma',     ['columns is-multiline', 'is-variable', 'column is-one-third', 'title is-6']],
]);

// ---- LISTING ---------------------------------------------------------------

it('renders the listing with framework-specific classes', function (string $framework, array $expected): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.listing :enum=\"\$enum\" />",
        ['enum' => StatusEnum::class],
    );

    foreach ($expected as $needle) {
        $html->assertSee($needle, false);
    }
})->with([
    'plain' => ['plain',     ['enumerator-list', 'enumerator-list-item']],
    'tailwind' => ['tailwind',  ['divide-y', 'divide-gray-200', 'py-2']],
    'daisyui' => ['daisyui',   ['menu', 'bg-base-100', 'rounded-box', 'menu-item']],
    'bootstrap' => ['bootstrap', ['list-group', 'list-group-flush', 'list-group-item']],
    'bulma' => ['bulma',     ['panel', 'panel-block']],
]);

// ---- ELEMENT ---------------------------------------------------------------

it('renders the polymorphic element with framework-specific root classes', function (string $framework, array $expected): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.element :case=\"\$case\" as=\"a\" href=\"/x\" />",
        ['case' => StatusEnum::Active],
    );

    foreach ($expected as $needle) {
        $html->assertSee($needle, false);
    }
})->with([
    'plain' => ['plain',     ['enumerator-element']],
    'tailwind' => ['tailwind',  ['inline-flex', 'items-center', 'gap-x-1.5']],
    'daisyui' => ['daisyui',   ['inline-flex', 'items-center', 'gap-2']],
    'bootstrap' => ['bootstrap', ['d-inline-flex', 'align-items-center', 'gap-2']],
    'bulma' => ['bulma',     ['is-inline-flex', 'is-align-items-center']],
]);

// ---- CROSS-CUTTING ---------------------------------------------------------

it('honors the per-call framework override on the canonical component', function (): void {
    // The canonical `<x-...::badge>` accepts framework=, switching variant.
    $html = $this->blade(
        '<x-laranail-enumerator::badge :case="$case" framework="bulma" />',
        ['case' => StatusEnum::Active],
    );

    $html->assertSee('tag', false);
    $html->assertSee('is-success', false);
    $html->assertSee('is-rounded', false);
});

it('merges per-call class="..." on top of framework classes', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::tailwind.badge :case="$case" class="ring-2 ring-offset-2 custom-extra" />',
        ['case' => StatusEnum::Active],
    );

    // Both the framework's class string and the consumer's overrides.
    $html->assertSee('inline-flex', false);
    $html->assertSee('ring-2', false);
    $html->assertSee('ring-offset-2', false);
    $html->assertSee('custom-extra', false);
});

it('frameworks do not bleed CSS class strings between each other', function (): void {
    $bootstrap = (string) $this->blade(
        '<x-laranail-enumerator::bootstrap.badge :case="$case" />',
        ['case' => StatusEnum::Active],
    );
    $tailwind = (string) $this->blade(
        '<x-laranail-enumerator::tailwind.badge :case="$case" />',
        ['case' => StatusEnum::Active],
    );

    expect($bootstrap)
        ->toContain('text-bg-success')
        ->not->toContain('bg-success-50');

    expect($tailwind)
        ->toContain('bg-success-50')
        ->not->toContain('badge-success');
});

it('falls back to plain when an unknown framework is requested', function (): void {
    // The class component's frameworkView() coerces unknown frameworks to 'plain'.
    $html = $this->blade(
        '<x-laranail-enumerator::badge :case="$case" framework="unknown-framework" />',
        ['case' => StatusEnum::Active],
    );

    $html->assertSee('enumerator-badge', false);
});

it('forwards aria/data/id attributes through framework variants', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::bootstrap.select :enum="$enum" name="status" id="my-select" data-testid="status-select" aria-describedby="hint" />',
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('id="my-select"', false);
    $html->assertSee('data-testid="status-select"', false);
    $html->assertSee('aria-describedby="hint"', false);
    // Framework classes still applied
    $html->assertSee('form-select', false);
});

it('emits identical semantic structure across all frameworks', function (string $framework): void {
    $html = (string) $this->blade(
        "<x-laranail-enumerator::{$framework}.radio :enum=\"\$enum\" name=\"status\" />",
        ['enum' => StatusEnum::class],
    );

    // Same semantic role + form structure regardless of framework.
    expect($html)
        ->toContain('<fieldset')
        ->toContain('role="radiogroup"')
        ->toContain('type="radio"')
        ->toContain('name="status"');
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);

it('respects disabled and required attributes across frameworks', function (string $framework): void {
    $html = $this->blade(
        "<x-laranail-enumerator::{$framework}.select :enum=\"\$enum\" name=\"status\" disabled required />",
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('disabled', false);
    $html->assertSee('required', false);
})->with(['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma']);
