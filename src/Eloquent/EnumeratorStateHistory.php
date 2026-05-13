<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Record of a state transition for any model using HasEnumeratorStateMachine.
 *
 * Table name comes from `config('enumerator.state_machine.table_name')`
 * (default `enumerator_state_history`).
 *
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $field
 * @property ?string $from
 * @property string $to
 * @property string $enum_class
 * @property ?array<string, mixed> $context
 * @property ?int $causer_id
 * @property ?string $causer_type
 */
class EnumeratorStateHistory extends Model
{
    /**
     * Explicit fillable list: tightens mass-assignment from the
     * previous `$guarded = []` blanket allow.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_type',
        'subject_id',
        'field',
        'from',
        'to',
        'enum_class',
        'context',
        'causer_id',
        'causer_type',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setTable((string) config('enumerator.state_machine.table_name', 'enumerator_state_history'));
        parent::__construct($attributes);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
