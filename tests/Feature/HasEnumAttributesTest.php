<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumAttributes;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\RenderableStatusEnum;

// In-memory Eloquent model under test. Schema created in the
// `beforeEach()` so each test runs in isolation.
class HasEnumAttributesTestModel extends Model
{
    use HasEnumAttributes;

    protected $table = 'enum_attributes_test_models';

    protected $guarded = [];

    public $timestamps = false;

    protected function enumAttributes(): array
    {
        return [
            'status' => StatusEnum::class,
            'rendered' => RenderableStatusEnum::class,
        ];
    }
}

beforeEach(function (): void {
    Schema::create('enum_attributes_test_models', function ($table): void {
        $table->id();
        $table->string('status')->nullable();
        $table->string('rendered')->nullable();
    });
});

afterEach(function (): void {
    Schema::dropIfExists('enum_attributes_test_models');
});

it('auto-registers AsEnum casts for declared enum attributes', function (): void {
    $model = HasEnumAttributesTestModel::create(['status' => 'active', 'rendered' => 'active']);
    $reloaded = HasEnumAttributesTestModel::find($model->id);

    expect($reloaded->status)->toBe(StatusEnum::Active);
    expect($reloaded->rendered)->toBe(RenderableStatusEnum::Active);
});

it('exposes _label / _value / _name accessors', function (): void {
    $model = HasEnumAttributesTestModel::create(['status' => 'active']);

    expect($model->status_label)->toBe('Active');
    expect($model->status_value)->toBe('active');
    expect($model->status_name)->toBe('Active');
});

it('exposes _color / _icon / _description accessors', function (): void {
    $model = HasEnumAttributesTestModel::create(['rendered' => 'active']);

    expect($model->rendered_color)->toBe('success');
    expect($model->rendered_icon)->toBe('check-circle');
    expect($model->rendered_description)->toBeNull();  // no #[Description] set
});

it('exposes _badge accessor returning HtmlString', function (): void {
    $model = HasEnumAttributesTestModel::create(['rendered' => 'active']);
    $badge = (string) $model->rendered_badge;

    expect($badge)->toContain('Active')
        ->toContain('role="status"');
});

it('exposes _meta accessor returning array', function (): void {
    $model = HasEnumAttributesTestModel::create(['status' => 'active']);

    expect($model->status_meta)->toBeArray();
});

it('exposes Is/In/Equals predicate methods via __call', function (): void {
    $model = HasEnumAttributesTestModel::create(['status' => 'active']);

    expect($model->statusIs(StatusEnum::Active))->toBeTrue();
    expect($model->statusIs(StatusEnum::Inactive))->toBeFalse();
    expect($model->statusIn([StatusEnum::Active, StatusEnum::Pending]))->toBeTrue();
    expect($model->statusEquals(StatusEnum::Active))->toBeTrue();
});

it('returns null / empty / false defaults when the attribute is null', function (): void {
    $model = HasEnumAttributesTestModel::create(['status' => null]);

    expect($model->status_label)->toBe('');
    expect($model->status_color)->toBeNull();
    expect($model->statusIs(StatusEnum::Active))->toBeFalse();
    expect($model->statusIn([StatusEnum::Active]))->toBeFalse();
});

it('falls through to parent __get / __call for non-enum keys', function (): void {
    $model = HasEnumAttributesTestModel::create(['status' => 'active']);

    // id is a real attribute on the model, not enum-derived.
    expect($model->id)->toBeInt();
});

it('respects consumer-declared casts when present', function (): void {
    // Subclass to override casts for `status` — trait should NOT re-register.
    $model = new class extends Model
    {
        use HasEnumAttributes;

        protected $table = 'enum_attributes_test_models';

        protected $guarded = [];

        public $timestamps = false;

        protected $casts = [
            'status' => 'string',  // consumer explicitly opts out of enum cast
        ];

        protected function enumAttributes(): array
        {
            return ['status' => StatusEnum::class];
        }
    };

    $model->status = 'active';

    // With the consumer's `string` cast, the attribute stays a string.
    expect($model->status)->toBe('active');
});
