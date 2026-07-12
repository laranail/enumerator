<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Enumerator\Eloquent\EnumeratorStateHistory;
use Simtabi\Laranail\Enumerator\Eloquent\HasEnumeratorStateMachine;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidTransitionException;
use Simtabi\Laranail\Enumerator\Presets\Enums\PublicationStatusEnum;

class StateMachineTestArticle extends Model
{
    use HasEnumeratorStateMachine;

    protected $table = 'state_machine_articles';

    protected $guarded = [];

    protected array $stateMachines = ['status'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['status' => PublicationStatusEnum::class];
    }
}

beforeEach(function (): void {
    config()->set('enumerator.state_machine.record_history', true);
    config()->set('enumerator.state_machine.enforce_initial_state', true);
    config()->set('enumerator.state_machine.table_name', 'enumerator_state_history');

    Schema::create('state_machine_articles', function ($table): void {
        $table->id();
        $table->string('status');
    });

    // enumerator_state_history is provided by the package migration (auto-
    // loaded by TestCase::defineDatabaseMigrations).
    DB::table('enumerator_state_history')->truncate();
});

afterEach(function (): void {
    Schema::dropIfExists('state_machine_articles');
});

// HasEnumeratorStateMachine — creating / updating hook chain

it('rejects creation when the initial state is not in initialStates()', function (): void {
    StateMachineTestArticle::create(['status' => PublicationStatusEnum::Published]);
})->throws(InvalidTransitionException::class);

it('allows creation when the initial state is in initialStates()', function (): void {
    $article = StateMachineTestArticle::create(['status' => PublicationStatusEnum::Draft]);
    expect($article->status)->toBe(PublicationStatusEnum::Draft);
});

it('rejects an updating transition that is not allowed', function (): void {
    $article = StateMachineTestArticle::create(['status' => PublicationStatusEnum::Draft]);
    $article->status = PublicationStatusEnum::Published;
    $article->save();
})->throws(InvalidTransitionException::class);

it('permits an updating transition that is allowed', function (): void {
    $article = StateMachineTestArticle::create(['status' => PublicationStatusEnum::Draft]);
    $article->status = PublicationStatusEnum::Pending;
    $article->save();
    expect($article->fresh()->status)->toBe(PublicationStatusEnum::Pending);
});

it('records a row in enumerator_state_history on creation', function (): void {
    StateMachineTestArticle::create(['status' => PublicationStatusEnum::Draft]);
    expect(DB::table('enumerator_state_history')->count())->toBe(1);

    $row = DB::table('enumerator_state_history')->first();
    expect($row->field)->toBe('status');
    expect($row->from)->toBeNull();
    expect($row->to)->toBe('draft');
});

it('records a row on each allowed update', function (): void {
    $article = StateMachineTestArticle::create(['status' => PublicationStatusEnum::Draft]);
    $article->status = PublicationStatusEnum::Pending;
    $article->save();
    $article->status = PublicationStatusEnum::Published;
    $article->save();

    expect(DB::table('enumerator_state_history')->count())->toBe(3);
    $last = DB::table('enumerator_state_history')->latest('id')->first();
    expect($last->from)->toBe('pending');
    expect($last->to)->toBe('published');
});

it('skips history recording when record_history config is false', function (): void {
    config()->set('enumerator.state_machine.record_history', false);

    // Re-boot the trait to re-evaluate the config-gated created/updated hooks.
    StateMachineTestArticle::clearBootedModels();

    StateMachineTestArticle::create(['status' => PublicationStatusEnum::Draft]);
    expect(DB::table('enumerator_state_history')->count())->toBe(0);
});

it('exposes enumeratorStateHistory() as a polymorphic relation', function (): void {
    $article = StateMachineTestArticle::create(['status' => PublicationStatusEnum::Draft]);
    expect($article->enumeratorStateHistory()->count())->toBe(1);
});

// EnumeratorStateHistory model itself

it('EnumeratorStateHistory uses the configured table name', function (): void {
    config()->set('enumerator.state_machine.table_name', 'enumerator_state_history');
    $model = new EnumeratorStateHistory;
    expect($model->getTable())->toBe('enumerator_state_history');
});

it('EnumeratorStateHistory casts context to array', function (): void {
    $row = DB::table('enumerator_state_history')->insertGetId([
        'subject_type' => StateMachineTestArticle::class,
        'subject_id' => 1,
        'field' => 'status',
        'from' => 'draft',
        'to' => 'pending',
        'enum_class' => PublicationStatusEnum::class,
        'context' => json_encode(['actor' => 'system']),
    ]);

    $reloaded = EnumeratorStateHistory::find($row);
    expect($reloaded->context)->toBe(['actor' => 'system']);
});

it('EnumeratorStateHistory subject() / causer() return MorphTo', function (): void {
    $model = new EnumeratorStateHistory;
    expect($model->subject())->toBeInstanceOf(MorphTo::class);
    expect($model->causer())->toBeInstanceOf(MorphTo::class);
});
