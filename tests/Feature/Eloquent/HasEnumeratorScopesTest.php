<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Enumerator\Eloquent\HasEnumeratorScopes;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;

/**
 * Minimal Eloquent model fixture with an enum-cast column and the scopes trait.
 *
 * The `whereEnumMeta` scope reads `Model::getCasts()` and expects the
 * column value to be the bare enum class (Laravel's native enum cast form),
 * not an `AsEnum:Class` spec — so this fixture uses the bare form.
 */
class ScopedTestModel extends Model
{
    use HasEnumeratorScopes;

    protected $table = 'scoped_test_models';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['status' => StatusEnum::class];
    }
}

beforeEach(function (): void {
    Schema::create('scoped_test_models', function ($table): void {
        $table->id();
        $table->string('status');
    });

    foreach (['active', 'active', 'inactive', 'pending', 'archived'] as $status) {
        DB::table('scoped_test_models')->insert(['status' => $status]);
    }
});

afterEach(function (): void {
    Schema::dropIfExists('scoped_test_models');
});

it('whereEnum filters to a single case', function (): void {
    $count = ScopedTestModel::whereEnum('status', StatusEnum::Active)->count();
    expect($count)->toBe(2);
});

it('whereEnumNot excludes a single case', function (): void {
    $count = ScopedTestModel::whereEnumNot('status', StatusEnum::Active)->count();
    expect($count)->toBe(3);
});

it('whereEnumIn filters to multiple cases', function (): void {
    $count = ScopedTestModel::whereEnumIn('status', [StatusEnum::Active, StatusEnum::Pending])->count();
    expect($count)->toBe(3);
});

it('whereEnumNotIn excludes multiple cases', function (): void {
    $count = ScopedTestModel::whereEnumNotIn('status', [StatusEnum::Active, StatusEnum::Pending])->count();
    expect($count)->toBe(2);
});

it('whereEnumMeta returns no rows when no meta matches', function (): void {
    // StatusEnum has no #[Meta] declared, so no value will match.
    $count = ScopedTestModel::whereEnumMeta('status', 'unknown-key', 'unknown-value')->count();
    expect($count)->toBe(0);
});

it('whereEnumMeta returns matching rows when meta override is set', function (): void {
    AttributesCache::flush();
    config()->set('enumerator.overrides', [
        StatusEnum::class => [
            'Active' => ['meta' => ['priority' => 'high']],
        ],
    ]);

    // Sanity check the override is in effect.
    expect(StatusEnum::Active->meta('priority'))->toBe('high');

    $count = ScopedTestModel::whereEnumMeta('status', 'priority', 'high')->count();
    expect($count)->toBe(2);
});

it('whereEnumMeta returns no rows when the column has no enum cast', function (): void {
    // Switch to a model whose `status` column isn't cast to an enum.
    $rawModel = new class extends Model
    {
        use HasEnumeratorScopes;

        protected $table = 'scoped_test_models';

        public $timestamps = false;
    };

    $count = $rawModel::query()->whereEnumMeta('status', 'priority', 'high')->count();
    expect($count)->toBe(0);
});

it('cast retrieves the case after persistence', function (): void {
    $model = ScopedTestModel::create(['status' => StatusEnum::Pending]);
    $reloaded = ScopedTestModel::find($model->id);
    expect($reloaded->status)->toBe(StatusEnum::Pending);
});
