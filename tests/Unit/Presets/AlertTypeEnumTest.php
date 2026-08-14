<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Presets\Enums\AlertTypeEnum;

// AlertTypeEnum — the presentational counterpart to SeverityEnum.
//
// Migrated from a class-constant enum in a consuming app, where a public
// `Request::alert($message, string $type)` macro fed arbitrary strings into
// `new AlertType($type)`. Its `icon()` was a match with no default arm, so any
// value outside the seven constants threw UnhandledMatchError from a helper
// whose whole job was showing the user a message. The native enum removes that
// class of failure rather than patching it: there is no way to hold a value
// that is not a case.

it('carries the seven alert types', function (): void {
    expect(AlertTypeEnum::cases())->toHaveCount(7);
});

it('keeps the css-facing backing values', function (): void {
    // These are the strings a front end already writes into a class name, so
    // ->value stays a drop-in for the class-constant original.
    expect(AlertTypeEnum::values())->toBe([
        'default', 'primary', 'success', 'info', 'warning', 'error', 'mono',
    ]);
});

it('is an Enumerator', function (): void {
    expect(AlertTypeEnum::Success)->toBeInstanceOf(Enumerator::class);
});

it('answers icon() for every case', function (AlertTypeEnum $case): void {
    // The original threw for anything outside its match arms. Every case
    // answering is the property that replaces the missing default arm.
    expect($case->icon())->toBeString()->not->toBeEmpty();
})->with(AlertTypeEnum::cases());

it('answers color() for every case', function (AlertTypeEnum $case): void {
    expect($case->color())->toBeString()->not->toBeEmpty();
})->with(AlertTypeEnum::cases());

it('answers label() for every case', function (AlertTypeEnum $case): void {
    expect($case->label())->toBeString()->not->toBeEmpty();
})->with(AlertTypeEnum::cases());

it('maps color() to the package palette, not to the backing value', function (): void {
    // The two deliberately differ. 'default' and 'mono' are CSS tokens with no
    // place in a shared palette; 'secondary' and 'ghost' are what the other
    // presets use for the same roles.
    expect(AlertTypeEnum::Default->color())->toBe('secondary');
    expect(AlertTypeEnum::Mono->color())->toBe('ghost');
    expect(AlertTypeEnum::Error->color())->toBe('danger');

    expect(AlertTypeEnum::Default->value)->toBe('default');
    expect(AlertTypeEnum::Mono->value)->toBe('mono');
    expect(AlertTypeEnum::Error->value)->toBe('error');
});

it('orders from least to most urgent', function (): void {
    expect(AlertTypeEnum::sortedByOrder()->flatValues())
        ->toBe(['default', 'primary', 'success', 'info', 'warning', 'error', 'mono']);
});

it('groups the cases by intent', function (): void {
    expect(AlertTypeEnum::Success->inGroup('positive'))->toBeTrue();
    expect(AlertTypeEnum::Error->inGroup('negative'))->toBeTrue();
    expect(AlertTypeEnum::Warning->inGroup('attention'))->toBeTrue();
    expect(AlertTypeEnum::Info->inGroup('neutral'))->toBeTrue();

    expect(AlertTypeEnum::Success->inGroup('negative'))->toBeFalse();
});

it('rejects a value that is not a case', function (): void {
    // The migration's whole point: the macro that used to accept any string
    // now cannot construct an invalid instance at all.
    expect(AlertTypeEnum::tryFrom('not-a-type'))->toBeNull();
});

it('builds select options', function (): void {
    $options = AlertTypeEnum::options();

    expect($options)->toBeArray();
    expect($options)->toHaveCount(7);
});
