<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;

it('renders a badge with case label, color, and value', function (): void {
    $html = $this->blade('<x-laranail-enumerator::badge :case="$case" />', [
        'case' => StatusEnum::Active,
    ]);

    $html->assertSee('Active', false);
    $html->assertSee('data-value="active"', false);
    $html->assertSee('data-color="success"', false);
    $html->assertSee('aria-label="Active"', false);
    $html->assertSee('role="status"', false);
});

it('renders a badge as an anchor when href is provided with {value} token', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::badge :case="$case" href="/items?status={value}" />',
        ['case' => StatusEnum::Pending],
    );

    $html->assertSee('href="/items?status=pending"', false);
    $html->assertSee('<a', false);
});

it('renders a select with options', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::select :enum="$enum" name="status" />',
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('name="status"', false);
    $html->assertSee('value="active"', false);
    $html->assertSee('Active</option>', false);
    $html->assertSee('Inactive', false);
});

it('renders a select with nullable placeholder and selected value', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::select :enum="$enum" name="status" :selected="$selected" nullable placeholder="Choose…" />',
        ['enum' => StatusEnum::class, 'selected' => StatusEnum::Inactive],
    );

    $html->assertSee('<option value="">Choose…</option>', false);
    $html->assertSee('selected', false);
    $html->assertSee('value="inactive"', false);
});

it('renders a multi-select with array name and iterable selection', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::select :enum="$enum" name="statuses" multiple :selected="$selected" />',
        ['enum' => StatusEnum::class, 'selected' => [StatusEnum::Active, StatusEnum::Pending]],
    );

    $html->assertSee('name="statuses[]"', false);
    $html->assertSee('multiple', false);
});

it('renders a dropdown with label and description and data hooks', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" label-text="Status" description="Required" searchable clearable />',
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('<label', false);
    $html->assertSee('Status', false);
    $html->assertSee('Required', false);
    $html->assertSee('data-searchable="true"', false);
    $html->assertSee('data-clearable="true"', false);
});

it('renders a radio group with selected and legend', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::radio :enum="$enum" name="priority" :selected="$selected" legend="Priority" />',
        ['enum' => PriorityEnum::class, 'selected' => PriorityEnum::High],
    );

    $html->assertSee('<fieldset', false);
    $html->assertSee('<legend', false);
    $html->assertSee('Priority</legend>', false);
    $html->assertSee('type="radio"', false);
    $html->assertSee('value="high"', false);
    $html->assertSee('checked', false);
});

it('renders a checkbox group with iterable selection', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="picks" :selected="$selected" />',
        ['enum' => StatusEnum::class, 'selected' => [StatusEnum::Active, StatusEnum::Archived]],
    );

    $html->assertSee('type="checkbox"', false);
    $html->assertSee('name="picks[]"', false);
    $html->assertSee('checked', false);
});

it('renders a grid with data-columns', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::grid :enum="$enum" :columns="4" />',
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('data-columns="4"', false);
    $html->assertSee('Active', false);
});

it('renders a list', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::listing :enum="$enum" />',
        ['enum' => StatusEnum::class],
    );

    $html->assertSee('role="list"', false);
    $html->assertSee('Active', false);
    $html->assertSee('Pending', false);
});

it('renders the polymorphic element as an h2 with icon and label', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::element :case="$case" as="h2" show-icon />',
        ['case' => StatusEnum::Active],
    );

    $html->assertSee('<h2', false);
    $html->assertSee('Active', false);
    $html->assertSee('data-value="active"', false);
});

it('renders the polymorphic element as an anchor with href token', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::element :case="$case" as="a" href="/users?status={value}" />',
        ['case' => StatusEnum::Archived],
    );

    $html->assertSee('<a', false);
    $html->assertSee('href="/users?status=archived"', false);
});

it('renders the polymorphic element as a button with type', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::element :case="$case" as="button" type="submit">Apply</x-laranail-enumerator::element>',
        ['case' => StatusEnum::Active],
    );

    $html->assertSee('<button', false);
    $html->assertSee('type="submit"', false);
    $html->assertSee('Apply', false);
});

it('falls back to span when as is not on the safe list', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::element :case="$case" as="script" />',
        ['case' => StatusEnum::Active],
    );

    $html->assertSee('<span', false);
    $html->assertDontSee('<script', false);
});

it('honours per-call class overrides via the classes prop', function (): void {
    // The canonical class component accepts `:classes="..."` to fully override
    // the framework's default root class string.
    $html = $this->blade(
        '<x-laranail-enumerator::badge :case="$case" classes="custom-badge ring-2" />',
        ['case' => StatusEnum::Active],
    );

    $html->assertSee('custom-badge', false);
    $html->assertSee('ring-2', false);
});

it('switches CSS framework per-call', function (): void {
    $bootstrap = $this->blade(
        '<x-laranail-enumerator::badge :case="$case" framework="bootstrap" />',
        ['case' => StatusEnum::Active],
    );
    $bootstrap->assertSee('badge', false);
    $bootstrap->assertSee('text-bg-success', false);

    $tailwind = $this->blade(
        '<x-laranail-enumerator::badge :case="$case" framework="tailwind" />',
        ['case' => StatusEnum::Active],
    );
    $tailwind->assertSee('bg-success-50', false);
    $tailwind->assertSee('rounded-full', false);
});
