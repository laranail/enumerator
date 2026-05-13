<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\FlaggedPermissionEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\GroupedStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\RenderableStatusEnum;

// Blade components — exercise the renderable surface across components +
// framework variants to lift coverage on the eight component classes.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

// Badge

it('badge renders the label for a backed enum case', function (): void {
    $html = Blade::render('<x-laranail-enumerator::badge :case="$case" />', [
        'case' => RenderableStatusEnum::Active,
    ]);
    expect($html)->toContain('Active');
});

it('badge renders an href when one is provided', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::badge :case="$case" href="/foo" />',
        ['case' => RenderableStatusEnum::Active],
    );
    expect($html)->toContain('href');
    expect($html)->toContain('/foo');
});

// Select

it('select renders an <option> for every case', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::select :enum="$enum" name="status" />',
        ['enum' => StatusEnum::class],
    );
    foreach (StatusEnum::cases() as $case) {
        expect($html)->toContain($case->value);
    }
});

it('select marks the matching option as selected', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::select :enum="$enum" name="status" :selected="$selected" />',
        ['enum' => StatusEnum::class, 'selected' => StatusEnum::Pending],
    );
    expect($html)->toContain('selected');
});

it('select supports the multiple attribute', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::select :enum="$enum" name="status[]" :multiple="true" />',
        ['enum' => StatusEnum::class],
    );
    expect($html)->toContain('multiple');
});

it('select groups cases via groupsBy="groups" when the enum exposes groups()', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::select :enum="$enum" name="status" groupsBy="groups" />',
        ['enum' => GroupedStatusEnum::class],
    );
    expect($html)->toContain('optgroup');
});

// Radio

it('radio renders one input per case', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" />',
        ['enum' => StatusEnum::class],
    );
    expect(substr_count($html, 'type="radio"'))->toBe(4);
});

it('radio marks the checked input', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::radio :enum="$enum" name="status" :selected="$selected" />',
        ['enum' => StatusEnum::class, 'selected' => StatusEnum::Active],
    );
    expect($html)->toContain('checked');
});

// Checkboxes

it('checkboxes renders one input per Bitwise case', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="flags[]" />',
        ['enum' => FlaggedPermissionEnum::class],
    );
    expect(substr_count($html, 'type="checkbox"'))->toBe(4);
});

it('checkboxes marks the cases that are part of the selected mask', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::checkboxes :enum="$enum" name="flags[]" :selected="$selected" />',
        [
            'enum' => FlaggedPermissionEnum::class,
            'selected' => [FlaggedPermissionEnum::Read, FlaggedPermissionEnum::Write],
        ],
    );
    expect(substr_count($html, 'checked'))->toBeGreaterThanOrEqual(2);
});

// Grid

it('grid renders one cell per case', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::grid :enum="$enum" />',
        ['enum' => StatusEnum::class],
    );
    foreach (StatusEnum::cases() as $case) {
        expect($html)->toContain($case->name);
    }
});

// Listing

it('listing renders an item per case', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::listing :enum="$enum" />',
        ['enum' => StatusEnum::class],
    );
    foreach (StatusEnum::cases() as $case) {
        expect($html)->toContain($case->name);
    }
});

// Dropdown

it('dropdown renders a menu item per case', function (): void {
    $html = Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" />',
        ['enum' => StatusEnum::class],
    );
    foreach (StatusEnum::cases() as $case) {
        expect($html)->toContain($case->name);
    }
});
