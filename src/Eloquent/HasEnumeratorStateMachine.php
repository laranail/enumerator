<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Eloquent;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidTransitionException;
use UnitEnum;

/**
 * Model trait that enforces state-machine transitions for one or more enum
 * columns and (optionally) records each transition in
 * `enumerator_state_history`.
 *
 * Implementing model declares which columns it manages:
 *
 *     class Order extends Model {
 *         use HasEnumeratorStateMachine, HasEnumeratorScopes;
 *
 *         protected array $stateMachines = ['status'];
 *         protected bool $recordEnumStateHistory = true;
 *
 *         protected function casts(): array {
 *             return [ 'status' => AsEnum::of(OrderStatusEnum::class) ];
 *         }
 *     }
 *
 * @mixin Model
 */
trait HasEnumeratorStateMachine
{
    public static function bootHasEnumeratorStateMachine(): void
    {
        static::creating(function ($model): void {
            $model->assertInitialStateForAllMachines();
        });

        static::updating(function ($model): void {
            $model->assertAllowedTransitionsForAllMachines();
        });

        if ((bool) config('enumerator.state_machine.record_history', true)) {
            static::created(function ($model): void {
                $model->recordTransitionsForAllMachines(true);
            });
            static::updated(function ($model): void {
                $model->recordTransitionsForAllMachines(false);
            });
        }
    }

    public function enumeratorStateHistory(): MorphMany
    {
        return $this->morphMany(EnumeratorStateHistory::class, 'subject');
    }

    /**
     * @return array<int, string>
     */
    protected function stateMachines(): array
    {
        return property_exists($this, 'stateMachines')
            ? (array) $this->stateMachines
            : [];
    }

    protected function shouldRecordHistory(): bool
    {
        if (property_exists($this, 'recordEnumStateHistory')) {
            return (bool) $this->recordEnumStateHistory;
        }

        return (bool) config('enumerator.state_machine.record_history', true);
    }

    protected function assertInitialStateForAllMachines(): void
    {
        if (! (bool) config('enumerator.state_machine.enforce_initial_state', true)) {
            return;
        }

        foreach ($this->stateMachines() as $field) {
            $value = $this->getAttribute($field);
            if ($value === null) {
                continue;
            }
            $class = $value::class;
            if (! is_subclass_of($class, Stateful::class)) {
                continue;
            }
            if (! in_array($value, $class::initialStates(), true)) {
                throw new InvalidTransitionException(sprintf(
                    'Initial state %s::%s is not allowed for %s.',
                    $class,
                    $value->name,
                    $field,
                ));
            }
        }
    }

    protected function assertAllowedTransitionsForAllMachines(): void
    {
        foreach ($this->stateMachines() as $field) {
            $original = $this->getOriginal($field);
            $current = $this->getAttribute($field);
            if ($original === null || $current === null || $original === $current) {
                continue;
            }

            if (! $original instanceof UnitEnum || ! $current instanceof UnitEnum) {
                continue;
            }
            if (! method_exists($original, 'canTransitionTo')) {
                continue;
            }
            if (! $original->canTransitionTo($current)) {
                throw new InvalidTransitionException(sprintf(
                    'Transition %s->%s not allowed on %s::%s.',
                    $original->name,
                    $current->name,
                    static::class,
                    $field,
                ));
            }
        }
    }

    protected function recordTransitionsForAllMachines(bool $isCreation): void
    {
        if (! $this->shouldRecordHistory()) {
            return;
        }

        foreach ($this->stateMachines() as $field) {
            $original = $isCreation ? null : $this->getOriginal($field);
            $current = $this->getAttribute($field);
            if ($current === null) {
                continue;
            }
            if (! $isCreation && $original === $current) {
                continue;
            }

            $from = $original instanceof BackedEnum ? (string) $original->value
                : ($original instanceof UnitEnum ? $original->name : null);
            $to = $current instanceof BackedEnum ? (string) $current->value
                : ($current instanceof UnitEnum ? $current->name : (string) $current);

            $this->enumeratorStateHistory()->create([
                'field' => $field,
                'from' => $from,
                'to' => $to,
                'enum_class' => $current::class,
            ]);
        }
    }
}
