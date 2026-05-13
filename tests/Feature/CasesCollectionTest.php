<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\AttributedStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

it('returns names/values/labels from a collection', function (): void {
    $col = SimpleStatusEnum::collect();
    expect($col->names())->toBe(['Active', 'Inactive', 'Pending']);
    // `values()` keeps parent Collection contract; use flatValues() for plain array.
    expect($col->flatValues())->toBe(['active', 'inactive', 'pending']);
    expect($col->labels())->toBe(['active' => 'Active', 'inactive' => 'Inactive', 'pending' => 'Pending']);
});

it('filters cases by name/value/meta', function (): void {
    expect(SimpleStatusEnum::collect()->onlyByName('Active', 'Banned')->names())->toBe(['Active']);
    expect(SimpleStatusEnum::collect()->exceptByName('Active')->names())->toBe(['Inactive', 'Pending']);
    expect(SimpleStatusEnum::collect()->whereValue('inactive')->names())->toBe(['Inactive']);
    expect(AttributedStatusEnum::collect()
        ->whereMeta('notify', true)->names())->toBe(['Active']);
});

it('keys by name/value', function (): void {
    $byName = SimpleStatusEnum::collect()->keyByName();
    expect($byName->keys()->all())->toBe(['Active', 'Inactive', 'Pending']);

    $byVal = SimpleStatusEnum::collect()->keyByValue();
    expect($byVal->keys()->all())->toBe(['active', 'inactive', 'pending']);
});

it('emits rich array shape', function (): void {
    $rich = SimpleStatusEnum::collect()->toRichArray();
    expect($rich)->toHaveCount(3);
    expect($rich[0])->toHaveKeys(['value', 'name', 'label']);
});

it('plucks via callable + key', function (): void {
    // `pluck()` keeps parent Collection contract; use flatPluck() for plain array.
    $picked = PriorityEnum::collect()->flatPluck(
        fn ($c) => $c->name,
        fn ($c) => $c->value,
    );
    expect($picked)->toBe([
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
        'critical' => 'Critical',
    ]);
});
