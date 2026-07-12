<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Fixture exercising the ResolvesMagicCalls multi-handler dispatch path.
 *
 * Adds a second magic handler (`magicSibling`) so any `sibling*()` call
 * resolves to a second hit. When both magicCompare and magicSibling return
 * a hit on the same method name, the ambiguous-resolution config branch
 * fires — exactly the surface that's unreachable with the default
 * single-handler dispatch.
 */
enum MultiHandlerEnum: string implements Enumerator
{
    use HasEnumerator;

    case Alpha = 'alpha';
    case Beta = 'beta';

    /**
     * @return list<string>
     */
    protected static function magicCallHandlers(): array
    {
        return ['magicCompare', 'magicSibling'];
    }

    /**
     * Returns a hit for any method starting with "is" (overlapping with
     * magicCompare) so we can deliberately provoke ambiguity. For methods
     * not starting with "is", returns null (deferring to the next handler).
     *
     * @param  array<int, mixed>  $arguments
     * @return array{0: mixed}|null
     */
    protected function magicSibling(string $method, array $arguments): ?array
    {
        if (! str_starts_with($method, 'is') || $arguments !== []) {
            return null;
        }

        return ['from-sibling-handler'];
    }
}
