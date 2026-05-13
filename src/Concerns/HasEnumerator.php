<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

/**
 * One-stop umbrella trait for native PHP 8.3+ enums.
 *
 * `use HasEnumerator;` pulls in every behaviour trait the package ships
 * for the native-enum path — including invokable case factories
 * (`Status::Active()`). Granular traits stay public for advanced
 * composition; this is the recommended default.
 *
 * Composed traits and what each contributes:
 *
 *   - HasEnumeratorBehavior  static collection helpers + the seven core
 *                            behaviours composed by `Concerns\Core\BehaviorCore`
 *                            (HasAttributes / HasEquality / HasFromHelpers /
 *                            IsJsonable / IsTranslatable / RendersHtml /
 *                            ResolvesMagicCalls)
 *   - HasBitmask             Bitwise support (#[Bit] attributes)
 *   - HasGrouping            declarative groups()
 *   - HasInvokableCases      `Status::Active()` static factory
 *   - HasLifecycle           next() / previous() / isFirst() / isLast()
 *   - HasMagicComparisons    isFoo() / isNotFoo() dispatch via magicCompare
 *   - HasOrder               compareTo() / sortedByOrder()
 *   - HasTransitions         state machine, when Stateful is implemented
 *
 * Mutually exclusive with `HasClassEnumBehavior` (class-const path).
 * No `insteadof`/`as` aliasing needed — none of the composed traits
 * define overlapping methods.
 *
 * Usage:
 *
 *     enum UserStatusEnum: string implements Enumerator
 *     {
 *         use HasEnumerator;
 *
 *         case Active   = 'active';
 *         case Inactive = 'inactive';
 *         case Banned   = 'banned';
 *     }
 *
 *     UserStatusEnum::Active()->label();    // via HasInvokableCases
 *     UserStatusEnum::Active->isActive();   // via HasMagicComparisons
 *     UserStatusEnum::collect();            // via HasEnumeratorBehavior
 */
trait HasEnumerator
{
    use HasBitmask;
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasInvokableCases;
    use HasLifecycle;
    use HasMagicComparisons;
    use HasOrder;
    use HasTransitions;
}
